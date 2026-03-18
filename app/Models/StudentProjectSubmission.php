<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProjectSubmission extends Model
{
    protected $fillable = [
        'student_id',
        'school_assessment_id',
        'submission_type',
        'file_path',
        'original_filename',
        'text_answer',
        'score',
        'teacher_feedback',
        'grading_status',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'student_id');
    }

    public function SchoolAssessment()
    {
        return $this->belongsTo(SchoolAssessment::class, 'school_assessment_id');
    }
}