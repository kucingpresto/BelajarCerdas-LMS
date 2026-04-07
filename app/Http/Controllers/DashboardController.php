<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        if (\Illuminate\Support\Facades\Auth::user()->role === 'Siswa') {
            // Jika yang masuk adalah siswa, langsung lempar ke Dashboard Kalender & Jadwal
            return redirect()->route('lms.student.dashboard');
        }  
        else {
            return view('beranda');
        }  
        
    }
}
