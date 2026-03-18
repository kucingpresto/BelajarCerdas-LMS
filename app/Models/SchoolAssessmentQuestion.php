<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolAssessmentQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_assessment_id',
        'question_bank_id',
        'question_weight',
    ];

    public function SchoolAssessment()
    {
        return $this->belongsTo(SchoolAssessment::class, 'school_assessment_id');
    }

    public function LmsQuestionBank()
    {
        return $this->belongsTo(LmsQuestionBank::class, 'question_bank_id');
    }

    public function StudentAssessmentAnswer()
    {
        return $this->hasMany(StudentAssessmentAnswer::class, 'school_assessment_question_id');
    }
}
