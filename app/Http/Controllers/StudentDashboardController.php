<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicCalendar;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $schoolId = 1; 

        $currentMonth = date('m');
        $currentYear = date('Y');
        
        $prefixDate = "{$currentYear}-" . str_pad($currentMonth, 2, '0', STR_PAD_LEFT);

        $holidays = [
            "2026-01-01" => "Tahun Baru 2026 Masehi", "2026-01-16" => "Isra Mikraj Nabi Muhammad SAW",
            "2026-02-16" => "Cuti Bersama Imlek", "2026-02-17" => "Tahun Baru Imlek 2577 Kongzili",
            "2026-03-18" => "Cuti Bersama Nyepi", "2026-03-19" => "Hari Suci Nyepi (Tahun Baru Saka 1948)",
            "2026-03-20" => "Cuti Bersama Idul Fitri", "2026-03-21" => "Idul Fitri 1447 Hijriah",
            "2026-03-22" => "Idul Fitri 1447 Hijriah", "2026-03-23" => "Cuti Bersama Idul Fitri",
            "2026-03-24" => "Cuti Bersama Idul Fitri", "2026-04-03" => "Wafat Yesus Kristus",
            "2026-04-05" => "Hari Paskah", "2026-05-01" => "Hari Buruh Internasional",
            "2026-05-14" => "Kenaikan Yesus Kristus", "2026-05-15" => "Cuti Bersama Kenaikan Yesus",
            "2026-05-27" => "Idul Adha 1447 Hijriah", "2026-05-28" => "Cuti Bersama Idul Adha",
            "2026-05-31" => "Hari Raya Waisak 2570 BE", "2026-06-01" => "Hari Lahir Pancasila",
            "2026-06-16" => "Tahun Baru Islam 1448 Hijriah", "2026-08-17" => "Proklamasi Kemerdekaan RI",
            "2026-08-25" => "Maulid Nabi Muhammad SAW", "2026-12-24" => "Cuti Bersama Natal",
            "2026-12-25" => "Hari Raya Natal"
        ];

        $allAgenda = [];

        foreach ($holidays as $date => $title) {
            if (strpos($date, $prefixDate) === 0) {
                $allAgenda[] = [
                    'date' => $date,
                    'title' => $title,
                    'color' => '#B91C1C' // Warna Merah untuk Libur
                ];
            }
        }

        if ($schoolId) {
            $dbEvents = AcademicCalendar::where('school_partner_id', $schoolId)
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

        usort($allAgenda, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        $monthlyEvents = json_decode(json_encode($allAgenda));

        $jadwalHariIni = [
            ['jam' => '07:15 - 08:45', 'mapel' => 'Matematika Lanjut', 'guru' => 'Bpk. Budi Santoso', 'ruang' => 'Kelas X-A'],
            ['jam' => '08:45 - 10:15', 'mapel' => 'Bahasa Indonesia', 'guru' => 'Ibu Siti Aminah', 'ruang' => 'Kelas X-A'],
            ['jam' => '10:15 - 10:45', 'mapel' => 'ISTIRAHAT', 'guru' => '-', 'ruang' => '-'],
            ['jam' => '10:45 - 12:15', 'mapel' => 'Informatika', 'guru' => 'Bpk. Ahmad Jaelani', 'ruang' => 'Lab Komputer 1'],
        ];

        return view('features.lms.students.dashboard', compact('monthlyEvents', 'jadwalHariIni'));
    }
}