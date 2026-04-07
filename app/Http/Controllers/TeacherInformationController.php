<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicCalendar;
use App\Models\LessonSchedule;

class TeacherInformationController extends Controller
{
    /**
     * Menampilkan Halaman Kalender Akademik Guru
     */
    public function teacherCalendarView($role, $schoolName, $schoolId)
    {
        $eventsFromDb = \App\Models\AcademicCalendar::where('school_partner_id', $schoolId)->get();
        
        $savedEvents = [];
        foreach($eventsFromDb as $ev) {
            $savedEvents[] = [
                'date'   => date('Y-m-d', strtotime($ev->date)), 
                'title'  => $ev->title,
                'type'   => $ev->type,
                'color'  => $ev->color,
                'status' => $ev->status
            ];
        }

        return view('features.lms.teacher.information.calender', compact('role', 'schoolName', 'schoolId', 'savedEvents'));
    }

    /**
     * Menyimpan Data Kalender Akademik Guru
     */
    public function saveCalendarData(Request $request, $role, $schoolName, $schoolId)
    {
        try {
            $status = $request->status; 
            $events = $request->events;

            \App\Models\AcademicCalendar::where('school_partner_id', $schoolId)->delete();

            if (!empty($events)) {
                $insertData = [];
                foreach ($events as $event) {
                    $insertData[] = [
                        'school_partner_id' => $schoolId,
                        'date'              => $event['date'],
                        'title'             => $event['title'],
                        'type'              => $event['type'] ?? 'school_event',
                        'color'             => $event['color'] ?? '#F59E0B',
                        'status'            => $status,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
                \App\Models\AcademicCalendar::insert($insertData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kalender berhasil disimpan permanen ke database!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'GAGAL DATABASE: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan Halaman Jadwal Pelajaran (Drag & Drop UI)
     */
    public function scheduleView($role, $schoolName, $schoolId)
    {
        // =========================
        // TIME SLOT
        // =========================
        $timeSlots = [
            ['start' => '07:00', 'end' => '07:45', 'is_break' => false],
            ['start' => '07:45', 'end' => '08:30', 'is_break' => false],
            ['start' => '08:30', 'end' => '09:15', 'is_break' => false],
            ['start' => '09:15', 'end' => '10:00', 'is_break' => false],
            ['start' => '10:00', 'end' => '10:45', 'is_break' => true],
            ['start' => '10:45', 'end' => '11:30', 'is_break' => false],
            ['start' => '11:30', 'end' => '12:15', 'is_break' => false],
            ['start' => '12:15', 'end' => '13:00', 'is_break' => true],
            ['start' => '13:00', 'end' => '13:45', 'is_break' => false],
            ['start' => '13:45', 'end' => '14:30', 'is_break' => false],
            ['start' => '14:30', 'end' => '15:15', 'is_break' => false],
        ];

        // =========================
        // GET KELAS
        // =========================
        $classes = DB::table('school_classes')
            ->where('school_partner_id', $schoolId)
            ->pluck('class_name')
            ->toArray();

        // =========================
        // GET GURU
        // =========================
        $guruDb = DB::table('user_accounts')
            ->join('school_staff_profiles', 'user_accounts.id', '=', 'school_staff_profiles.user_id')
            ->where('school_staff_profiles.school_partner_id', $schoolId)
            ->where('user_accounts.role', 'Guru')
            ->select('user_accounts.id', 'school_staff_profiles.nama_lengkap')
            ->get();

        $teachers = [];
        $colors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#06B6D4', '#EAB308'];
        $colorIndex = 0;

        foreach ($guruDb as $g) {

            // =========================
            // AMBIL MAPEL DARI PIVOT
            // =========================
            $pivotData = DB::table('teacher_mapels')
                ->where('user_id', $g->id)
                ->get();

            $mapelList = [];

            foreach ($pivotData as $pivot) {

                if (!$pivot->mapel_id) continue;

                // HANDLE CSV / SINGLE
                $ids = explode(',', $pivot->mapel_id);

                foreach ($ids as $id) {

                    $mapelId = (int) trim($id);
                    if (!$mapelId) continue;

                    // =========================
                    // CEK KE MAPELS
                    // =========================
                    $mapel = DB::table('mapels')->where('id', $mapelId)->first();

                    if ($mapel) {
                        $mapelList[] = $mapel->mata_pelajaran;
                    } else {
                        // fallback ke school_mapels
                        $schoolMapel = DB::table('school_mapels')->where('id', $mapelId)->first();

                        if ($schoolMapel) {
                            $mapelAsli = DB::table('mapels')
                                ->where('id', (int)$schoolMapel->mapel_id)
                                ->first();

                            if ($mapelAsli) {
                                $mapelList[] = $mapelAsli->mata_pelajaran;
                            }
                        }
                    }
                }
            }

            // =========================
            // JIKA MASIH KOSONG → FALLBACK GLOBAL MAPEL
            // =========================
            if (empty($mapelList)) {
                $fallbackMapel = DB::table('mapels')->inRandomOrder()->limit(1)->first();

                $teachers[] = [
                    'id'      => $g->id,
                    'name'    => $g->nama_lengkap,
                    'subject' => $fallbackMapel ? $fallbackMapel->mata_pelajaran : 'Belum Ada Mapel',
                    'color'   => $colors[$colorIndex % count($colors)]
                ];

                $colorIndex++;
                continue;
            }

            // =========================
            // ANTI DUPLIKAT
            // =========================
            $mapelList = array_unique($mapelList);

            foreach ($mapelList as $mapelName) {
                $teachers[] = [
                    'id'      => $g->id,
                    'name'    => $g->nama_lengkap,
                    'subject' => $mapelName,
                    'color'   => $colors[$colorIndex % count($colors)]
                ];
                $colorIndex++;
            }
        }

        return view('features.lms.teacher.information.schedule', compact(
            'role',
            'schoolName',
            'schoolId',
            'timeSlots',
            'teachers',
            'classes'
        ));
    }
    /**
     * Menyimpan Data Jadwal Pelajaran & Validasi Anti-Bentrok
     */
    public function saveSchedule(Request $request, $role, $schoolName, $schoolId)
    {
        $className = $request->class_name;
        $status = $request->status;
        $schedules = $request->schedules;

        if (!$className) {
            return response()->json(['success' => false, 'message' => 'Pilih kelas terlebih dahulu.']);
        }

        if (!empty($schedules)) {
            foreach ($schedules as $schedule) {
                $clash = LessonSchedule::where('school_partner_id', $schoolId)
                    ->where('day_of_week', $schedule['day'])
                    ->where('start_time', $schedule['start_time'])
                    ->where('teacher_id', $schedule['teacher_id'])
                    ->where('class_name', '!=', $className) 
                    ->first();

                if ($clash) {
                    return response()->json([
                        'success' => false, 
                        'message' => "🚨 BENTROK DETECTED!\n\n" . $schedule['teacher_name'] . " sudah memiliki jadwal mengajar di " . $clash->class_name . " pada hari " . $schedule['day'] . " jam " . substr($schedule['start_time'], 0, 5) . ".\n\nSilakan ganti jam atau guru."
                    ]);
                }
            }
        }

        LessonSchedule::where('school_partner_id', $schoolId)
            ->where('class_name', $className)
            ->delete();

        if (!empty($schedules)) {
            $insertData = [];
            foreach ($schedules as $schedule) {
                $endTime = date('H:i', strtotime('+45 minutes', strtotime($schedule['start_time'])));

                $insertData[] = [
                    'school_partner_id' => $schoolId,
                    'class_name'        => $className,
                    'day_of_week'       => $schedule['day'],
                    'start_time'        => $schedule['start_time'],
                    'end_time'          => $endTime,
                    'teacher_id'        => $schedule['teacher_id'],
                    'teacher_name'      => $schedule['teacher_name'],
                    'subject_name'      => $schedule['subject_name'],
                    'color'             => $schedule['color'],
                    'status'            => $status,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }
            LessonSchedule::insert($insertData);
        }
        
        return response()->json([
            'success' => true, 
            'message' => 'Jadwal Kelas ' . $className . ' berhasil disimpan sebagai ' . strtoupper($status) . '!'
        ]);
    }

    public function getScheduleData($schoolId, $className)
    {
        $schedules = \App\Models\LessonSchedule::where('school_partner_id', $schoolId)
            ->where('class_name', $className)
            ->get();

        return response()->json(['success' => true, 'data' => $schedules]);
    }

    /**
     * Menampilkan Halaman Pembuatan Polling
     */
    public function teacherPollingView($role, $schoolName, $schoolId)
    {
        $polls = \App\Models\Poll::where('school_partner_id', $schoolId)
                    ->where('teacher_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('features.lms.teacher.information.polling', compact('role', 'schoolName', 'schoolId', 'polls'));
    }

    /**
     * Menyimpan Pertanyaan dan Pilihan Polling ke Database
     */
    public function savePollingData(Request $request, $role, $schoolName, $schoolId)
    {
        try {
            $poll = \App\Models\Poll::create([
                'school_partner_id' => $schoolId,
                'teacher_id'        => Auth::id(),
                'question'          => $request->question,
                'status'            => 'active',
            ]);

            $optionsData = [];
            foreach ($request->options as $opt) {
                if (!empty($opt)) {
                    $optionsData[] = [
                        'poll_id'     => $poll->id,
                        'option_text' => $opt,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
            }
            \App\Models\PollOption::insert($optionsData);

            return response()->json([
                'success' => true,
                'message' => 'Polling berhasil dipublikasikan ke siswa!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ERROR DATABASE: ' . $e->getMessage()
            ], 500);
        }
    }
}