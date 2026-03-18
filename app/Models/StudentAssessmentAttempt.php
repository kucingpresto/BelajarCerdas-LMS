<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAssessmentAttempt extends Model
{

    protected $casts = [
        'start_time' => 'datetime',
        'expire_time' => 'datetime',
    ];
    protected $fillable = [
        'student_id',
        'school_assessment_id',
        'start_time',
        'expire_time',
        'tab_switch_count',
        'status',
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