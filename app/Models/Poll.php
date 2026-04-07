<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;
    
    // Tambahkan baris ini agar Laravel mengizinkan data disimpan
    protected $guarded = ['id']; 
}