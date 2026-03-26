<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectPassingGradeCriteria extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_partner_id',
        'kelas_id',
        'mapel_id',
        'school_year',
        'kkm_value',
    ];

    public function UserAccount()
    {
        return $this->belongsTo(UserAccount::class, 'user_id');
    }

    public function SchoolPartner()
    {
        return $this->belongsTo(SchoolPartner::class, 'school_partner_id');
    }

    public function Kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function Mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }
}