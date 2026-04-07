<?php

namespace App\Http\Controllers;

use App\Models\LmsMeetingContent;
use App\Models\Mapel;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolClass;
use App\Models\Service;
use App\Models\StudentSchoolClass;
use Illuminate\Support\Facades\Auth;

class StudentLearningController extends Controller
{
    // private function guessMime
    private function guessMime($ext)
    {
        return match (strtolower($ext)) {
            'mp4', 'webm', 'ogg' => 'video/' . $ext,
            'pdf'               => 'application/pdf',
            'jpg', 'jpeg', 'png', 'webp' => 'image/' . $ext,
            default             => 'application/octet-stream',
        };
    }

    // function lms student view
    public function lmsStudentView($role, $schoolName, $schoolId){
        return view('features.lms.student.lms-student', compact('role', 'schoolName', 'schoolId'));
    }

    // paginate lms student
    public function paginateLmsStudent($role, $schoolName, $schoolId)
    {
        // ambil student school class siswa
        $studentSchoolClass = StudentSchoolClass::where('student_class_status', 'active')->where('student_id', Auth::id())->first();

        if (!$studentSchoolClass) {
            return response()->json([
                'data' => [],
                'assessmentActivity' => null
            ]);
        }

        // ambil kelasId siswa
        $kelasId = $studentSchoolClass->SchoolClass->kelas_id;

        // ambil school classId siswa
        $schoolClassId = $studentSchoolClass->school_class_id;

        // ambil mapel default dan custom by school berdasarkan kelas pada siswa tersebut
        $mapel = Mapel::with(['TeacherMapel' => function ($q) use ($schoolClassId) {
            $q->where('is_active', 1)->where('school_class_id', $schoolClassId);
            
            },'TeacherMapel.UserAccount.SchoolStaffProfile'])->where('status_mata_pelajaran', 'active')->where('kelas_id', $kelasId)
            ->where(function ($q) use ($schoolId) {

                // ambil mapel default yang active
                $q->where(function ($qGlobal) use ($schoolId) {
                    $qGlobal->whereNull('school_partner_id')->whereDoesntHave('SchoolMapel', function ($qSM) use ($schoolId) {
                        $qSM->where('school_partner_id', $schoolId)->where('is_active', 0);
                    });
                })

                // atau ambil mapel custom yang active pada masing" sekolah
                ->orWhere(function ($q2) use ($schoolId) {
                    $q2->where('school_partner_id', $schoolId)->whereHas('SchoolMapel', function ($q3) use ($schoolId) {
                        $q3->where('school_partner_id', $schoolId)->where('is_active', 1);
                    });
            });

        })->get();

        return response()->json([
            'data' => $mapel,
            'assessmentActivity' => '/lms/:role/:schoolName/:schoolId/curriculum/:curriculumId/subject/:mapelId/learning'
        ]); 
    }

    // function lms student learning view
    public function studentLearning($role, $schoolName, $schoolId, $curriculumId, $mapelId){
        return view('features.lms.student.learning.student-learning', compact('role', 'schoolName', 'schoolId', 'curriculumId', 'mapelId'));
    }

    // function paginate student learning
    public function paginateStudentLearning($role, $schoolName, $schoolId, $curriculumId, $mapelId){
        $getContent = Service::with(['Kurikulum'])->where('kurikulum_id', $curriculumId)->where('school_partner_status', true)->get();

        $getAssessment = SchoolAssessmentType::where('school_partner_id', $schoolId)->get();

        $getMapel = Mapel::with(['TeacherMapel' => function ($q) {
            $q->where('is_active', 1);
            
            },'TeacherMapel.UserAccount.SchoolStaffProfile'])->where('id', $mapelId)->first();

        return response()->json([
            'data' => $getContent,
            'assessmentType' => $getAssessment,
            'mapel' => $getMapel,
            'previewMateri' => '/lms/:role/:schoolName/:schoolId/curriculum/:curriculumId/subject/:mapelId/learning/service/:serviceId',
            'previewAssessment' => '/lms/:role/:schoolName/:schoolId/curriculum/:curriculumId/subject/:mapelId/learning/assessment/:assessmentTypeId'
        ]);
    }

