<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserAccount extends Authenticatable
{
    use HasFactory;
    protected $fillable = [
        'email',
        'password',
        'no_hp',
        'role',
        'status_akun',
    ];

    // PROFILE USERS
    public function OfficeProfile() {
        return $this->hasOne(OfficeProfile::class, 'user_id');
    }

    public function SchoolStaffProfile() {
        return $this->hasOne(SchoolStaffProfile::class, 'user_id');
    }

    public function StudentProfile() {
        return $this->hasOne(StudentProfile::class, 'user_id');
    }

    // SCHOOL PARTNER
    public function SchoolPartner() {
        return $this->hasOne(SchoolPartner::class, 'kepsek_id');
    }

    // TRANSACTION
    public function Transaction() {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    // SYLLABUS
    public function Kurikulum() {
        return $this->hasMany(Kurikulum::class, 'user_id');
    }

    public function Fase() {
        return $this->hasMany(Fase::class, 'user_id');
    }

    public function Kelas() {
        return $this->hasMany(Kelas::class, 'user_id');
    }

    public function Mapel() {
        return $this->hasMany(Mapel::class, 'user_id');
    }

    public function Bab() {
        return $this->hasMany(Bab::class, 'user_id');
    }

    // LMS FEATURE
    public function SchoolClass() {
        return $this->hasOne(SchoolClass::class, 'wali_kelas_id');
    }

    public function StudentSchoolClass() {
        return $this->hasMany(StudentSchoolClass::class, 'student_id');
    }

    public function LmsQuestionBank() {
        return $this->hasMany(LmsQuestionBank::class, 'user_id');
    }

    public function LmsContent()
    {
        return $this->hasMany(LmsContent::class, 'user_id');
    }

    public function SchoolAssessmentType() {
        return $this->hasMany(SchoolAssessmentType::class, 'user_id');
    }

    public function TeacherMapel()
    {
        return $this->hasMany(TeacherMapel::class, 'user_id');
    }

    public function LmsMeetingContent()
    {
        return $this->hasMany(LmsMeetingContent::class, 'teacher_id');
    }

    public function SchoolAssessment()
    {
        return $this->hasMany(SchoolAssessment::class, 'user_id');
    }

    public function StudentAssessmentAnswer()
    {
        return $this->hasMany(StudentAssessmentAnswer::class, 'student_id');
    }

    public function StudentProjectSubmission()
    {
        return $this->hasMany(StudentProjectSubmission::class, 'student_id');
    }
}
