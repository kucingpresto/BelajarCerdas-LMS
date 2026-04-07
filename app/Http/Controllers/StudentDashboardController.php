<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $schoolId = $user->StudentProfile->school_partner_id ?? null;
        $studentId = $user->id;

        // =========================================================
        // 1. CARI KELAS SISWA
        // =========================================================
        $studentClass = 'Belum Ada Kelas';
        
        $classRecord = \Illuminate\Support\Facades\DB::table('student_school_classes')
            ->join('school_classes', 'student_school_classes.school_class_id', '=', 'school_classes.id')
            ->where('student_school_classes.student_id', $studentId)
            ->select('school_classes.class_name')
            ->first();

        if ($classRecord) {
            $studentClass = $classRecord->class_name;
        }

        // =========================================================
        // 2. DATA AGENDA KALENDER (Gabungan DB & Libur Nasional)
        // =========================================================
        $currentMonth = date('m');
        $currentYear = date('Y');
        $allAgenda = [];
        
        if ($schoolId) {
            $dbEvents = \App\Models\AcademicCalendar::where('school_partner_id', $schoolId)
                ->where('status', 'published') 
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->get();

            foreach ($dbEvents as $ev) {
                $allAgenda[] = [
                    'date' => $ev->date,
                    'title' => $ev->title,
                    'color' => $ev->color
                ];
            }
        }

        // Libur Nasional
        $nationalHolidays = [
            "2026-01-01" => "Tahun Baru 2026", "2026-03-19" => "Hari Suci Nyepi",
            "2026-03-21" => "Idul Fitri", "2026-03-22" => "Idul Fitri",
            "2026-05-01" => "Hari Buruh", "2026-08-17" => "Kemerdekaan RI",
            "2026-12-25" => "Hari Raya Natal"
        ];
        
        $prefixFilter = $currentYear . '-' . $currentMonth;
        foreach ($nationalHolidays as $date => $title) {
            if (strpos($date, $prefixFilter) === 0) {
                $allAgenda[] = ['date' => $date, 'title' => $title, 'color' => '#B91C1C'];
            }
        }

        usort($allAgenda, function ($a, $b) { return strtotime($a['date']) - strtotime($b['date']); });
        $monthlyEvents = json_decode(json_encode($allAgenda));

        // =========================================================
        // 3. JADWAL PELAJARAN HARI INI
        // =========================================================
        $hariInggris = date('l');
        $mapHari = ['Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu', 'Sunday'=>'Minggu'];
        $hariIni = $mapHari[$hariInggris] ?? 'Senin';

        $dbSchedules = [];
        if ($schoolId && $studentClass !== 'Belum Ada Kelas') {
            $dbSchedules = \App\Models\LessonSchedule::where('school_partner_id', $schoolId)
                ->where('class_name', $studentClass)
                ->where('day_of_week', $hariIni)
                ->where('status', 'published') 
                ->get();
        }

        $jadwalHariIni = [];
        foreach ($dbSchedules as $jadwal) {
            $jadwalHariIni[] = [
                'is_break'   => false,
                'start_time' => $jadwal->start_time,
                'jam'        => substr($jadwal->start_time, 0, 5) . ' - ' . substr($jadwal->end_time, 0, 5),
                'mapel'      => $jadwal->subject_name,
                'guru'       => $jadwal->teacher_name,
                'ruang'      => $jadwal->class_name,
                'color'      => $jadwal->color
            ];
        }

        if (in_array($hariIni, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])) {
            $jadwalHariIni[] = ['is_break' => true, 'start_time' => '10:00:00', 'jam' => '10:00 - 10:45', 'mapel' => 'ISTIRAHAT PERTAMA', 'color' => '#f97316'];
            $jadwalHariIni[] = ['is_break' => true, 'start_time' => '12:15:00', 'jam' => '12:15 - 13:00', 'mapel' => 'ISTIRAHAT KEDUA', 'color' => '#f97316'];
        }
        usort($jadwalHariIni, function ($a, $b) { return strcmp($a['start_time'], $b['start_time']); });

        // =========================================================
        // 4. DATA POLLING (AKTIF & SUDAH DIVOTE)
        // =========================================================
        $activePolls = [];
        $votedPolls = [];

        if ($schoolId) {
            $polls = \App\Models\Poll::where('school_partner_id', $schoolId)
                        ->where('status', 'active')
                        ->orderBy('created_at', 'desc')
                        ->get();

            foreach ($polls as $poll) {
                $options = \App\Models\PollOption::where('poll_id', $poll->id)->get();
                $hasVoted = \App\Models\PollVote::where('poll_id', $poll->id)
                                ->where('student_id', $studentId)
                                ->exists();
                $totalVotes = \App\Models\PollVote::where('poll_id', $poll->id)->count();

                $formattedOptions = [];
                foreach ($options as $opt) {
                    $votesForOption = \App\Models\PollVote::where('poll_option_id', $opt->id)->count();
                    $percentage = $totalVotes > 0 ? round(($votesForOption / $totalVotes) * 100) : 0;
                    
                    $formattedOptions[] = (object)[
                        'id'         => $opt->id,
                        'text'       => $opt->option_text,
                        'votes'      => $votesForOption,
                        'percentage' => $percentage
                    ];
                }

                $pollData = (object)[
                    'id'          => $poll->id,
                    'question'    => $poll->question,
                    'total_votes' => $totalVotes,
                    'options'     => $formattedOptions
                ];

                if ($hasVoted) {
                    $votedPolls[] = $pollData; 
                } else {
                    $activePolls[] = $pollData; 
                }
            }
        }

        // =========================================================
        // 5. RENDER KE VIEW
        // =========================================================
        return view('features.lms.students.dashboard', compact(
            'monthlyEvents', 
            'jadwalHariIni', 
            'hariIni', 
            'studentClass', 
            'activePolls', 
            'votedPolls'
        ));
    }

    /**
     * Memproses Pilihan Vote Siswa via AJAX
     */
    public function submitVote(Request $request)
    {
        try {
            $userId = Auth::id();
            $pollId = $request->poll_id;
            $optionId = $request->option_id;

            $sudahVote = \App\Models\PollVote::where('poll_id', $pollId)
                            ->where('student_id', $userId)
                            ->exists();

            if ($sudahVote) {
                return response()->json(['success' => false, 'message' => 'Kamu sudah pernah mengisi polling ini!']);
            }

            \App\Models\PollVote::insert([
                'poll_id'        => $pollId,
                'poll_option_id' => $optionId,
                'student_id'     => $userId,
                'created_at'     => now(),
                'updated_at'     => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Suaramu berhasil direkam!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error Sistem: ' . $e->getMessage()], 500);
        }
    }
}