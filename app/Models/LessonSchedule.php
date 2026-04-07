<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_partner_id',
        'class_name',
        'day_of_week',
        'start_time',
        'end_time',
        'teacher_id',
        'teacher_name',
        'subject_name',
        'color',
        'status',
    ];
}