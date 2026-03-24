<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolAssessmentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_partner_id',
        'name',
        'assessment_mode_id',
        'is_remedial_allowed',
        'max_remedial_attempt',
        'is_active',
    ];

    public function UserAccount() {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function SchoolPartner() {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function AssessmentMode() {
        return $this->belongsTo(AssessmentMode::class, 'assessment_mode_id');
    }

    public function SchoolAssessment()
    {
        return $this->hasMany(SchoolAssessment::class, 'assessment_type_id');
    }

    public function SchoolAssessmentTypeWeight()
    {
        return $this->hasMany(SchoolAssessmentTypeWeight::class, 'assessment_type_id');
    }
}