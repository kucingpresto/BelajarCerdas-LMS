<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolPartner extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_sekolah',
        'npsn',
        'kepsek_id',
        'jenjang_sekolah',
    ];

    public function Transactions()
    {
        return $this->hasMany(Transaction::class, 'school_partner_id');
    }

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'kepsek_id');
    }

    public function SchoolLmsSubscription()
    {
        return $this->hasOne(SchoolLmsSubscription::class, 'school_partner_id');
    }

    public function SchoolStaffProfile()
    {
        return $this->hasMany(SchoolStaffProfile::class, 'school_partner_id');
    }

    public function StudentProfile()
    {
        return $this->hasOne(StudentProfile::class, 'school_partner_id');
    }

    public function SchoolClass()
    {
        return $this->hasMany(SchoolClass::class, 'school_partner_id');
    }

    public function SchoolMajor()
    {
        return $this->hasMany(SchoolMajor::class, 'school_partner_id');
    }

    // SYLLABUS
    public function Mapel()
    {
        return $this->hasMany(Mapel::class, 'school_partner_id');
    }

    public function Bab()
    {
        return $this->hasMany(Bab::class, 'school_partner_id');
    }

    public function SubBab()
    {
        return $this->hasMany(SubBab::class, 'school_partner_id');
    }

    // LMS QUESTION BANK
    public function LmsQuestionBank()
    {
        return $this->hasMany(LmsQuestionBank::class, 'school_partner_id');
    }

    // LMS SCHOOL MAPEL
    public function SchoolMapel()
    {
        return $this->hasMany(SchoolMapel::class, 'school_partner_id');
    }

    public function LmsContent()
    {
        return $this->hasMany(Service::class, 'school_partner_id');
    }

    public function SchoolLmsContent()
    {
        return $this->hasMany(SchoolLmsContent::class, 'school_partner_id');
    }

    public function SchoolAssessmentType() {
        return $this->hasMany(SchoolAssessmentType::class, 'school_partner_id');
    }

    public function SchoolQuestionBank()
    {
        return $this->hasMany(SchoolQuestionBank::class, 'school_partner_id');
    }

    public function LmsMeetingContent()
    {
        return $this->hasMany(LmsMeetingContent::class, 'school_partner_id');
    }

    public function SchoolAssessment()
    {
        return $this->hasMany(SchoolAssessment::class, 'school_partner_id');
    }

    public function SchoolAssessmentTypeWeight()
    {
        return $this->hasMany(SchoolAssessmentTypeWeight::class, 'school_partner_id');
    }

    public function SubjectPassingGradeCriteria()
    {
        return $this->hasMany(SubjectPassingGradeCriteria::class, 'school_partner_id');
    }
}
