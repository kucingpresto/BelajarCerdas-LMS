<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAssessmentAnswer extends Model
{
    use HasFactory;

    protected $casts = [
        'answer_value' => 'array',
    ];

    protected $fillable = [
        'student_id',
        'school_assessment_id',
        'school_assessment_question_id',
        'answer_value',
        'question_score',
        'status_answer',
        'grading_status',
        'teacher_feedback',
        'answer_duration',
        'total_exam_duration',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'student_id');
    }

    public function SchoolAssessment()
    {
        return $this->belongsTo(SchoolAssessment::class, 'school_assessment_id');
    }

    public function SchoolAssessmentQuestion()
    {
        return $this->belongsTo(SchoolAssessmentQuestion::class, 'school_assessment_question_id');
    }
}