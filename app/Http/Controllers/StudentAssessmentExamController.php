<?php

namespace App\Http\Controllers;

use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentQuestion;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentAssessmentAttempt;
use App\Models\StudentProjectSubmission;
use App\Models\StudentSchoolClass;
use App\Models\UserAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentAssessmentExamController extends Controller
{
    public function studentAssessmentExam($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        return view('features.lms.student.assessment.student-assessment-test', compact('role', 'schoolName', 'schoolId', 'curriculumId', 
            'mapelId', 'assessmentTypeId', 'semester', 'assessmentId'));
    }

    public function studentAssessmentExamForm($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId) 
    {
        $user = UserAccount::with('StudentProfile')->find(Auth::id());

        $assessment = SchoolAssessment::with(['SchoolAssessmentType', 'SchoolClass'])
            ->whereHas('SchoolClass.StudentSchoolClass', function ($query) use ($user) {
                $query->where('student_id', $user->id)->where('student_class_status', 'active');
            })->where('assessment_type_id', $assessmentTypeId)->where('semester', $semester)->first();

        if (!$assessment) {
            return response()->json(['data' => null]);
        }

        $publishedQuestionIds = SchoolAssessmentQuestion::where('school_assessment_id', $assessmentId)->pluck('id')->implode(',');

        $schoolAssessment = SchoolAssessment::where('id', $assessmentId)->first();

        $shuffleQuestions = $schoolAssessment->shuffle_questions;
        $shuffleOptions = $schoolAssessment->shuffle_options;

        $cacheKey = "assessment-{$user->id}-{$assessmentId}-{$publishedQuestionIds}-{$semester}-{$shuffleQuestions}-test";

        if (Cache::has($cacheKey)) {

            $cachedIds = Cache::get($cacheKey);

            $questions = SchoolAssessmentQuestion::with(['LmsQuestionBank', 'LmsQuestionBank.LmsQuestionOption', 'LmsQuestionBank.Mapel'])->whereIn('id', $cachedIds)->get()
            ->sortBy(function ($q) use ($cachedIds) {
                return array_search($q->id, $cachedIds);
            })
            ->values();

        } else {

            $baseQuery = SchoolAssessmentQuestion::with(['LmsQuestionBank', 'LmsQuestionBank.LmsQuestionOption', 'LmsQuestionBank.Mapel'
            ])->where('school_assessment_id', $assessmentId)->whereHas('LmsQuestionBank', function ($q) {
                $q->where('status_bank_soal', 'Publish');
            });

            if ($shuffleQuestions) {
                $questions = $baseQuery->get()->shuffle()->values();
            } else {
                $questions = $baseQuery->get();
            }

            $cachePayload = $questions->pluck('id')->toArray();

            Cache::put($cacheKey, $cachePayload, now()->addHours(3));
        }

        // SHUFFLE OPTIONS
        $questions->transform(function ($question) use ($user, $assessmentId, $semester, $shuffleOptions) {

            $type = strtoupper($question->LmsQuestionBank->tipe_soal ?? '');

            $options = $question->LmsQuestionBank->LmsQuestionOption;

            if (!$options) {
                return $question;
            }

            $publishedOptionIds = $options->pluck('id')->implode(',');

            $optionCacheKey = "assessment-option-{$user->id}-{$assessmentId}-{$question->id}-{$publishedOptionIds}-{$semester}-{$shuffleOptions}";

            // MCQ / MCMA
            if (in_array($type, ['MCQ','MCMA'])) {

                if (Cache::has($optionCacheKey)) {

                    $cachedIds = Cache::get($optionCacheKey);

                    $sorted = $options
                        ->whereIn('id', $cachedIds)
                        ->sortBy(function ($opt) use ($cachedIds) {
                            return array_search($opt->id, $cachedIds);
                        })
                        ->values();

                } else {

                    if ($shuffleOptions) {
                        $sorted = $options->shuffle()->values();
                    } else {
                        $sorted = $options;
                    }

                    Cache::put(
                        $optionCacheKey,
                        $sorted->pluck('id')->toArray(),
                        now()->addHours(3)
                    );
                }

                $question->LmsQuestionBank->setRelation(
                    'LmsQuestionOption',
                    $sorted
                );
            }

            // MATCHING
            if ($type === 'MATCHING') {

                $left = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side']) 
                        && $opt->extra_data['side'] === 'left';
                })->values();

                $right = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side']) 
                        && $opt->extra_data['side'] === 'right';
                })->values();

                $publishedRightIds = $right->pluck('id')->implode(',');

                $matchingCacheKey = "assessment-match-{$user->id}-{$assessmentId}-{$question->id}-{$publishedRightIds}-{$semester}-{$shuffleOptions}";

                if (Cache::has($matchingCacheKey)) {

                    $cachedIds = Cache::get($matchingCacheKey);

                    $right = $right
                        ->whereIn('id', $cachedIds)
                        ->sortBy(function ($opt) use ($cachedIds) {
                            return array_search($opt->id, $cachedIds);
                        })
                        ->values();

                } else {

                    if ($shuffleOptions) {
                        $right = $right->shuffle()->values();
                    } else {
                        $right = $right->values();
                    }

                    Cache::put(
                        $matchingCacheKey,
                        $right->pluck('id')->toArray(),
                        now()->addHours(3)
                    );
                }

                $shuffled = collect();

                foreach ($left as $l) {
                    $shuffled->push($l);
                }

                foreach ($right as $r) {
                    $shuffled->push($r);
                }

                $question->LmsQuestionBank->setRelation(
                    'LmsQuestionOption',
                    $shuffled
                );
            }

            return $question;
        });

        // STUDENT ANSWER
        $questionsAnswer = StudentAssessmentAnswer::where('student_id', $user->id)
            ->where('school_assessment_id', $assessmentId)
            ->get()
            ->mapWithKeys(function ($item) {

                $data = $item->attributesToArray();

                if (is_string($data['answer_value'])) {
                    $decoded = json_decode($data['answer_value'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data['answer_value'] = $decoded;
                    }
                }

                $question = SchoolAssessmentQuestion::with('LmsQuestionBank.LmsQuestionOption')
                    ->find($item->school_assessment_question_id);

                $isCorrect = false;

                if ($question) {

                    $type = $question->LmsQuestionBank->tipe_soal;

                    $correctOptions = $question->LmsQuestionBank->LmsQuestionOption
                        ->where('is_correct', 1)
                        ->pluck('options_key')
                        ->values()
                        ->toArray();

                    $studentAnswer = $data['answer_value'];

                    if ($type === 'MCQ') {
                        $isCorrect = $studentAnswer === ($correctOptions[0] ?? null);
                    }

                    if ($type === 'MCMA') {

                        if (!is_array($studentAnswer)) {
                            $isCorrect = false;
                        } else {

                            sort($correctOptions);
                            sort($studentAnswer);

                            $isCorrect = $studentAnswer === $correctOptions;
                        }
                    }

                    if ($type === 'MATCHING') {

                        if (is_string($studentAnswer)) {
                            $studentAnswer = json_decode($studentAnswer, true);
                        }

                        if (!is_array($studentAnswer)) {
                            $isCorrect = false;
                        } else {

                            $correctPairs = $question->LmsQuestionBank->LmsQuestionOption
                                ->filter(function ($opt) {
                                    return isset($opt->extra_data['side']) 
                                        && $opt->extra_data['side'] === 'left';
                                })
                                ->mapWithKeys(function ($opt) {
                                    return [
                                        trim($opt->options_key) =>
                                        trim($opt->extra_data['pair_with'] ?? '')
                                    ];
                                })
                                ->toArray();

                            $normalizedStudentAnswer = collect($studentAnswer)
                                ->mapWithKeys(function ($value, $key) {
                                    return [trim($key) => trim($value)];
                                })
                                ->toArray();

                            ksort($correctPairs);
                            ksort($normalizedStudentAnswer);

                            $isCorrect = $correctPairs === $normalizedStudentAnswer;
                        }
                    }
                }

                $data['is_correct'] = $isCorrect;

                return [
                    $item->school_assessment_question_id => $data
                ];
            });

        $schoolAssessment = SchoolAssessment::where('id', $assessmentId)->first();

        return response()->json([
            'data' => $questions,
            'questionsAnswer' => $questionsAnswer,
            'schoolAssessment' => $schoolAssessment,
            'user' => $user,
            'start_date' => $assessment->start_date ? $assessment->start_date->format('Y-m-d H:i') : null,
            'end_date' => $assessment->end_date ? $assessment->end_date->format('Y-m-d H:i') : null,
            'assessment_title' => $assessment->SchoolAssessmentType->name,
            'semester' => $assessment->semester,
            'resultTestHref' => '/lms/:role/:schoolName/:schoolId/curriculum/:curriculumId/subject/:mapelId/learning/assessment/:assessmentTypeId/semester/:semester/assessment/:assessmentId/result-test'
        ]);
    }

    public function startTImer($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        $userId = Auth::id();

        $assessment = SchoolAssessment::findOrFail($assessmentId);

        $attempt = StudentAssessmentAttempt::firstOrCreate(
            [
                'student_id' => $userId,
                'school_assessment_id' => $assessmentId
            ],
            [
                'start_time' => now(),
                'expire_time' => now()->addMinutes($assessment->duration),
                'status' => 'in_progress'
            ]
        );

        return response()->json([
            'start_time' => $attempt->start_time->timestamp * 1000,
            'expire_time' => $attempt->expire_time->timestamp * 1000,
            'duration' => $assessment->duration
        ]);
    }

    public function reportTabSwitch($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        $attempt = StudentAssessmentAttempt::where('student_id', Auth::id())->where('school_assessment_id', $assessmentId)->first();

        if (!$attempt) {
            return response()->json(['status' => 'error']);
        }

        $attempt->increment('tab_switch_count');

        if ($attempt->tab_switch_count >= 3) {

            $attempt->update([
                'status' => 'cheating',
            ]);

            return response()->json([
                'status' => 'blocked'
            ]);
        }

        return response()->json([
            'status' => 'warning',
            'count' => $attempt->tab_switch_count
        ]);
    }

    public function checkAttemptStatus($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        $attempt = StudentAssessmentAttempt::where('student_id', Auth::id())
            ->where('school_assessment_id', $assessmentId)
            ->first();

        if (!$attempt) {
            return response()->json([
                'status' => 'error'
            ]);
        }

        if ($attempt->status === 'cheating') {
            return response()->json([
                'status' => 'blocked',
                'count' => $attempt->tab_switch_count
            ]);
        }

        if ($attempt->tab_switch_count > 0) {
            return response()->json([
                'status' => 'warning',
                'count' => $attempt->tab_switch_count
            ]);
        }

        return response()->json([
            'status' => 'ok'
        ]);
    }

    public function studentProjectSubmission(Request $request, $role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        $assessment = SchoolAssessment::findOrFail($assessmentId);

        $isExpired = now()->greaterThan($assessment->end_date);
        $isBeforeStart = now()->lessThan($assessment->start_date);

        if ($isBeforeStart) {
            return response()->json([
                'status' => 'not_started',
                'message' => 'Asesmen belum dimulai.'
            ], 422);
        }

        if ($isExpired) {
            return response()->json([
                'status' => 'expired',
                'message' => 'Asesmen sudah berakhir.'
            ], 422);
        }
    
        $validator = Validator::make($request->all(), [

            'submission_type' => [
                'required', 'in:file,text'
            ],

            'project_file' => [
                'nullable', 'required_if:submission_type,file', 'file', 'mimes:pdf', 'max:100000'
            ],

            'project_text' => [
                'nullable', 'required_if:submission_type,text'
            ]

        ], [
            'project_file.required_if' => 'Harap upload file asesmen.',
            'project_file.mimes' => 'Format file tidak sesuai.',
            'project_file.max' => 'Ukuran file tidak boleh lebih dari 100 MB.',
            'project_text.required_if' => 'Harap isi deskripsi asesmen.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $submissionType = $request->submission_type;

        $data = [
            'student_id' => Auth::id(),
            'school_assessment_id' => $assessmentId,
            'submission_type' => $submissionType
        ];

        if ($submissionType === 'file') {
            $file = $request->file("project_file");

            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assessment/assessment-file-submission'), $filename);

            $data['file_path'] = $filename;
            $data['original_filename'] = $file->getClientOriginalName();
        } else {
            $data['text_answer'] = $request->project_text;
        }

        StudentProjectSubmission::firstOrCreate(
            [
                'student_id' => Auth::id(),
                'school_assessment_id' => $assessmentId
            ],
            $data
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Asesmen berhasil disubmit.',
        ]);
    }
    
    public function studentAssessmentExamAnswer(Request $request, $role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        $userId = Auth::id();

        $assessment = SchoolAssessment::findOrFail($assessmentId);

        $isExpired = now()->greaterThan($assessment->end_date);

        if ($isExpired && !$request->auto_submit) {

            // ubah request agar dianggap auto submit
            $request->merge([
                'auto_submit' => true,
                'status_attempt' => 'timeout'
            ]);

        }

        $validator = Validator::make($request->all(), [
            'school_assessment_question_id' => 'required|exists:school_assessment_questions,id',
            'answer_value' => [
                Rule::requiredIf(!$request->auto_submit)
            ],
            'status_answer' => 'required|in:draft,submitted',
        ], [
            'answer_value.required' => 'Jawaban tidak boleh kosong.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ambil soal
        $schoolQuestion = SchoolAssessmentQuestion::with('lmsQuestionBank')
            ->findOrFail($request->school_assessment_question_id);

        $question = $schoolQuestion->lmsQuestionBank;

        // cari jawaban siswa
        $answer = StudentAssessmentAnswer::where('student_id', $userId)->where('school_assessment_question_id', $request->school_assessment_question_id)
            ->where('school_assessment_id', $assessmentId)->first();

        // normalisasi answer_value
        $answerData = $request->answer_value;

        // decode JSON jika string
        if (is_string($answerData)) {
            $decoded = json_decode($answerData, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $answerData = $decoded;
            }
        }

        // jika kosong -> simpan NULL
        if ($answerData === '' || $answerData === [] || $answerData === null) {
            $answerData = null;
        }

        // hitung score
        $score = 0;

        if ($answerData !== null) {

            switch ($question->tipe_soal) {

                // MCQ
                case 'MCQ':

                    $correctOption = $question->lmsQuestionOption()
                        ->where('is_correct', 1)
                        ->first();

                    if ($correctOption && $answerData === $correctOption->options_key) {
                        $score = $schoolQuestion->question_weight;
                    }

                break;

                // MCMA
                case 'MCMA':

                    if (is_array($answerData)) {

                        $correctOptions = $question->lmsQuestionOption()->where('is_correct', 1)->pluck('options_key')->toArray();

                        sort($correctOptions);
                        sort($answerData);

                        if ($correctOptions === $answerData) {
                            $score = $schoolQuestion->question_weight;
                        }

                    }

                break;

                // MATCHING
                case 'MATCHING':

                    if (is_array($answerData)) {

                        $correctPairs = $question->lmsQuestionOption()
                            ->get()
                            ->filter(function ($opt) {
                                return isset($opt->extra_data['side'])
                                    && $opt->extra_data['side'] === 'left';
                            })
                            ->mapWithKeys(function ($opt) {
                                return [
                                    $opt->options_key => $opt->extra_data['pair_with'] ?? null
                                ];
                            })
                            ->toArray();

                        ksort($correctPairs);
                        ksort($answerData);

                        if ($correctPairs === $answerData) {
                            $score = $schoolQuestion->question_weight;
                        }

                    }

                break;

                // ESSSAY
                case 'ESSAY':

                    $score = 0;

                break;
            }
        }

        // simpan jawaban
        if ($answer) {

            $updateData = [
                'status_answer' => $request->status_answer,
                'answer_duration' => $request->answer_duration,
            ];

            // hanya update jika dikirim
            if ($request->has('answer_value')) {
                $updateData['answer_value'] = $answerData;
                $updateData['question_score'] = $score;
            }

            if ($request->filled('total_exam_duration')) {
                $updateData['total_exam_duration'] = $request->total_exam_duration;
            }

            $answer->update($updateData);

        } else {

            $data = [
                'student_id' => $userId,
                'school_assessment_id' => $assessmentId,
                'school_assessment_question_id' => $request->school_assessment_question_id,
                'answer_value' => $answerData,
                'question_score' => $score,
                'answer_duration' => $request->answer_duration,
                'status_answer' => $request->status_answer,
                'grading_status' => $question->tipe_soal === 'ESSAY' ? 'pending' : null,
            ];

            if ($request->filled('total_exam_duration')) {
                $data['total_exam_duration'] = $request->total_exam_duration;
            }

            StudentAssessmentAnswer::create($data);
        }

        // auto submit
        $totalQuestions = SchoolAssessmentQuestion::where('school_assessment_id', $assessmentId)->count();

        $totalAnswers = StudentAssessmentAnswer::where('student_id', $userId)
            ->where('school_assessment_id', $assessmentId)
            ->count();

        if ($request->status_answer === 'submitted' && $totalAnswers === $totalQuestions) {
            if ($request->filled('total_exam_duration')) {
                StudentAssessmentAnswer::where('student_id', $userId)->where('school_assessment_id', $assessmentId)
                ->update([
                    'total_exam_duration' => $request->total_exam_duration
                ]);

            }
        }

        $attempt = StudentAssessmentAttempt::where('student_id', Auth::id())
            ->where('school_assessment_id', $assessmentId)
            ->first();

        if ($attempt && $attempt->status === 'in_progress') {

            $attempt->update([
                'status' => $request->status_attempt
            ]);

        }

        if ($isExpired) {

            return response()->json([
                'status' => 'expired',
                'message' => 'Waktu ujian telah berakhir. Jawaban otomatis disubmit.'
            ], 422);

        }


        return response()->json([
            'status' => 'success',
            'message' => 'Jawaban berhasil disimpan',
        ]);
    }

    // function submit essay (for ckeditor)
    public function storeImageEssay(Request $request) {
        if ($request->hasFile('upload')) {
            $originName = $request->file('upload')->getClientOriginalName();
            $fileName = pathInfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('upload')->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;

            $request->file('upload')->move(public_path('lms-assessment-submission/assessment-test'), $fileName);

            $url = "/lms-assessment-submission/assessment-test/$fileName";
            return response()->json(['fileName' => $fileName, 'uploaded' => 1, 'url' => $url]);
        }
    }

    // function delete image bank soal (for ckeditor)
    public function deleteImageEssay(Request $request) {
        $request->validate([
            'imageUrl' => 'required|url',
        ]);

        $imagePath = str_replace(asset(''), '', $request->imageUrl); // Hapus base URL
        $fullImagePath = public_path($imagePath);

        if (file_exists($fullImagePath)) {
            unlink($fullImagePath); // Hapus gambar
            return response()->json(['message' => 'Gambar berhasil dihapus']);
        }

        return response()->json(['message' => 'Gambar tidak ditemukan'], 404);
    }

    public function studentResultAssessment($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId) 
    {
        $user = UserAccount::with(['StudentProfile','StudentSchoolClass'])->findOrFail(Auth::id());
        $classId = $user->StudentSchoolClass[0]->school_class_id; // Ambil class id

        // DATA JAWABAN USER
        $answers = StudentAssessmentAnswer::where('student_id',$user->id)->where('school_assessment_id',$assessmentId)->get();
        $totalQuestionsExam = $answers->count();
        $totalCorrect = $answers->where('question_score','>',0)->count();
        $totalWrong = $answers->where('question_score',0)->where('status_answer','submitted')->count();
        $totalUnanswered = $answers->where('status_answer','draft')->count();
        $totalPendingEssay = $answers->where('status_answer','submitted')->where('grading_status','pending')->count();

        // SCORE
        $finalScore = $answers->whereNotNull('question_score')->sum('question_score');
        $maxScore = 100;
        $percentage = $maxScore > 0 ? round(($finalScore / $maxScore) * 100, 2) : 0;

        // DURASI USER
        $durations = $answers->where('answer_duration','>',0)->pluck('answer_duration');
        $fastestRaw = $durations->min() ?? 0;
        $slowestRaw = $durations->max() ?? 0;
        $totalDurationRaw = $answers->where('answer_duration','>',0)->pluck('total_exam_duration')->first() ?? 0;

        // FORMAT DURASI
        $formatDuration = function($seconds){

            if($seconds <= 0) return '-';

            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $secondsRemain = $seconds % 60;

            $parts = [];

            if($hours > 0) $parts[] = $hours.' jam';
            if($minutes > 0) $parts[] = $minutes.' menit';
            if($secondsRemain > 0) $parts[] = $secondsRemain.' detik';

            return implode(' ',$parts);
        };

        $fastest = $formatDuration($fastestRaw); // Format tercepat
        $slowest = $formatDuration($slowestRaw); // Format terlambat
        $totalDuration = $formatDuration($totalDurationRaw); // Total durasi

        // CONFIDENCE SCORE
        $answered = $totalCorrect + $totalWrong; // Total dijawab
        $accuracy = $answered > 0 ? $totalCorrect / $answered : 0; // Akurasi jawaban benar
        $completion = $totalQuestionsExam > 0 ? $answered / $totalQuestionsExam : 0; // Completion
        $confidence = round(($accuracy * 0.8 + $completion * 0.2) * 100, 2); // Confidence awal

        // TOTAL SISWA DI KELAS
        $totalStudents = StudentSchoolClass::where('school_class_id',$classId)->count();

        // SISWA YANG MENGERJAKAN
        $participants = StudentAssessmentAnswer::where('school_assessment_id', $assessmentId)
        ->whereIn('student_id', function ($q) use ($classId) {
            $q->select('student_id')->from('student_school_classes')->where('school_class_id', $classId);
        })->distinct('student_id')->count('student_id');

        // RANKING TOTAL DURASI
        $allDurations = StudentAssessmentAnswer::where('school_assessment_id',$assessmentId)->where('answer_duration','>',0)
        ->selectRaw('student_id, SUM(answer_duration) as total_duration')->groupBy('student_id')->orderBy('total_duration')->get();

        $uniqueTotalDurations = $allDurations->pluck('total_duration')->unique()->sort()->values();

        $userTotalDuration = $allDurations->firstWhere('student_id',$user->id)->total_duration ?? null;

        $rankDuration = $userTotalDuration !== null ? $uniqueTotalDurations->search($userTotalDuration) + 1 : null;

        // cek apakah ada siswa yang sudah mengerjakan test atau belum
        if ($rankDuration !== null) {
            if ($participants == 1) {
                $percentileDuration = 100; // hanya satu siswa -> 100%
            } else {
                $percentileDuration = round((($participants - $rankDuration) / ($participants - 1)) * 100, 2);
            }
        } else {
            $percentileDuration = 0; // jika siswa tidak mengerjakan
        }

        // TERCEPAT PER SOAL
        $fastestQuestion = StudentAssessmentAnswer::where('school_assessment_id',$assessmentId)->where('answer_duration','>',0)
        ->selectRaw('student_id, MIN(answer_duration) as duration')->groupBy('student_id')->orderBy('duration')->get();

        $uniqueFastestDurations = $fastestQuestion->pluck('duration')->unique()->sort()->values();

        $userFastestDuration = $fastestQuestion->firstWhere('student_id',$user->id)->duration ?? null;

        $rankFastest = $userFastestDuration !== null ? $uniqueFastestDurations->search($userFastestDuration) + 1 : null;

        // cek apakah ada siswa yang sudah mengerjakan test atau belum
        if ($rankFastest !== null) {
            if ($participants == 1) {
                $percentileFastest = 100; // hanya satu siswa -> 100%
            } else {
                $percentileFastest = round((($participants - $rankFastest) / ($participants - 1)) * 100, 2);
            }
        } else {
            $percentileFastest = 0; // jika siswa tidak mengerjakan
        }

        // TERLAMA PER SOAL
        $slowestQuestion = StudentAssessmentAnswer::where('school_assessment_id',$assessmentId)->where('answer_duration','>',0)
        ->selectRaw('student_id, MAX(answer_duration) as duration')->groupBy('student_id')->orderBy('duration')->get();

        $uniqueSlowestDuration = $slowestQuestion->pluck('duration')->unique()->sortDesc()->values();

        $userSlowestDuration = $slowestQuestion->firstWhere('student_id',$user->id)->duration ?? null;

        $rankSlowest = $userSlowestDuration !== null ? $uniqueSlowestDuration->search($userSlowestDuration) + 1 : null;

        // cek apakah ada siswa yang sudah mengerjakan test atau belum
        if ($rankSlowest !== null) {
            if ($participants == 1) {
                $percentileSlowest = 100; // hanya satu siswa -> 100%
            } else {
                $percentileSlowest = round((($participants - $rankSlowest) / ($participants - 1)) * 100, 2);
            }
        } else {
            $percentileSlowest = 0; // jika siswa tidak mengerjakan
        }

        // CONFIDENCE RANK
        $totalQuestions = StudentAssessmentAnswer::where('school_assessment_id',$assessmentId)->distinct('school_assessment_question_id')
        ->count('school_assessment_question_id');

        $allConfidence = StudentAssessmentAnswer::where('school_assessment_id',$assessmentId)
        ->selectRaw('student_id, SUM(CASE WHEN question_score > 0 THEN 1 ELSE 0 END) as correct, SUM(CASE WHEN status_answer = "submitted" THEN 1 ELSE 0 END) as answered,
            SUM(answer_duration) as total_duration')->groupBy('student_id')
        ->get();

        $durations = $allConfidence->pluck('total_duration');
        $minDuration = $durations->min();
        $maxDuration = $durations->max();

        $allConfidence = $allConfidence->map(function($row) use ($totalQuestions,$minDuration,$maxDuration){

            $duration = $row->total_duration ?? 0;

            $accuracy = $row->answered > 0 ? $row->correct / $row->answered : 0; // Akurasi

            $completion = $totalQuestions > 0 ? min($row->answered / $totalQuestions, 1) : 0; // Completion

            if($maxDuration == $minDuration){
                $speed = 1;
            } else {
                $speed = ($maxDuration - $duration) / ($maxDuration - $minDuration);
            }

            $speed = max(min($speed,1),0);

            $confidence = min(($accuracy * 0.7 + $completion * 0.2 + $speed * 0.1) * 100, 100); // Hitung confidence

            return [
                'student_id'=> $row->student_id,
                'confidence'=> round($confidence,2)
            ];

        })
        ->sortByDesc('confidence')
        ->values();

        $uniqueConfidence = $allConfidence->pluck('confidence')->unique()->sortDesc()->values();

        $userConfidenceRow = $allConfidence->firstWhere('student_id',$user->id);

        $userConfidence = $userConfidenceRow['confidence'] ?? null;

        $confidence = $userConfidence;

        $rankConfidence = $userConfidence !== null ? $uniqueConfidence->search($userConfidence) + 1 : null;

        // cek apakah ada siswa yang sudah mengerjakan test atau belum
        if ($rankConfidence !== null) {
            if ($participants == 1) {
                $percentileConfidence = 100; // hanya satu siswa -> 100%
            } else {
                $percentileConfidence = round((($participants - $rankConfidence) / ($participants - 1)) * 100, 2);
            }
        } else {
            $percentileConfidence = 0; // jika siswa tidak mengerjakan
        }

        $isFullyGraded = $totalPendingEssay === 0;

        $schoolAssessment = SchoolAssessment::find($assessmentId);

        return view('features.lms.student.assessment.student-assessment-result-test',compact('role','schoolName', 'schoolId', 'curriculumId', 'mapelId', 
            'assessmentTypeId', 'semester', 'assessmentId', 'user','totalQuestions', 'totalCorrect', 'totalWrong', 'totalUnanswered', 'totalPendingEssay', 'finalScore', 'percentage',
                'isFullyGraded', 'schoolAssessment', 'fastest', 'slowest', 'totalDuration', 'confidence', 'percentileDuration', 'percentileFastest', 'percentileSlowest', 'percentileConfidence',
                'participants', 'totalStudents'
            )
        );
    }

    public function studentProjectResult($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        $user = Auth::user();

        $submission = StudentProjectSubmission::where('student_id', $user->id)
            ->where('school_assessment_id', $assessmentId)
            ->first();

        $score = $submission->score ?? 0;

        // cek apakah sudah dinilai
        $isFullyGraded = $submission && $submission->grading_status === 'graded';

        $schoolAssessment = SchoolAssessment::find($assessmentId);

        return view('features.lms.student.assessment.student-project-assessment-result', compact('role', 'schoolName', 'schoolId', 'curriculumId', 
            'mapelId', 'assessmentTypeId', 'semester', 'assessmentId', 'submission', 'score', 'isFullyGraded', 'schoolAssessment'));
    }
}
