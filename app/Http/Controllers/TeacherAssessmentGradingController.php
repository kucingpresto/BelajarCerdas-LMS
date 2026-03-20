<?php

namespace App\Http\Controllers;

use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentQuestion;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolPartner;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentProjectSubmission;
use App\Models\StudentSchoolClass;
use App\Models\UserAccount;
use App\Services\ClassName\ClassNameService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TeacherAssessmentGradingController extends Controller
{
    // function extract class level
    private function extractClassLevel($className)
    {
        $classNameService = new ClassNameService();
        return $classNameService->extractClassLevel($className);
    }

    public function assessmentGradingManagement($role, $schoolName, $schoolId)
    {
        return view('features.lms.teacher.assessment-grading.teacher-assessment-grading-management',compact('role', 'schoolName', 'schoolId'));
    }

    public function paginateAssessmentGrading(Request $request, $role, $schoolName, $schoolId)
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

        $query = SchoolAssessment::with(['Mapel', 'SchoolClass', 'SchoolAssessmentType'])->where('user_id', $user->id)->latest();
        
        $assessments = $query->get();

        // TAHUN AJARAN
        $tahunAjaran = $assessments->pluck('SchoolClass.tahun_ajaran')->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        // FILTER BERDASARKAN TAHUN AJARAN
        $schoolClasses = $assessments->where('SchoolClass.tahun_ajaran', $searchYear)->values();

        // LEVEL KELAS UNIK
        $classLevels = $schoolClasses->pluck('SchoolClass.class_name')->map(fn($c) => (int) $this->extractClassLevel($c))->unique()->sort()->values();

        $selectedClass = $request->filled('search_class') ? (int) $request->search_class : ($classLevels->first() ?? $defaultLevel);

        // FILTER ROMBEL SESUAI LEVEL
        $schoolClasses = $schoolClasses->filter(fn($item) => (int)$this->extractClassLevel($item->SchoolClass->class_name) === $selectedClass)->values();

        // Filter berdasarkan level kelas
        if ($selectedClass) {
            $assessments = $assessments->filter(function ($item) use ($selectedClass) {

                if (!$item || !$item->SchoolClass->class_name) {
                    return false;
                }

                return $this->extractClassLevel($item->SchoolClass->class_name) == $selectedClass;
            });
        }

        $schoolAssessmentType = SchoolAssessmentType::where('school_partner_id', $schoolId)->get();

        // FILTER SEARCH ASSESSMENT TYPE
        if ($request->filled('search_assessment_type')) {
            $assessments = $assessments->filter(function ($item) use ($request) {
                return $item->SchoolAssessmentType->id == $request->search_assessment_type;
            })->values();
        }

        // manual pagination karena sudah menjadi collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;

        $paginated = new LengthAwarePaginator(
            $assessments->forPage($currentPage, $perPage)->values(),
            $assessments->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        $paginated->getCollection()->transform(function ($assessment) {

            // total siswa aktif di kelas
            $totalStudents = StudentSchoolClass::where('school_class_id', $assessment->school_class_id)->where('student_class_status', 'active')->where(function ($q) {
                $q->whereNull('academic_action')->orWhere('academic_action', '');
            })->count();

            // TOTAL SUBMISSION
            $examStudents = StudentAssessmentAnswer::where('school_assessment_id', $assessment->id)->where('status_answer', 'submitted')->distinct()->pluck('student_id');

            $projectStudents = StudentProjectSubmission::where('school_assessment_id', $assessment->id)->distinct()->pluck('student_id');

            $submissionStudents = $examStudents->merge($projectStudents)->unique();

            $submissionCount = $submissionStudents->count();

            // TOTAL PENDING GRADING
            $pendingExamStudents = StudentAssessmentAnswer::where('school_assessment_id', $assessment->id)->where('status_answer', 'submitted')->where('grading_status', 'pending')
                ->distinct()->pluck('student_id');

            $pendingProjectStudents = StudentProjectSubmission::where('school_assessment_id', $assessment->id)->where('grading_status', 'pending')->distinct()
                ->pluck('student_id');

            $pendingStudents = $pendingExamStudents->merge($pendingProjectStudents)->unique();

            $pendingCount = $pendingStudents->count();

            // FINAL DATA
            $assessment->total_students = $totalStudents;
            $assessment->submission_count = $submissionCount;
            $assessment->pending_count = $pendingCount;

            $assessment->grading_status = $pendingCount > 0 ? 'pending' : 'completed';

            return $assessment;
        });

        return response()->json([
            'data' => $paginated->items(),
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'selectedClass' => $selectedClass,
            'className'     => $classLevels,
            'schoolAssessmentType' => $schoolAssessmentType,
            'assessmentGradingStudentList' => '/lms/:role/:schoolName/:schoolId/assessment-grading/:assessmentId/student-list'
        ]);
    }

    public function assessmentGradingStudentList($role, $schoolName, $schoolId, $assessmentId)
    {
        return view('features.lms.teacher.assessment-grading.teacher-assessment-grading-student-list',compact('role', 'schoolName', 'schoolId', 'assessmentId'));
    }

    public function paginateAssessmentGradingStudentList(Request $request, $role, $schoolName, $schoolId, $assessmentId)
    {
        $assessment = SchoolAssessment::with(['SchoolClass', 'Mapel', 'SchoolAssessmentType.AssessmentMode'])->findOrFail($assessmentId);

        $query = StudentSchoolClass::with(['UserAccount.StudentProfile'])->where('student_class_status', 'active')->where(function ($sub) {
            $sub->whereNull('academic_action')->orWhere('academic_action', '');
        })->where('school_class_id', $assessment->school_class_id);

        // FILTER SEARCH MATERI
        if ($request->filled('search_student')) {
            $query->whereHas('UserAccount.StudentProfile', function ($q) use ($request) {
                $q->where('nama_lengkap', 'LIKE', '%' . $request->search_student . '%');
            });
        }

        $students = $query->get()->sortBy(function ($item) {
            return strtolower($item->UserAccount->StudentProfile->nama_lengkap ?? '');
        })->values();

        $students->map(function ($item) use ($assessmentId, $assessment) {

            $studentId = $item->student_id;

            $answers = StudentAssessmentAnswer::where('school_assessment_id', $assessmentId)->where('student_id', $studentId)->where('status_answer', 'submitted')->get();

            $project = StudentProjectSubmission::where('school_assessment_id', $assessmentId)->where('student_id', $studentId)->first();

            $submission = ($answers->count() > 0 || $project) ? 'Submit' : 'Tidak Submit';

            $score = $answers->sum('question_score');
            $gradingStatus = null;

            if ($assessment->SchoolAssessmentType->AssessmentMode->code === 'project') {

                if ($project) {
                    $score += $project->score ?? 0;
                    $gradingStatus = $project->grading_status === 'pending' ? 'Sementara' : 'Final';
                }

            } else {

                if ($answers->count() > 0) {
                    $pending = $answers->where('grading_status', 'pending')->count();
                    $gradingStatus = $pending > 0 ? 'Sementara' : 'Final';
                }

            }

            $item->submission_status = $submission;
            $item->score = $score;
            $item->grading_status = $gradingStatus;

            return $item;
        });

        // hitung total siswa di kelas
        $totalStudents = $students->count();

        $submittedCount = $students->where('submission_status', 'Submit')->count();

        $notSubmittedCount = $students->where('submission_status', 'Tidak Submit')->count();

        $pendingScoreCount = $students->where('grading_status', 'Sementara')->count();

        $finalScoreCount = $students->where('grading_status', 'Final')->count();
        
        return response()->json([
            'data' => $students,
            'assessment' => $assessment,
            'statistics' => [
                'total_students' => $totalStudents,
                'submitted' => $submittedCount,
                'not_submitted' => $notSubmittedCount,
                'pending_score' => $pendingScoreCount,
                'final_score' => $finalScoreCount
            ],
            'assessmentGradingStudentAnswer' => '/lms/:role/:schoolName/:schoolId/assessment-grading/:assessmentId/student-list/:studentId/scoring'
        ]);
    }

    public function assessmentGradingStudentAnswer($role, $schoolName, $schoolId, $assessmentId, $studentId)
    {
        $assessment = SchoolAssessment::with(['SchoolAssessmentType.AssessmentMode', 'Mapel'])->findOrFail($assessmentId);

        if ($assessment->SchoolAssessmentType->AssessmentMode->code === 'project') {
            return view('features.lms.teacher.assessment-grading.teacher-assessment-grading-student-project-detail', compact('role', 'schoolName', 'schoolId', 'assessmentId', 'studentId'));
        } else {
            return view('features.lms.teacher.assessment-grading.teacher-assessment-grading-student-answer-detail', compact('role', 'schoolName', 'schoolId', 'assessmentId', 'studentId'));
        }
    }

    public function paginateAssessmentGradingStudentAnswer($role, $schoolName, $schoolId, $assessmentId, $studentId)
    {
        $assessment = SchoolAssessment::with(['SchoolClass', 'Mapel'])->findOrFail($assessmentId);

        $schoolAssessmentQuestion = SchoolAssessmentQuestion::with(['LmsQuestionBank.LmsQuestionOption', 'StudentAssessmentAnswer' => function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
            }
        ])->where('school_assessment_id', $assessmentId)->get();

        // STUDENT ANSWER
        $questionsAnswer = StudentAssessmentAnswer::where('student_id', $studentId)->where('school_assessment_id', $assessmentId)->get()->keyBy('school_assessment_question_id');;

        $student = UserAccount::with(['studentProfile', 'StudentSchoolClass.SchoolClass'])->findOrFail($studentId);

        $students = StudentSchoolClass::where('school_class_id', $assessment->school_class_id)->orderBy('student_id')->get()->sortBy(function ($item) {
            return strtolower($item->UserAccount->StudentProfile->nama_lengkap ?? '');
        })->pluck('student_id')->values();

        $currentIndex = $students->search($studentId);

        $previousStudent = $students[$currentIndex - 1] ?? null;
        $nextStudent = $students[$currentIndex + 1] ?? null;

        return response()->json([
            'data' => $schoolAssessmentQuestion,
            'questionsAnswer' => $questionsAnswer,
            'assessment' => $assessment,
            'student' => $student,
            'previousStudent' => $previousStudent,
            'nextStudent' => $nextStudent
        ]);
    }

    public function submitAssessmentStudentScore(Request $request, $role, $schoolName, $schoolId, $assessmentId, $studentId, $schoolAssessmentQuestionId)
    {
        $validator = Validator::make(request()->all(), [
            'question_score' => 'required',
        ], [
            'question_score.required' => 'Nilai harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $schoolAssessmentQuestion = SchoolAssessmentQuestion::findOrFail($schoolAssessmentQuestionId);

        if ($request->question_score < 0) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'question_score' => ['Nilai tidak dapat kurang dari 0.']
                ],
            ], 422);
        } else if ($request->question_score > $schoolAssessmentQuestion->question_weight) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'question_score' => ['Nilai tidak dapat melebihi nilai bobot soal.']
                ],
            ], 422);
        }

        $studentAssessmentAnswer = StudentAssessmentAnswer::where('school_assessment_question_id', $schoolAssessmentQuestionId)
        ->where('school_assessment_id', $assessmentId)->where('student_id', $studentId)->first();

        $studentAssessmentAnswer->update([
            'question_score' => $request->question_score,
            'grading_status' => 'graded',
            'teacher_feedback' => $request->teacher_feedback,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Nilai berhasil disimpan.',
        ], 200);
    }

    public function paginateAssessmentGradingStudentProject($role, $schoolName, $schoolId, $assessmentId, $studentId)
    {
        $assessment = SchoolAssessment::with(['SchoolClass','Mapel'])->findOrFail($assessmentId);

        $submission = StudentProjectSubmission::where('school_assessment_id', $assessmentId)->where('student_id', $studentId)->first();

        $student = UserAccount::with(['studentProfile', 'StudentSchoolClass.SchoolClass'])->findOrFail($studentId);

        $students = StudentSchoolClass::where('school_class_id',$assessment->school_class_id)->orderBy('student_id')->pluck('student_id')->values();

        $currentIndex = $students->search($studentId);

        $previousStudent = $students[$currentIndex - 1] ?? null;
        $nextStudent = $students[$currentIndex + 1] ?? null;

        return response()->json([
            'assessment' => $assessment,
            'student' => $student,
            'submission' => $submission,
            'previousStudent' => $previousStudent,
            'nextStudent' => $nextStudent
        ]);
    }

    public function submitAssessmentStudentProjectScore(Request $request, $role, $schoolName, $schoolId, $assessmentId, $studentId, $submissionId)
    {
        $validator = Validator::make(request()->all(), [
            'score' => 'required',
        ], [
            'score.required' => 'Nilai harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $submission = StudentProjectSubmission::findOrFail($submissionId);

        if ($request->score < 0) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'score' => ['Nilai tidak dapat kurang dari 0.']
                ],
            ], 422);
        } else if ($request->score > 100) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'score' => ['Nilai tidak dapat lebih dari 100.']
                ],
            ], 422);
        }

        $submission->update([
            'score' => $request->score,
            'grading_status' => 'graded',
            'teacher_feedback' => $request->teacher_feedback,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Nilai berhasil disimpan.',
        ], 200);
    }
}
