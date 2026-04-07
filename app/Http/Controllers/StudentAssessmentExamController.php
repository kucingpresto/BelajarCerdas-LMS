<?php

namespace App\Http\Controllers;

use App\Models\SchoolAssessment;
use App\Models\SchoolAssessmentQuestion;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentAssessmentAttempt;
use App\Models\StudentAssessmentSummary;
use App\Models\StudentProjectSubmission;
use App\Models\StudentSchoolClass;
use App\Models\SubjectPassingGradeCriteria;
use App\Models\UserAccount;
use App\Services\LMS\AssessmentSummaryService\AssessmentSummaryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentAssessmentExamController extends Controller
{
    private $summaryService;

    public function __construct(AssessmentSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }
    
    public function studentAssessmentExam($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        return view('features.lms.student.assessment.student-assessment-test', compact('role', 'schoolName', 'schoolId', 'curriculumId', 
            'mapelId', 'assessmentTypeId', 'semester', 'assessmentId'));
    }

    public function studentAssessmentExamForm($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId) 
    {
        $user = UserAccount::with('StudentProfile')->find(Auth::id());

        $assessment = SchoolAssessment::with(['SchoolAssessmentType', 'SchoolClass'])->where('id', $assessmentId)
            ->whereHas('SchoolClass.StudentSchoolClass', function ($query) use ($user) {
                $query->where('student_id', $user->id)->where('student_class_status', 'active');
            })->where('assessment_type_id', $assessmentTypeId)->where('semester', $semester)->first();

        if (!$assessment) {
            return response()->json(['data' => null]);
        }

        // AMBIL ASSESSMENT
        $schoolAssessment = SchoolAssessment::where('id', $assessmentId)->first();

        // REMEDIAL LOGIC (AMBIL CATEGORY & PARENT)
        $assessmentCategory = strtolower($schoolAssessment->assessment_category ?? '');
        $parentAssessmentId = $schoolAssessment->parent_assessment_id;

        $sourceAssessmentId = $assessmentId;

        if (in_array($assessmentCategory, ['remedial', 'susulan']) && $parentAssessmentId) {
            $sourceAssessmentId = $parentAssessmentId;
        }

        $wrongBankIds = [];

        if ($assessmentCategory === 'remedial' && $parentAssessmentId) {

            $previousAssessment = null;

            // ambil semua assessment dalam 1 chain + yang SUDAH DIKERJAKAN USER
            $relatedAssessments = SchoolAssessment::where(function ($q) use ($parentAssessmentId) {
                $q->where('id', $parentAssessmentId)->orWhere('parent_assessment_id', $parentAssessmentId);
            })->whereHas('StudentAssessmentAnswer', function ($q) use ($user) {
                $q->where('student_id', $user->id);
            })->orderBy('start_date')->pluck('id')->toArray();

            // cari previous
            $currentIndex = array_search($assessmentId, $relatedAssessments);

            if ($currentIndex !== false && $currentIndex > 0) {
                $prevAssessmentId = $relatedAssessments[$currentIndex - 1];
                $previousAssessment = SchoolAssessment::find($prevAssessmentId);
            }

            // CACHE KEY
            $cacheKeyWrong = "assessment-remedial-wrong-{$user->id}-prev-" . ($previousAssessment->id ?? 'none');

            if (Cache::has($cacheKeyWrong)) {

                $wrongBankIds = Cache::get($cacheKeyWrong);

            } else {

                if ($previousAssessment) {

                    $previousAnswers = StudentAssessmentAnswer::with('SchoolAssessmentQuestion')->where('student_id', $user->id)->where('school_assessment_id', $previousAssessment->id)->get();

                    foreach ($previousAnswers as $ans) {

                        if ($ans->status_answer === 'submitted' && $ans->question_score !== null && $ans->question_score <= 0) {
                            if ($ans->SchoolAssessmentQuestion) {
                                $wrongBankIds[] = $ans->SchoolAssessmentQuestion->question_bank_id;
                            }
                        }
                    }
                }

                $wrongBankIds = array_values(array_unique($wrongBankIds));

                Cache::put($cacheKeyWrong, $wrongBankIds, now()->addHours(3));
            }
        }

        $publishedQuestionIds = SchoolAssessmentQuestion::where('school_assessment_id', $sourceAssessmentId)->orderBy('id')->pluck('id')->implode(',');

        $shuffleQuestions = $schoolAssessment->shuffle_questions;
        $shuffleOptions = $schoolAssessment->shuffle_options;

        $cacheKey = "assessment-{$user->id}-{$sourceAssessmentId}-{$publishedQuestionIds}-{$semester}-{$shuffleQuestions}-test";

        if (Cache::has($cacheKey)) {

            $cachedIds = Cache::get($cacheKey);

            $questions = SchoolAssessmentQuestion::with(['LmsQuestionBank', 'LmsQuestionBank.LmsQuestionOption', 'LmsQuestionBank.Mapel'])->whereIn('id', $cachedIds)
            ->when(!empty($wrongBankIds), function ($q) use ($wrongBankIds) {
            $q->whereIn('question_bank_id', $wrongBankIds);
        })->get()
            ->sortBy(function ($q) use ($cachedIds) {
                return array_search($q->id, $cachedIds);
            })
            ->values();

        } else {

            $baseQuery = SchoolAssessmentQuestion::with(['LmsQuestionBank', 'LmsQuestionBank.LmsQuestionOption', 'LmsQuestionBank.Mapel'
            ])->where('school_assessment_id', $sourceAssessmentId)->whereHas('LmsQuestionBank', function ($q) {
                $q->where('status_bank_soal', 'Publish');
            });

            if (!empty($wrongBankIds)) {
                $baseQuery->whereIn('question_bank_id', $wrongBankIds);
            }

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

            $publishedOptionIds = $options->sortBy('id')->pluck('id')->implode(',');

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

                $publishedRightIds = $right->sortBy('id')->pluck('id')->implode(',');

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

            if ($type === 'PG_KOMPLEKS') {

                // AMBIL ITEMS (ROW)
                $items = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side']) 
                        && $opt->extra_data['side'] === 'item';
                })->values();

                $publishedItemIds = $items->sortBy('id')->pluck('id')->implode(',');

                $itemCacheKey = "assessment-pgk-item-{$user->id}-{$assessmentId}-{$question->id}-{$publishedItemIds}-{$semester}-{$shuffleOptions}";

                if (Cache::has($itemCacheKey)) {

                    $cachedIds = Cache::get($itemCacheKey);

                    $items = $items->whereIn('id', $cachedIds)->sortBy(function ($opt) use ($cachedIds) {
                        return array_search($opt->id, $cachedIds);
                    })->values();

                } else {

                    if ($shuffleOptions) {
                        $items = $items->shuffle()->values();
                    } else {
                        $items = $items->values();
                    }

                    Cache::put($itemCacheKey, $items->pluck('id')->toArray(), now()->addHours(3));
                }

                // AMBIL CATEGORY (COLUMN)
                $right = $options->filter(function ($opt) {
                    return isset($opt->extra_data['side']) 
                        && $opt->extra_data['side'] === 'category';
                })->values();

                $publishedRightIds = $right->sortBy('id')->pluck('id')->implode(',');

                $matchingCacheKey = "assessment-pgk-category-{$user->id}-{$assessmentId}-{$question->id}-{$publishedRightIds}-{$semester}-{$shuffleOptions}";

                if (Cache::has($matchingCacheKey)) {

                    $cachedIds = Cache::get($matchingCacheKey);

                    $right = $right->whereIn('id', $cachedIds)->sortBy(function ($opt) use ($cachedIds) {
                        return array_search($opt->id, $cachedIds);
                    })->values();
                } else {

                    if ($shuffleOptions) {
                        $right = $right->shuffle()->values();
                    } else {
                        $right = $right->values();
                    }

                    Cache::put($matchingCacheKey, $right->pluck('id')->toArray(), now()->addHours(3));
                }

                // GABUNGKAN ITEMS + CATEGORY
                $shuffled = collect();

                foreach ($items as $item) {
                    $shuffled->push($item);
                }

                foreach ($right as $cat) {
                    $shuffled->push($cat);
                }

                $question->LmsQuestionBank->setRelation(
                    'LmsQuestionOption',
                    $shuffled
                );

                $shuffled = collect();

                foreach ($items as $l) {
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

                    if ($type === 'PG_KOMPLEKS') {

                        if (is_string($studentAnswer)) {
                            $studentAnswer = json_decode($studentAnswer, true);
                        }

                        if (!is_array($studentAnswer)) {
                            $isCorrect = false;
                        } else {

                            $correctPairs = $question->LmsQuestionBank->LmsQuestionOption
                                ->filter(function ($opt) {
                                    return isset($opt->extra_data['side']) 
                                        && $opt->extra_data['side'] === 'item';
                                })
                                ->mapWithKeys(function ($opt) {
                                    return [
                                        trim($opt->options_key) =>
                                        trim($opt->extra_data['answer'] ?? '')
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

            $assessment = SchoolAssessment::findOrFail($assessmentId);

            $this->summaryService->updateStudentAssessmentSummary(Auth::id(), $assessment);

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
        $schoolQuestion = SchoolAssessmentQuestion::with('lmsQuestionBank')->findOrFail($request->school_assessment_question_id);

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

        $schoolAssessment = $assessment;

        $assessmentCategory = strtolower($schoolAssessment->assessment_category ?? '');
        $isRemedial = $assessmentCategory === 'remedial';

        if ($isRemedial && $assessment->parent_assessment_id) {

            $previousAssessment = null;

            // ambil chain yang SUDAH DIKERJAKAN
            $relatedAssessments = SchoolAssessment::where(function ($q) use ($assessment) {
                $q->where('id', $assessment->parent_assessment_id)->orWhere('parent_assessment_id', $assessment->parent_assessment_id);
            })->whereHas('StudentAssessmentAnswer', function ($q) use ($userId) {
                $q->where('student_id', $userId);
            })->orderBy('start_date')->pluck('id')->toArray();

            $currentIndex = array_search($assessmentId, $relatedAssessments);

            if ($currentIndex !== false && $currentIndex > 0) {
                $prevAssessmentId = $relatedAssessments[$currentIndex - 1];
                $previousAssessment = SchoolAssessment::find($prevAssessmentId);
            }

            // cache key harus sama dengan form
            $cacheKeyWrong = "assessment-remedial-wrong-{$userId}-prev-" . ($previousAssessment->id ?? 'none');

            $wrongBankIds = Cache::get($cacheKeyWrong, []);

            // fallback kalau cache kosong
            if (empty($wrongBankIds)) {
                $totalQuestions = SchoolAssessmentQuestion::where('school_assessment_id', $assessment->parent_assessment_id)->count();
            } else {
                $totalQuestions = count($wrongBankIds);
            }

        } else {

            $totalQuestions = SchoolAssessmentQuestion::where('school_assessment_id', $assessmentId)->count();
        }

        $scorePerQuestion = $totalQuestions > 0 ? (100 / $totalQuestions) : 0;

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
                        $score = $isRemedial ? $scorePerQuestion : $schoolQuestion->question_weight;
                    }

                break;

                // MCMA
                case 'MCMA':

                    if (is_array($answerData)) {

                        $correctOptions = $question->lmsQuestionOption()->where('is_correct', 1)->pluck('options_key')->toArray();

                        sort($correctOptions);
                        sort($answerData);

                        if ($correctOptions === $answerData) {
                            $score = $isRemedial ? $scorePerQuestion : $schoolQuestion->question_weight;
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
                            $score = $isRemedial ? $scorePerQuestion : $schoolQuestion->question_weight;
                        }

                    }

                break;

                case 'PG_KOMPLEKS':

                    $correctAnswers = $question->lmsQuestionOption()->get()->filter(function ($opt) {
                        return isset($opt->extra_data['side']) && $opt->extra_data['side'] === 'item';
                    })
                        ->mapWithKeys(function ($opt) {
                            return [
                                $opt->options_key => $opt->extra_data['answer']
                            ];
                        })->toArray();

                    ksort($correctAnswers);
                    ksort($answerData);

                    if ($correctAnswers === $answerData) {
                        $score = $isRemedial ? $scorePerQuestion : $schoolQuestion->question_weight;
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

        if ($request->status_answer === 'submitted') {

            $totalSubmitted = StudentAssessmentAnswer::where('student_id', $userId)
                ->where('school_assessment_id', $assessmentId)
                ->where('status_answer', 'submitted')
                ->count();

            if ($totalSubmitted >= $totalQuestions) {

                if ($request->filled('total_exam_duration')) {
                    StudentAssessmentAnswer::where('student_id', $userId)
                        ->where('school_assessment_id', $assessmentId)
                        ->update([
                            'total_exam_duration' => $request->total_exam_duration
                        ]);
                }

                $this->summaryService->updateStudentAssessmentSummary($userId, $assessment);
            }
        }

        $attempt = StudentAssessmentAttempt::where('student_id', Auth::id())->where('school_assessment_id', $assessmentId)->first();

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

    public function studentProjectSubmission(Request $request, $role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId)
    {
        $userId = Auth::id();
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

        $this->summaryService->updateStudentAssessmentSummary($userId, $assessment);

        return response()->json([
            'status' => 'success',
            'message' => 'Asesmen berhasil disubmit.',
        ]);
    }

    public function studentResultAssessment($role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId) 
    {
        $user = UserAccount::with(['StudentProfile','StudentSchoolClass'])->findOrFail(Auth::id());
        $classId = $user->StudentSchoolClass[0]->school_class_id;

        // GET ASSESSMENT
        $schoolAssessment = SchoolAssessment::with('SchoolClass')->find($assessmentId);

        // ROOT (untuk summary)
        $rootAssessmentId = $schoolAssessment->parent_assessment_id ?? $schoolAssessment->id;

        // SUMMARY
        $summary = StudentAssessmentSummary::where('student_id', $user->id)->where('root_assessment_id', $rootAssessmentId)->first();

        // DATA JAWABAN USER
        $answers = StudentAssessmentAnswer::where('student_id',$user->id)->where('school_assessment_id', $assessmentId)->get();

        $totalQuestionsExam = $answers->count();
        $totalCorrect = $answers->where('question_score','>',0)->count();
        $totalWrong = $answers->where('question_score',0)->where('status_answer','submitted')->count();
        $totalUnanswered = $answers->where('status_answer','draft')->count();
        $totalPendingEssay = $answers->where('status_answer','submitted')->where('grading_status','pending')->count();

        // SCORE
        $rawScore = $answers->whereNotNull('question_score')->sum('question_score');

        // FINAL SCORE
        $category = strtolower($schoolAssessment->assessment_category ?? 'main');

        if ($summary) {

            if ($category === 'main') {
                $finalScore = $summary->main_score ?? $rawScore;
            }

            elseif ($category === 'susulan') {
                $finalScore = $summary->susulan_score ?? $rawScore;
            }

            elseif ($category === 'remedial') {

                // kalau kamu pakai JSON remedial_score
                $remedials = $summary->remedial_score ?? [];

                $currentRemedial = collect($remedials)
                    ->firstWhere('assessment_id', $assessmentId);

                $finalScore = $currentRemedial['score'] ?? $rawScore;
            }

            elseif ($category === 'pengayaan') {
                $finalScore = $summary->pengayaan_score ?? $rawScore;
            }

            else {
                $finalScore = $rawScore;
            }

        } else {
            $finalScore = $rawScore;
        }

        $finalScore = round($finalScore, 2);

        // final global score (lulus / tidak)
        $finalScoreGlobal = null;

        if ($summary) {
            if ($category === 'main') {
                $finalScoreGlobal = $summary->main_score ?? $finalScore;
            }

            elseif ($category === 'susulan') {
                $finalScoreGlobal = $summary->susulan_score ?? $finalScore;
            }

            elseif ($category === 'remedial') {
                $finalScoreGlobal = $finalScore;
            }

            elseif ($category === 'pengayaan') {
                $finalScoreGlobal = $summary->pengayaan_score ?? $finalScore;
            }

            else {
                $finalScoreGlobal = $finalScore;
            }
        }

        // fallback kalau semua null
        $finalScoreGlobal = $finalScoreGlobal ?? $finalScore;

        $maxScore = 100;
        $percentage = $maxScore > 0 ? round(($finalScore / $maxScore) * 100, 2) : 0;

        // DURASI USER
        $durations = $answers->where('answer_duration','>',0)->pluck('answer_duration');
        $fastestRaw = $durations->min() ?? 0;
        $slowestRaw = $durations->max() ?? 0;
        $totalDurationRaw = $answers->where('answer_duration','>',0)->pluck('total_exam_duration')->first() ?? 0;

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

        $fastest = $formatDuration($fastestRaw);
        $slowest = $formatDuration($slowestRaw);
        $totalDuration = $formatDuration($totalDurationRaw);

        // CONFIDENCE SCORE
        $answered = $totalCorrect + $totalWrong;
        $accuracy = $answered > 0 ? $totalCorrect / $answered : 0;
        $completion = $totalQuestionsExam > 0 ? $answered / $totalQuestionsExam : 0;
        $confidence = round(($accuracy * 0.8 + $completion * 0.2) * 100, 2);

        // TOTAL SISWA
        $totalStudents = StudentSchoolClass::where('school_class_id',$classId)->count();

        $participants = StudentAssessmentAnswer::where('school_assessment_id', $assessmentId)
        ->whereIn('student_id', function ($q) use ($classId) {
            $q->select('student_id')->from('student_school_classes')->where('school_class_id', $classId);
        })->distinct('student_id')->count('student_id');

        // RANKING DURASI
        $allDurations = StudentAssessmentAnswer::where('school_assessment_id',$assessmentId)->where('answer_duration','>', 0)->selectRaw('student_id, SUM(answer_duration) as total_duration')
        ->groupBy('student_id')->orderBy('total_duration')->get();

        $uniqueTotalDurations = $allDurations->pluck('total_duration')->unique()->sort()->values();
        $userTotalDuration = $allDurations->firstWhere('student_id',$user->id)->total_duration ?? null;
        $rankDuration = $userTotalDuration !== null ? $uniqueTotalDurations->search($userTotalDuration) + 1 : null;

        $percentileDuration = ($rankDuration !== null) ? ($participants == 1 ? 100 : round((($participants - $rankDuration) / ($participants - 1)) * 100, 2)) : 0;

        // FASTEST
        $fastestQuestion = StudentAssessmentAnswer::where('school_assessment_id',$assessmentId)->where('answer_duration','>', 0)->selectRaw('student_id, MIN(answer_duration) as duration')
        ->groupBy('student_id')->orderBy('duration')->get();

        $uniqueFastestDurations = $fastestQuestion->pluck('duration')->unique()->sort()->values();
        $userFastestDuration = $fastestQuestion->firstWhere('student_id',$user->id)->duration ?? null;
        $rankFastest = $userFastestDuration !== null ? $uniqueFastestDurations->search($userFastestDuration) + 1 : null;

        $percentileFastest = ($rankFastest !== null) ? ($participants == 1 ? 100 : round((($participants - $rankFastest) / ($participants - 1)) * 100, 2)) : 0;

        // SLOWEST
        $slowestQuestion = StudentAssessmentAnswer::where('school_assessment_id',$assessmentId)->where('answer_duration','>',0)->selectRaw('student_id, MAX(answer_duration) as duration')
        ->groupBy('student_id')->orderBy('duration')->get();

        $uniqueSlowestDuration = $slowestQuestion->pluck('duration')->unique()->sortDesc()->values();
        $userSlowestDuration = $slowestQuestion->firstWhere('student_id',$user->id)->duration ?? null;
        $rankSlowest = $userSlowestDuration !== null ? $uniqueSlowestDuration->search($userSlowestDuration) + 1 : null;

        $percentileSlowest = ($rankSlowest !== null) ? ($participants == 1 ? 100 : round((($participants - $rankSlowest) / ($participants - 1)) * 100, 2)) : 0;

        // CONFIDENCE RANK
        $totalQuestions = StudentAssessmentAnswer::where('school_assessment_id',$assessmentId)->distinct('school_assessment_question_id')->count('school_assessment_question_id');

        $allConfidence = StudentAssessmentAnswer::where('school_assessment_id', $assessmentId)->selectRaw('student_id,
            SUM(CASE WHEN question_score > 0 THEN 1 ELSE 0 END) as correct,
            SUM(CASE WHEN status_answer = "submitted" THEN 1 ELSE 0 END) as answered,
            SUM(answer_duration) as total_duration')
        ->groupBy('student_id')->get();

        $durations = $allConfidence->pluck('total_duration');
        $minDuration = $durations->min();
        $maxDuration = $durations->max();

        $allConfidence = $allConfidence->map(function($row) use ($totalQuestions,$minDuration,$maxDuration){

            $duration = $row->total_duration ?? 0;
            $accuracy = $row->answered > 0 ? $row->correct / $row->answered : 0;
            $completion = $totalQuestions > 0 ? min($row->answered / $totalQuestions, 1) : 0;

            $speed = ($maxDuration == $minDuration) ? 1 : ($maxDuration - $duration) / ($maxDuration - $minDuration);
            $speed = max(min($speed,1),0);

            $confidence = min(($accuracy * 0.7 + $completion * 0.2 + $speed * 0.1) * 100, 100);

            return [
                'student_id'=> $row->student_id,
                'confidence'=> round($confidence,2)
            ];
        })->sortByDesc('confidence')->values();

        $uniqueConfidence = $allConfidence->pluck('confidence')->unique()->sortDesc()->values();
        $userConfidenceRow = $allConfidence->firstWhere('student_id',$user->id);
        $confidence = $userConfidenceRow['confidence'] ?? null;

        $rankConfidence = $confidence !== null ? $uniqueConfidence->search($confidence) + 1 : null;

        $percentileConfidence = ($rankConfidence !== null) ? ($participants == 1 ? 100 : round((($participants - $rankConfidence) / ($participants - 1)) * 100, 2)) : 0;

        $isFullyGraded = $totalPendingEssay === 0;

        // KKM
        $kelasId = $schoolAssessment->SchoolClass?->kelas_id;
        $schoolYear = $schoolAssessment->SchoolClass?->tahun_ajaran;

        $kkm = SubjectPassingGradeCriteria::where('mapel_id', $schoolAssessment->mapel_id)->where('kelas_id', $kelasId)->where('school_year', $schoolYear)->latest()->value('kkm_value') ?? 75;

        $hasAttempt = $answers->count() > 0;

        return view('features.lms.student.assessment.student-assessment-result-test', compact('role', 'schoolName', 'schoolId', 'curriculumId', 'mapelId', 'assessmentTypeId', 'semester',
            'assessmentId', 'user', 'totalQuestions', 'totalCorrect', 'totalWrong', 'totalUnanswered', 'totalPendingEssay', 'finalScore', 'percentage', 'isFullyGraded', 'schoolAssessment',
            'fastest', 'slowest', 'totalDuration', 'confidence', 'percentileDuration', 'percentileFastest', 'percentileSlowest', 'percentileConfidence', 'participants','totalStudents','kkm',
            'hasAttempt', 'finalScoreGlobal', 'category'
        ));
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
