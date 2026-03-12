<?php

namespace App\Http\Controllers;

use App\Models\AssessmentMode;
use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolPartner;
use App\Models\TeacherMapel;
use App\Services\ClassName\ClassNameService;
use App\Services\LMS\AssessmentManagement\Teacher\LmsReviewFileService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TeacherAssessmentController extends Controller
{
    // CONSTRUCT LMS REVIEW CONTENT
    public function __construct(protected LmsReviewFileService $reviewFileService) 
    {}

    // function extract class level
    private function extractClassLevel($className)
    {
        $classNameService = new ClassNameService();
        return $classNameService->extractClassLevel($className);
    }
    
    // function teacher assessment management view
    public function teacherAssessmentManagement($role, $schoolName, $schoolId)
    {
        $schoolAssessmentType = SchoolAssessmentType::where('school_partner_id', $schoolId)->get();

        return view('features.lms.teacher.assessment.teacher-assessment', compact('role', 'schoolName', 'schoolId', 'schoolAssessmentType'));
    }

    // function teacher assessment management form
    public function teacherFormAssessmentManagement(Request $request, $role, $schoolName, $schoolId)
    {
        $teacherId = Auth::id();

        // VALIDASI SCHOOL
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

        $searchSubject = $request->filled('mapel_id') ? $request->mapel_id : null;

        // TEACHER MAPEL
        $baseQuery = TeacherMapel::where('user_id', $teacherId)
            ->where('is_active', true)
            ->whereHas('SchoolClass', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })
            ->whereHas('Mapel', function ($q) use ($schoolId) {
                // MAPEL KHUSUS SEKOLAH
                $q->whereHas('SchoolMapel', function ($q1) use ($schoolId) {
                    $q1->where('school_partner_id', $schoolId)
                        ->where('is_active', 1);
                })

                // ATAU MAPEL GLOBAL
                ->orWhere(function ($q2) use ($schoolId) {
                    $q2->whereNull('school_partner_id')->where('status_mata_pelajaran', 'active')

                        // JANGAN AMBIL JIKA ADA SCHOOL OVERRIDE
                        ->whereDoesntHave('SchoolMapel', function ($sq) use ($schoolId) {
                            $sq->where('school_partner_id', $schoolId);
                    });
                });
            })->with(['Mapel', 'SchoolClass' => function ($q) {
                    $q->withCount(['StudentSchoolClass as student_school_class_count' => function ($q) {
                        $q->where('student_class_status', 'active')
                        ->where(function ($sub) {
                            $sub->whereNull('academic_action')
                                ->orWhere('academic_action', '');
                        });
                    }]);
                }
            ]);

        $allData = $baseQuery->get();

        // TAHUN AJARAN
        $tahunAjaran = $allData->pluck('SchoolClass.tahun_ajaran')->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        $dataByYear = $allData->where('SchoolClass.tahun_ajaran', $searchYear)->values();

        // LEVEL KELAS UNIK
        $classLevels = $dataByYear->pluck('SchoolClass.class_name')->map(fn($c) => (int) $this->extractClassLevel($c))->unique()->sort()->values();

        $selectedClass = $request->filled('search_class') ? (int) $request->search_class : ($classLevels->first() ?? $defaultLevel);

        // FILTER ROMBEL SESUAI LEVEL
        $dataByClass = $dataByYear->filter(fn($item) => (int)$this->extractClassLevel($item->SchoolClass->class_name) === $selectedClass)->values();

        // AMBIL MAPEL GURU
        $subjects = $dataByClass->unique('mapel_id')->map(function ($item) {
            return [
                'id' => $item->mapel_id,
                'name' => $item->Mapel->mata_pelajaran ?? '-',
            ];
        })->values();

        $schoolClasses = $dataByClass->when($searchSubject, function ($collection) use ($searchSubject) {
            return $collection->where('mapel_id', $searchSubject);
        })->values();

        return response()->json([
            'tahunAjaran' => $tahunAjaran,
            'selectedYear' => $searchYear,
            'selectedClass' => $selectedClass,
            'className' => $classLevels,
            'rombel' => $schoolClasses,
            'subject' => $subjects,
        ]);
    }
    
    // function teacher assessment management validate step
    public function teacherFormAssessmentValidateStep(Request $request, $role, $schoolName, $schoolId)
    {
        $step = $request->input('step');

        // Default kosong
        $rules = [];
        $messages = [];

        $rules = [
            'school_class_id' => 'required|array|min:1',
            'school_class_id.*' => 'required|integer',
            'title' => 'required',
            'assessment_type_id' => 'required|integer|exists:school_assessment_types,id',
            'semester' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required',
        ];
        $messages = [
            'school_class_id.required' => 'Harap pilih kelas.',
            'title.required' => 'Harap isi judul asesmen.',
            'assessment_type_id.required' => 'Harap pilih tipe asesmen.',
            'semester.required' => 'Harap pilih semester.',
            'start_date.required' => 'Harap pilih tanggal mulai.',
            'end_date.required' => 'Harap pilih tanggal selesai.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Ambil assessment mode
        $assessmentMode = AssessmentMode::whereHas('SchoolAssessmentType', function ($q) use ($request) {
            $q->where('id', $request->assessment_type_id);
        })->first();

        if (!$assessmentMode) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'assessment_type_id' => ['Mode asesmen tidak ditemukan.']
                ],
            ], 422);
        }

        if ($step == 1) {
            if ($assessmentMode->code !== 'project') {
                $rules['duration'] = 'required|integer|min:1';
                $messages['duration.required'] = 'Harap isi durasi.';
            }

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            foreach ($request->school_class_id as $index => $classId) {
    
                $mapelId = $request->mapel_id[$index] ?? null;
    
                $exists = SchoolAssessment::where('school_class_id', $classId)->where('mapel_id', $mapelId)->where('semester', $request->semester)
                ->where('assessment_type_id', $request->assessment_type_id)->where('school_partner_id', $schoolId)->exists();
    
                if ($assessmentMode->code == 'exam') {
                    if ($exists) {
                        return response()->json([
                            'status' => 'error',
                            'errors' => [
                                'assessment_type_id' => ['Tipe asesmen telah terdaftar pada rombel kelas ini.']
                            ],
                        ], 422);
                    }
                }
            }
        } else if ($step == 2) {
            if ($assessmentMode->code === 'project') {
                $rules = [
                    'assessment_value_file' => 'required|mimes:pdf,mp4|max:100000',
                    'assessment_instruction' => 'required',
                ];
                $messages = [
                    'assessment_value_file.required' => 'File tidak boleh kosong.',
                    'assessment_value_file.mimes' => 'Format file tidak sesuai.',
                    'assessment_value_file.max' => 'File telah melebihi kapasitas yang ditentukan.',
                    'assessment_instruction' => 'Instruksi tidak boleh kosong.',
                ];
    
                $validator = Validator::make($request->all(), $rules, $messages);
        
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => $validator->errors(),
                    ], 422);
                }
            }
        }
    }

    // function teacher assessment management store
    public function teacherFormAssessmentManagementStore(Request $request, $role, $schoolName, $schoolId)
    {
        $user = Auth::user();

        // Ambil assessment mode
        $assessmentMode = AssessmentMode::whereHas('SchoolAssessmentType', function ($q) use ($request) {
            $q->where('id', $request->assessment_type_id);
        })->first();

        if ($assessmentMode->code === 'project') {

            $file = $request->file("assessment_value_file");

            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assessment/assessment-file'), $filename);
        }

        foreach ($request->school_class_id as $index => $schoolClassId) {

            $mapelId = $request->mapel_id[$index] ?? null;

            $data = [
                'user_id' => $user->id,
                'school_partner_id' => $schoolId,
                'school_class_id' => $schoolClassId,
                'mapel_id' => $mapelId,
                'assessment_type_id' => $request->assessment_type_id,
                'title' => $request->title,
                'description' => $request->description,
                'duration' => $request->duration,
                'semester' => $request->semester,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status ?: 'draft',
            ];

            if ($assessmentMode->code === 'project') {
                $data['assessment_value_file'] = $filename;
                $data['assessment_original_filename'] = $file->getClientOriginalName();
                $data['assessment_instruction'] = $request->assessment_instruction;
                $data['show_score'] = $request->boolean('show_project_score');
            } else {
                $data['shuffle_questions'] = $request->boolean('shuffle_questions');
                $data['shuffle_options'] = $request->boolean('shuffle_options');
                $data['show_answer'] = $request->boolean('show_answer');
                $data['show_score'] = $request->boolean('show_score');
            }

            SchoolAssessment::create($data);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
        ]);
    }

    // function paginate teacher assessment management
    public function paginateTeacherAssessmentManagement(Request $request, $role, $schoolName, $schoolId)
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
            ->where('user_id', $user->id)->where('school_partner_id', $schoolId)->orderBy('created_at', 'desc');

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

                return $this->extractClassLevel($item->SchoolClass->kelas_id) == $selectedClass;
            });
        }

        $schoolAssessmentType = SchoolAssessmentType::where('school_partner_id', $schoolId)->get();

        // FILTER SEARCH ASSESSMENT TYPE
        if ($request->filled('search_assessment_type')) {
            $schoolAssessment = $schoolAssessment->filter(function ($item) use ($request) {
                return $item->SchoolAssessmentType->id == $request->search_assessment_type;
            })->values();
        }

        // manual pagination karena sudah menjadi collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;

        $paginated = new LengthAwarePaginator(
            $schoolAssessment->forPage($currentPage, $perPage)->values(),
            $schoolAssessment->count(),
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
            'assessmentManagementEdit' => '/lms/:role/:schoolName/:schoolId/teacher-assessment-management/:assessmentId/edit'
        ]);
    }

    // function teacher assessment management edit view
    public function teacherAssessmentManagementEdit($role, $schoolName, $schoolId, $assessmentId)
    {
        $assessment = SchoolAssessment::findOrFail($assessmentId);

        return view('features.lms.teacher.assessment.teacher-assessment-edit', compact('role', 'schoolName', 'schoolId', 'assessmentId', 'assessment'));
    }

    // function teacher assessment management edit form
    public function teacherFormAssessmentManagementEdit(Request $request, $role, $schoolName, $schoolId, $assessmentId)
    {
        $data = $this->reviewFileService->getByAssessmentId($assessmentId);

        return response()->json([
            'data' => $data
        ]);
    }

    // function teacher assessment management edit submission
    public function teacherAssessmentManagementEditSubmission(Request $request, $role, $schoolName, $schoolId, $assessmentId)
    {
        $user = Auth::user();

        // Ambil assessment mode
        $assessmentMode = AssessmentMode::whereHas('SchoolAssessmentType', function ($q) use ($request) {
            $q->where('id', $request->assessment_type_id);
        })->first();

        $rules = [];
        $messages = [];

        // Base rules
        $rules = [
            'title' => 'required',
            'assessment_type_id' => 'required',
            'semester' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required',
        ];

        $messages = [
            'title.required' => 'Harap isi judul asesmen.',
            'semester.required' => 'Harap pilih semester.',
            'start_date.required' => 'Harap pilih tanggal mulai.',
            'end_date.required' => 'Harap pilih tanggal selesai.',
        ];
        
        if ($assessmentMode->code !== 'project') {
            $rules['duration'] = 'required|integer|min:1';
            $messages['duration.required'] = 'Harap isi durasi.';
        } else if ($assessmentMode->code === 'project') {
            $hasExisting = $request->input("existing_files") == 1;

            $rules['assessment_value_file'] = $hasExisting ? 'nullable|mimes:pdf,mp4|max:100000' : 'required|mimes:pdf,mp4|max:100000';
            $messages['assessment_value_file.required'] = 'Harap isi file asesmen.';
            $messages['assessment_value_file.mimes'] = 'Format file tidak sesuai.';
            $messages['assessment_value_file.max'] = 'File telah melebihi kapasitas yang ditentukan.';

            $rules['assessment_instruction'] = 'required';
            $messages['assessment_instruction.required'] = 'Instruksi tidak boleh kosong.';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $assessment = SchoolAssessment::findOrFail($assessmentId);

        if ($assessmentMode->code === 'project') {

            $file = $request->file("assessment_value_file");

            if ($file) {
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assessment/assessment-file'), $filename);
            }
        }

        $data = [
            'user_id' => $user->id,
            'school_partner_id' => $schoolId,
            'title' => $request->title,
            'description' => $request->description,
            'duration' => $request->duration,
            'semester' => $request->semester,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ];

        if ($assessmentMode->code === 'project') {
            if ($file) {
                $data['assessment_value_file'] = $filename;
                $data['assessment_original_filename'] = $file->getClientOriginalName();
            }
            $data['assessment_instruction'] = $request->assessment_instruction;
            $data['show_score'] = $request->boolean('show_score');
        } else {
            $data['shuffle_questions'] = $request->boolean('shuffle_questions');
            $data['shuffle_options'] = $request->boolean('shuffle_options');
            $data['show_answer'] = $request->boolean('show_answer');
            $data['show_score'] = $request->boolean('show_score');
        }

        $assessment->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diubah.',
        ]);
    }

    // function teacher assessment management activate
    public function teacherFormAssessmentManagementActivate(Request $request, $role, $schoolName, $schoolId, $assessmentId)
    {
        $assessment = SchoolAssessment::findOrFail($assessmentId);

        $assessment->update([
            'status' => $request->status,
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Status berhasil diubah.',
        ]);
    }
}
