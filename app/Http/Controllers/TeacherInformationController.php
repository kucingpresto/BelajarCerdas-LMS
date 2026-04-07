<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicCalendar;

class TeacherInformationController extends Controller
{
    
    public function calendarView($role, $schoolName, $schoolId)
    {
        $events = AcademicCalendar::where('school_partner_id', $schoolId)->get(['date', 'title', 'type', 'display', 'color', 'status']);
        
        $savedEvents = $events->toArray();
        return view('features.lms.teacher.Information.calender', compact('role', 'schoolName', 'schoolId', 'savedEvents'));
    }

    public function saveCalendar(Request $request, $role, $schoolName, $schoolId)
    {
        $status = $request->status; 
        $events = $request->events; 

        AcademicCalendar::where('school_partner_id', $schoolId)->delete();

        if (!empty($events)) {
            $insertData = [];
            foreach ($events as $event) {
                $insertData[] = [
                    'school_partner_id' => $schoolId,
                    'date'              => $event['date'],
                    'title'             => $event['title'],
                    'type'              => $event['type'] ?? 'school_event',
                    'display'           => $event['display'] ?? 'outline',
                    'color'             => $event['color'],
                    'status'            => $status,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }
            AcademicCalendar::insert($insertData);
        }
        return response()->json([
            'success' => true, 
            'message' => 'Kalender berhasil disimpan sebagai ' . strtoupper($status)
        ]);
    }

    public function scheduleView($role, $schoolName, $schoolId)
    {
        return view('features.lms.teacher.Information.schedule', compact('role', 'schoolName', 'schoolId'));
    }

    public function pollingView($role, $schoolName, $schoolId)
    {
        return view('features.lms.teacher.Information.polling', compact('role', 'schoolName', 'schoolId'));
    }
}