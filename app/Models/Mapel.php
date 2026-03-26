<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mata_pelajaran',
        'kode',
        'kelas_id',
        'fase_id',
        'kurikulum_id',
        'school_partner_id',
        'status_mata_pelajaran',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function SubBab()
    {
        return $this->hasMany(SubBab::class, 'mapel_id');
    }

    public function Bab()
    {
        return $this->hasMany(Bab::class, 'mapel_id');
    }

    public function Kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function Fase()
    {
        return $this->belongsTo(Fase::class, 'fase_id');
    }

    public function Kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }
    
    // LMS QUESTION BANK
    public function LmsQuestionBank()
    {
        return $this->hasMany(LmsQuestionBank::class, 'mapel_id');
    }

    // LMS SCHOOL MAPEL
    public function SchoolMapel()
    {
        return $this->hasMany(SchoolMapel::class, 'mapel_id');
    }

    public function LmsContent()
    {
        return $this->hasMany(LmsContent::class, 'mapel_id');
    }

    public function TeacherMapel()
    {
        return $this->hasMany(TeacherMapel::class, 'mapel_id');
    }

    public function LmsMeetingContent()
    {
        return $this->hasMany(LmsMeetingContent::class, 'mapel_id');
    }

    public function SchoolAssessment()
    {
        return $this->hasMany(SchoolAssessment::class, 'mapel_id');
    }

    // SUBJECT PASSING GRADE CRITERIA
    public function SubjectPassingGradeCriteria()
    {
        return $this->hasMany(SubjectPassingGradeCriteria::class, 'mapel_id');
    }
}
