<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\LmsQuestionBank;
use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentQuestion;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolPartner;
use App\Models\TeacherMapel;
use App\Services\ClassName\ClassNameService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TeacherQuestionBankReleaseController extends Controller
{
    private function extractClassLevel($className)
    {
        $classNameService = new ClassNameService();
        return $classNameService->extractClassLevel($className);
    }
    
    public function teacherQuestionBankForRelease($role, $schoolName, $schoolId)
    {
        $schoolAssessmentType = SchoolAssessmentType::where('school_partner_id', $schoolId)->get();

        $getCurriculum = Kurikulum::all();

        return view('features.lms.teacher.question-bank-for-release.teacher-question-bank-for-release', compact('role', 'schoolName', 
            'schoolId', 'schoolAssessmentType', 'getCurriculum'));
    }

    // function teacher form question bank for release
    public function teacherFormQuestionBankForRelease(Request $request, $role, $schoolName, $schoolId)
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

        $query = SchoolAssessment::with(['Mapel', 'SchoolClass', 'SchoolAssessmentType'])
        ->whereHas('SchoolAssessmentType.AssessmentMode', function ($query) {
            $query->whereNot('code', 'project');
        })->where('user_id', $user->id)->where('school_partner_id', $schoolId)->orderBy('created_at', 'desc');

        $schoolAssessment = $query->get();

        // TAHUN AJARAN
        $tahunAjaran = $schoolAssessment->pluck('SchoolClass.tahun_ajaran')->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        // FILTER BERDASARKAN TAHUN AJARAN
        $schoolClasses = $schoolAssessment->where('SchoolClass.tahun_ajaran', $searchYear)->values();

        // LEVEL KELAS UNIK
        $classLevels = $schoolClasses->pluck('SchoolClass.class_name')->map(fn($c) => (int) $this->extractClassLevel($c))->unique()->sort()->values();

        $selectedClass = $request->filled('search_class') ? (int) $request->search_class : ($classLevels->first() ?? $defaultLevel);

        // FILTER ROMBEL SESUAI LEVEL
        $schoolClasses = $schoolClasses->filter(fn($item) => (int)$this->extractClassLevel($item->SchoolClass->class_name) === $selectedClass)->values();

        // Filter berdasarkan level kelas
        if ($selectedClass) {
            $schoolAssessment = $schoolAssessment->filter(function ($item) use ($selectedClass) {

                if (!$item || !$item->SchoolClass->kelas_id) {
                    return false;
                }

                return $this->extractClassLevel($item->SchoolClass->class_name) == $selectedClass;
            });
        }

        // AMBIL MAPEL GURU
        $subjects = $schoolClasses->unique('mapel_id')->map(function ($item) {
            return [
                'id' => $item->mapel_id,
                'name' => $item->Mapel->mata_pelajaran ?? '-',
            ];
        })->values();

        $schoolAssessmentType = SchoolAssessmentType::where('school_partner_id', $schoolId)->get();

        // FILTER SEARCH ASSESSMENT TYPE
        if ($request->filled('search_assessment_type')) {
            $schoolAssessment = $schoolAssessment->filter(function ($item) use ($request) {
                return $item->SchoolAssessmentType->id == $request->search_assessment_type;
            })->values();
        }

        // FILTER SEARCH SUBJECT
        if ($request->filled('search_subject')) {
            $schoolAssessment = $schoolAssessment->filter(function ($item) use ($request) {
                return $item->mapel_id == $request->search_subject;
            })->values();
        }

        // FILTER SEARCH SEMESTER
        if ($request->filled('search_semester')) {
            $schoolAssessment = $schoolAssessment->filter(function ($item) use ($request) {
                return $item->semester == $request->search_semester;
            })->values();
        }

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

        // MAPPING KELAS BERDASARKAN JENJANG
        $mappingClasses = [
            'SD'  => ['kelas 1','kelas 2','kelas 3','kelas 4','kelas 5','kelas 6'],
            'MI'  => ['kelas 1','kelas 2','kelas 3','kelas 4','kelas 5','kelas 6'],
            'SMP' => ['kelas 7','kelas 8','kelas 9'],
            'MTS' => ['kelas 7','kelas 8','kelas 9'],
            'SMA' => ['kelas 10','kelas 11','kelas 12'],
            'SMK' => ['kelas 10','kelas 11','kelas 12'],
            'MA'  => ['kelas 10','kelas 11','kelas 12'],
            'MAK' => ['kelas 10','kelas 11','kelas 12'],
        ];

        $allowedKelas = $mappingClasses[$jenjang] ?? [];

        $kelasIds = Kelas::whereIn(DB::raw('LOWER(kelas)'), $allowedKelas)->pluck('id');

        // TEACHER MAPEL
        $teacherMapels = TeacherMapel::where('user_id', $user->id)->where('is_active', true)
            ->whereHas('SchoolClass', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->with(['SchoolClass', 'Mapel'])->get();

        // AMBIL MAPEL ID GURU
        $mapelIds = $teacherMapels->pluck('mapel_id')->unique();

        // QUESTION BANK LIST
        $getQuestions = LmsQuestionBank::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile','Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab',
            'SchoolPartner', 'LmsQuestionOption',
            'SchoolQuestionBank' => function ($q) use ($schoolId) {

            if ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            }
            
        }])->whereIn('mapel_id', $mapelIds)->whereIn('kelas_id', $kelasIds)->orderBy('created_at', 'desc');

        // FILTER SEARCH MATERI
        if ($request->filled('search_question')) {
            $getQuestions->where('questions', 'LIKE', '%' . $request->search_question . '%');
        }

        // FILTER CURRICULUM CORE
        foreach (['kurikulum_id', 'kelas_id', 'mapel_id', 'bab_id', 'sub_bab_id'] as $filter) {
            if ($request->filled($filter)) {
                $getQuestions->where($filter, $request->$filter);
            }
        }

        $getQuestions = $getQuestions->where(function ($q1) use ($schoolId, $kelasIds) {
            $q1->where('school_partner_id', $schoolId)
            ->orWhere(function ($q2) use ($kelasIds) {
                $q2->whereNull('school_partner_id')->whereIn('kelas_id', $kelasIds);
            });
        })->get();

        return response()->json([
            'data' => $schoolAssessment->values(),
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'selectedClass' => $selectedClass,
            'className'     => $classLevels,
            'subject' => $subjects,
            'schoolAssessmentType' => $schoolAssessmentType,
            'questionBank' => $getQuestions,
        ]);
    }

    // function teacher question bank for release store
    public function teacherQuestionBankForReleaseStore(Request $request, $role, $schoolName, $schoolId)
    {
        $validator = Validator::make($request->all(), [
            'school_assessment_id' => 'required',
            'question_id' => 'required|array|min:1',
            'question_id.*' => 'required|integer',
            'question_weight' => 'required|array|min:1',
            'question_weight.*' => 'required|integer',
        ], [
            'school_assessment_id.required' => 'Harap pilih rombel kelas.',
            'question_id.required' => 'Harap pilih soal.',
            'question_weight.*.required' => 'Harap pilih bobot soal.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        if ($request->total_weight > 100) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'total_weight' => ['Total bobot tidak boleh melebihi 100.']
                ],
            ], 422);
        } else if ($request->total_weight < 100) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'total_weight' => ['Total bobot tidak boleh kurang dari 100.']
                ],
            ], 422);
        }

        foreach ($request->question_id as $questionId) {

            $questionWeight = $request->question_weight[$questionId];

            $data = [
                'school_assessment_id' => $request->school_assessment_id,
                'question_bank_id' => $questionId,
                'question_weight' => $questionWeight
            ];

            SchoolAssessmentQuestion::create($data);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
        ]); 
    }

    public function paginateTeacherQuestionBankForRelease(Request $request, $role, $schoolName, $schoolId)
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

        $query = SchoolAssessmentQuestion::with(['SchoolAssessment', 'SchoolAssessment.SchoolClass', 'SchoolAssessment.Mapel',
            'SchoolAssessment.SchoolAssessmentType'])->orderBy('created_at', 'desc')->get();

        // TAHUN AJARAN
        $tahunAjaran = $query->pluck('SchoolAssessment.SchoolClass.tahun_ajaran')->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        // FILTER BERDASARKAN TAHUN AJARAN
        $schoolClasses = $query->where('SchoolAssessment.SchoolClass.tahun_ajaran', $searchYear)->values();

        // LEVEL KELAS UNIK
        $classLevels = $schoolClasses->pluck('SchoolAssessment.SchoolClass.class_name')->map(fn($c) => (int) $this->extractClassLevel($c))->unique()->sort()->values();

        $selectedClass = $request->filled('search_class') ? (int) $request->search_class : ($classLevels->first() ?? $defaultLevel);

        // FILTER ROMBEL SESUAI LEVEL
        $schoolClasses = $schoolClasses->filter(fn($item) => (int)$this->extractClassLevel($item->SchoolAssessment->SchoolClass->class_name) === $selectedClass)->values();

        // Filter berdasarkan level kelas
        if ($selectedClass) {
            $query = $query->filter(function ($item) use ($selectedClass) {

                if (!$item || !$item->SchoolAssessment->SchoolClass->class_name) {
                    return false;
                }

                return $this->extractClassLevel($item->SchoolAssessment->SchoolClass->class_name) == $selectedClass;
            });
        }

        $schoolAssessmentType = SchoolAssessmentType::where('school_partner_id', $schoolId)->get();

        // FILTER SEARCH ASSESSMENT TYPE
        if ($request->filled('search_assessment_type')) {
            $query = $query->filter(function ($item) use ($request) {
                return $item->SchoolAssessment->SchoolAssessmentType->id == $request->search_assessment_type;
            })->values();
        }

        // GROUP BY school_assessment_id
        $schoolAssessmentQuestion = $query->groupBy('school_assessment_id');

        // manual pagination karena sudah menjadi collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;

        $paginated = new LengthAwarePaginator(
            $schoolAssessmentQuestion->forPage($currentPage, $perPage)->values(),
            $schoolAssessmentQuestion->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        return response()->json([
            'data' => $paginated->items(),
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'selectedClass' => $selectedClass,
            'className'     => $classLevels,
            'schoolAssessmentType' => $schoolAssessmentType,
            'teacherReviewQuestionBankForRelease' => '/lms/:role/:schoolName/:schoolId/teacher-question-bank-for-release/review/:assessmentQuestionId'
        ]);
    }

    // function teacher review question bank for release
    public function teacherReviewQuestionBankForRelease(Request $request, $role, $schoolName, $schoolId, $assessmentQuestionId)
    {
        return view('features.lms.teacher.question-bank-for-release.teacher-review-question-bank-for-release', compact('role', 'schoolName', 'schoolId', 
            'assessmentQuestionId'));
    }

    public function paginateTeacherReviewQuestionBankForRelease(Request $request, $role, $schoolName, $schoolId, $assessmentQuestionId)
    {
        $user = Auth::user();

        $questions = SchoolAssessmentQuestion::with(['LmsQuestionBank', 'LmsQuestionBank.LmsQuestionOption'])->where('school_assessment_id', $assessmentQuestionId)->get();

        $videoIds = $questions->map(function ($q) {
            if (preg_match(
                '/youtu\.be\/([a-zA-Z0-9_-]{11})|youtube\.com\/.*v=([a-zA-Z0-9_-]{11})/',
                $q->explanation,
                $matches
            )) {
                return $matches[1] ?? $matches[2];
            }
            return null;
        });

        $response = [
            'data' => $questions,
            'videoIds' => $videoIds,
        ];

        return response()->json($response);
    }
}