    // function preview materi
    public function studentReviewMeeting($role, $schoolName, $schoolId, $curriculumId, $mapelId, $serviceId){
        $getService = Service::where('id', $serviceId)->first();

        return view('features.lms.student.learning.student-review-meeting', compact('role', 'schoolName', 'schoolId', 'curriculumId', 'mapelId', 
            'serviceId', 'getService'));
    }

    // function paginate review meeting
    public function paginateStudentReviewMeeting($role, $schoolName, $schoolId, $curriculumId, $mapelId, $serviceId) {

        $getMeeting = LmsMeetingContent::with(['LmsContent.SchoolLmsContent' => function ($query) use ($schoolId) {
            $query->where('school_partner_id', $schoolId)->where('is_active', true);
        }])->where('is_active', true)->whereHas('LmsContent', function ($query) use ($schoolId) {
            $query->where('is_active', 1); // ambil content global aktif

            $query->where(function ($q) use ($schoolId) {

                // Jika ada override untuk sekolah
                $q->whereHas('SchoolLmsContent', function ($qOverride) use ($schoolId) {
                    $qOverride->where('school_partner_id', $schoolId)->where('is_active', 1);
                })

                // Jika tidak ada override, pakai global
                ->orWhere(function ($qGlobal) use ($schoolId) {
                    $qGlobal->whereNull('school_partner_id')->whereDoesntHave('SchoolLmsContent', function ($qCheck) use ($schoolId) {
                        $qCheck->where('school_partner_id', $schoolId);
                    });
                });
            });
        })->where('service_id', $serviceId)->get();

        return response()->json([
            'data' => $getMeeting,
        ]);
    }


    // function show review meeting
    public function showStudentReviewContent($role, $schoolName, $schoolId, $curriculumId, $mapelId, $serviceId, $meetingId)
    {
        $item = LmsMeetingContent::with('LmsContent.LmsContentItem.ServiceRule', 'LmsContent.Service')
            ->findOrFail($meetingId);

        $serviceName = $item->LmsContent?->Service?->name;
        $contentItem = $item->LmsContent->LmsContentItem[0];

        if (!$contentItem->value_file) {
            return response()->json([
                'type' => 'text',
                'service_name' => $serviceName,
                'value_text' => $contentItem->value_text
            ]);
        }

        $extension = pathinfo($contentItem->value_file, PATHINFO_EXTENSION);

        return response()->json([
            'type' => 'file',
            'service_name' => $serviceName,
            'file_url' => asset('lms-contents/' . $contentItem->value_file),
            'mime' => $this->guessMime($extension)
        ]);
    }

    public function downloadStudentContent($role, $schoolName, $schoolId, $curriculumId, $mapelId, $serviceId, $meetingContentId)
    {
        $item = LmsMeetingContent::with('LmsContent.LmsContentItem')
            ->findOrFail($meetingContentId);

        if (!$item->LmsContent) {
            abort(404, 'Content tidak ditemukan');
        }

        $contentItem = $item->LmsContent->LmsContentItem->first(function ($row) {
            return filled($row->value_file);
        });

        if (!$contentItem) {
            abort(404, 'File tidak tersedia');
        }

        $safeFilename = basename($contentItem->value_file);
        $filePath = public_path('lms-contents/' . $safeFilename);

        if (!is_file($filePath) || !is_readable($filePath)) {
            abort(404, 'File tidak ditemukan di server');
        }

        clearstatcache(true, $filePath);
        if ((int) filesize($filePath) <= 0) {
            abort(404, 'File kosong atau rusak di server');
        }

        $downloadName = $contentItem->original_filename ?: $safeFilename;

        return response()->download($filePath, $downloadName, [
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
