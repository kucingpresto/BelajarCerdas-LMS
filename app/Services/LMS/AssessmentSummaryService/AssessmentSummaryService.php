<?php

namespace App\Services\LMS\AssessmentSummaryService;

use App\Models\SchoolAssessment;
use App\Models\StudentAssessmentAnswer;
use App\Models\StudentAssessmentSummary;
use App\Models\StudentProjectSubmission;
use App\Models\SubjectPassingGradeCriteria;

class AssessmentSummaryService
{
    // private function update student assessment summary
public function updateStudentAssessmentSummary($studentId, $assessment)
{
    // cari root assessment
    $rootAssessmentId = $assessment->parent_assessment_id ? $assessment->parent_assessment_id : $assessment->id;

    // ambil semua assessment turunan (main, remedial, susulan, dll)
    $assessments = SchoolAssessment::where(function ($q) use ($rootAssessmentId) {
        $q->where('id', $rootAssessmentId)->orWhere('parent_assessment_id', $rootAssessmentId);
    })->get();

    // ambil semua jawaban siswa dari semua attempt
    $answers = StudentAssessmentAnswer::where('student_id', $studentId)->whereIn('school_assessment_id', $assessments->pluck('id'))->get()->groupBy('school_assessment_id');

    $mainScore = null;
    $susulanScore = null;
    $remedialScores = [];
    $pengayaanScore = null;
    $lastRemedialAssessmentId = null;

    foreach ($assessments as $a) {

        $studentAnswers = $answers->get($a->id);

        // ambil project submission
        $project = StudentProjectSubmission::where('student_id', $studentId)->where('school_assessment_id', $a->id)->orderByDesc('id')->first();

        // skip kalau tidak ada answer DAN tidak ada project
        if ((!$studentAnswers || $studentAnswers->count() === 0) && !$project) {
            continue;
        }

        // default score dari answer
        $totalScore = round($studentAnswers?->sum('question_score') ?? 0, 2);

        // override kalau project
        if ($a->SchoolAssessmentType->AssessmentMode->code === 'project') {
            $totalScore = $project->score !== null ? round($project->score, 2) : 0;
        }

        switch ($a->assessment_category) {
            case 'main':
                $mainScore = $totalScore;
                break;

            case 'susulan':
                $susulanScore = $totalScore;
                break;

            case 'remedial':
                $remedialScores[] = [
                    'score' => $totalScore,
                    'assessment_id' => $a->id,
                ];
                break;

            case 'pengayaan':
                $pengayaanScore = $totalScore;
                break;
        }
    }

    // ambil remedial terakhir
    $lastRemedialScore = null;

    if (!empty($remedialScores)) {
        $currentAssessmentCategory = strtolower($assessment->assessment_category ?? '');

        if ($currentAssessmentCategory === 'remedial') {

            //  FIX: rounding juga di sini
            $currentScore = round(
                $answers->get($assessment->id)?->sum('question_score') ?? 0,
                2
            );

            $lastRemedialScore = $currentScore;
            $lastRemedialAssessmentId = $assessment->id;

        } else {

            $lastRemedial = collect($remedialScores)
                ->filter(fn($item) => $item['score'] > 0)
                ->sortByDesc('assessment_id')
                ->first();

            if ($lastRemedial) {
                $lastRemedialScore = $lastRemedial['score'];
                $lastRemedialAssessmentId = $lastRemedial['assessment_id'];
            }
        }
    }

    $kelasId = $assessment->SchoolClass?->kelas_id;

    $kkm = SubjectPassingGradeCriteria::where('mapel_id', $assessment->mapel_id)->where('kelas_id', $kelasId)->where('school_partner_id', $assessment->school_partner_id)->value('kkm_value');

    // tentukan final score
    $finalScore = null;
    $scoreSource = null;

    // kumpulkan semua score
    $allScores = collect([
        ['type' => 'main', 'score' => $mainScore],
        ['type' => 'susulan', 'score' => $susulanScore],
        ['type' => 'remedial', 'score' => $lastRemedialScore],
    ])->filter(fn($item) => $item['score'] !== null);

    // ambil yang tertinggi
    $best = $allScores->sortByDesc('score')->first();

    if ($best && is_numeric($best['score'])) {

        $scoreSource = $best['type'];

        $bestScore = round((float) $best['score'], 2);

        if ($best['type'] === 'remedial' && $kkm !== null && $bestScore >= $kkm) {
            $finalScore = round($kkm, 2);
        } else {
            $finalScore = $bestScore;
        }
    }

    // upsert summary
    StudentAssessmentSummary::updateOrCreate(
        [
            'student_id' => $studentId,
            'root_assessment_id' => $rootAssessmentId,
        ],
        [
            // semua score distandarisasi 2 decimal
            'main_score' => $mainScore !== null ? round($mainScore, 2) : null,
            'susulan_score' => $susulanScore !== null ? round($susulanScore, 2) : null,
            'last_remedial_score' => $lastRemedialScore !== null ? round($lastRemedialScore, 2) : null,
            'pengayaan_score' => $pengayaanScore !== null ? round($pengayaanScore, 2) : null,

            'final_score' => $finalScore !== null ? round($finalScore, 2) : null,
            'score_source' => $scoreSource,
            'remedial_count' => count($remedialScores),
            'last_remedial_assessment_id' => $lastRemedialAssessmentId,
        ]
    );
}
}