<?php

namespace App\Http\Controllers;

use App\Exports\GradebookExport;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolAssessmentTypeWeight;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentSummary;
use App\Models\StudentSchoolClass;
use App\Models\TeacherMapel;
use App\Services\ClassName\ClassNameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class TeacherGradebookController extends Controller
{
    private function extractClassLevel($className)
    {
        $classNameService = new ClassNameService();
        return $classNameService->extractClassLevel($className);
    }
    
    public function teacherClassList($role, $schoolName, $schoolId)
    {
        return view('features.lms.teacher.gradebook.teacher-class-list', compact('role', 'schoolName', 'schoolId'));
    }

    public function paginateTeacherClassList(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $schoolPartner = SchoolPartner::findOrFail($schoolId);
        $jenjang = strtoupper($schoolPartner->jenjang_sekolah);

        // DEFAULT LEVEL BERDASARKAN JENJANG
        $startLevelMap = [
            'SD'  => 1,  'MI'  => 1,
            'SMP' => 7,  'MTS' => 7,
            'SMA' => 10, 'SMK' => 10,
            'MA'  => 10, 'MAK' => 10,
        ];

        $defaultLevel = $startLevelMap[$jenjang] ?? 1;
        
        $subjectTeacher = TeacherMapel::with(['Mapel', 'SchoolClass' => function ($query) {
                $query->withCount('StudentSchoolClass');
            },
            'SchoolClass.UserAccount.SchoolStaffProfile'
        ])
        ->where('user_id', $user->id)->where('is_active', 1)->get();

        // TAHUN AJARAN
        $tahunAjaran = $subjectTeacher->pluck('SchoolClass.tahun_ajaran')->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        // FILTER BERDASARKAN TAHUN AJARAN
        $schoolClasses = $subjectTeacher->where('SchoolClass.tahun_ajaran', $searchYear)->values();

        // LEVEL KELAS UNIK
        $classLevels = $schoolClasses->pluck('SchoolClass.class_name')->map(fn($c) => (int) $this->extractClassLevel($c))->unique()->sort()->values();

        $selectedClass = $request->filled('search_class') ? (int) $request->search_class : ($classLevels->first() ?? $defaultLevel);

        // FILTER ROMBEL SESUAI LEVEL
        $schoolClasses = $schoolClasses->filter(fn($item) => (int)$this->extractClassLevel($item->SchoolClass->class_name) === $selectedClass)->values();

        // AMBIL MAPEL GURU
        $subjects = $schoolClasses->unique('mapel_id')->map(function ($item) {
            return [
                'id' => $item->mapel_id,
                'name' => $item->Mapel->mata_pelajaran ?? '-',
            ];
        })->values();

        // Filter berdasarkan level kelas
        if ($selectedClass) {
            $subjectTeacher = $subjectTeacher->filter(function ($item) use ($selectedClass) {

                if (!$item || !$item->SchoolClass->class_name) {
                    return false;
                }

                return $this->extractClassLevel($item->SchoolClass->class_name) == $selectedClass;
            });
        }

        $searchSubject = $request->filled('search_subject') ? (int) $request->search_subject : null;

        if ($searchSubject) {
            $schoolClasses = $schoolClasses->filter(function ($item) use ($searchSubject) {
                return $item->mapel_id == $searchSubject;
            })->values();
        }

        return response()->json([
            'data' => $schoolClasses,
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'selectedClass' => $selectedClass,
            'className'     => $classLevels,
            'subject' => $subjects,
            'teacherGradebook' => '/lms/:role/:schoolName/:schoolId/teacher-class-list/teacher-gradebook/subject-teacher/:subjectTeacherId'
        ]);
    }

    public function gradebookManagement($role, $schoolName, $schoolId, $subjectTeacherId)
    {
        return view('features.lms.teacher.gradebook.teacher-gradebook-management', compact('role', 'schoolName', 'schoolId', 'subjectTeacherId'));
    }

    public function paginateGradebookManagement($role, $schoolName, $schoolId, $subjectTeacherId)
    {
        $user = Auth::user();

        $teacherMapel = TeacherMapel::with(['Mapel', 'SchoolClass'])->where('id', $subjectTeacherId)->where('user_id', $user->id)->firstOrFail();

        $assessmentTypes = SchoolAssessmentType::with('AssessmentMode')->where('school_partner_id', $schoolId)->where('is_active', 1)->get();

        $weights = SchoolAssessmentTypeWeight::where('school_partner_id', $schoolId)->get()->keyBy('assessment_type_id');

        $students = StudentSchoolClass::with('UserAccount')->where('school_class_id', $teacherMapel->school_class_id)->where('student_class_status', 'active')->get();

        $data = [];

        foreach ($students as $student) {

            $studentId = $student->student_id;

            $summaries = StudentAssessmentSummary::with('SchoolAssessment.SchoolAssessmentType')
                ->where('student_id', $studentId)
                ->whereHas('SchoolAssessment', function ($q) use ($teacherMapel) {
                    $q->where('school_class_id', $teacherMapel->school_class_id)
                    ->where('mapel_id', $teacherMapel->mapel_id);
                })
                ->get();

            $row = [
                'name' => $student->UserAccount->StudentProfile->nama_lengkap ?? '-',
                'types' => []
            ];

            foreach ($assessmentTypes as $type) {

                $scores = [];

                foreach ($summaries as $summary) {
                    if ($summary->SchoolAssessment->assessment_type_id == $type->id) {
                        $scores[] = $summary->final_score;
                    }
                }

                $avg = count($scores) ? array_sum($scores) / count($scores) : 0;

                $row['types'][] = [
                    'type_id' => $type->id,
                    'type_name' => $type->name,
                    'avg' => round($avg),
                    'count' => count($scores)
                ];
            }

            $total = 0;
            $totalWeight = 0;

            foreach ($row['types'] as $type) {
                $weight = $weights[$type['type_id']]->weight ?? 0;

                if ($type['count'] > 0 && $weight > 0) {
                    $total += $type['avg'] * $weight;
                    $totalWeight += $weight;
                }
            }

            $finalNormalized = $totalWeight > 0 ? round($total / $totalWeight) : 0;
            $finalAbsolute = round($total / 100);

            $row['final_normalized'] = $finalNormalized;
            $row['final_absolute'] = $finalAbsolute;

            $data[] = $row;
        }

        $finalNormalizedList = collect($data)->pluck('final_normalized');

        $summary = [
            'total_students' => count($data),

            'avg_normalized' => $finalNormalizedList->avg() ? round($finalNormalizedList->avg()) : 0,
            'max_normalized' => $finalNormalizedList->max() ?? 0,
            'min_normalized' => $finalNormalizedList->min() ?? 0,
        ];

        return response()->json([
            'data' => $data,
            'summary' => $summary,
            'teacherMapel' => $teacherMapel,
            'assessmentTypes' => $assessmentTypes
        ]);
    }

    public function exportGradebook($role, $schoolName, $schoolId, $subjectTeacherId)
    {
        // reuse logic dari paginate (penting biar konsisten)
        $response = $this->paginateGradebookManagement($role, $schoolName, $schoolId, $subjectTeacherId)->getData(true);

        $data = $response['data'];
        $assessmentTypes = $response['assessmentTypes'];

        return Excel::download(new GradebookExport($data, $assessmentTypes), 'gradebook.xlsx');
    }
}
