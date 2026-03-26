<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_partner_id',
        'school_class_id',
        'mapel_id',
        'assessment_type_id',
        'title',
        'assessment_instruction',
        'duration',
        'semester',
        'start_date',
        'end_date',
        'assessment_value_file',
        'assessment_original_filename',
        'shuffle_questions',
        'shuffle_options',
        'show_score',
        'show_answer',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function SchoolAssessmentQuestion()
    {
        return $this->hasMany(SchoolAssessmentQuestion::class, 'school_assessment_id');
    }

    public function StudentAssessmentAnswer()
    {
        return $this->hasMany(StudentAssessmentAnswer::class, 'school_assessment_id');
    }

    public function StudentProjectSubmission()
    {
        return $this->hasMany(StudentProjectSubmission::class, 'school_assessment_id');
    }

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function SchoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function Mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function SchoolAssessmentType()
    {
        return $this->belongsTo(SchoolAssessmentType::class, 'assessment_type_id');
    }
}