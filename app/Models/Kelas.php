<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kelas',
        'kode',
        'fase_id',
        'kurikulum_id',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function SubBab()
    {
        return $this->hasMany(SubBab::class, 'kelas_id');
    }

    public function Bab()
    {
        return $this->hasMany(Bab::class, 'kelas_id');
    }

    public function Mapel()
    {
        return $this->hasMany(Mapel::class, 'kelas_id');
    }

    public function Fase()
    {
        return $this->belongsTo(Fase::class, 'fase_id');
    }

    public function Kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function SchoolClass()
    {
        return $this->hasOne(SchoolClass::class, 'kelas_id');
    }

    // LMS QUESTION BANK
    public function LmsQuestionBank()
    {
        return $this->hasMany(LmsQuestionBank::class, 'kelas_id');
    }

    public function LmsContent()
    {
        return $this->hasMany(LmsContent::class, 'kelas_id');
    }

    // SUBJECT PASSING GRADE CRITERIA
    public function SubjectPassingGradeCriteria()
    {
        return $this->hasMany(SubjectPassingGradeCriteria::class, 'kelas_id');
    }
}
