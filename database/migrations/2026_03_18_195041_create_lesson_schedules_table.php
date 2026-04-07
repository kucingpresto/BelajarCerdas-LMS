<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_partner_id'); // ID Sekolah
            $table->string('class_name'); // Contoh: "Kelas X-A"
            $table->string('day_of_week'); // Contoh: "Senin", "Selasa"
            $table->time('start_time'); // Jam mulai, cth: 07:00:00
            $table->time('end_time'); // Jam selesai, cth: 07:45:00
            
            // Kunci utama untuk fitur Anti-Bentrok:
            $table->unsignedBigInteger('teacher_id'); 
            
            $table->string('teacher_name'); // Nama guru (disimpan agar loading UI lebih cepat)
            $table->string('subject_name'); // Nama Mata Pelajaran
            $table->string('color')->default('#0071BC'); // Warna kotak jadwal (untuk UI)
            
            $table->enum('status', ['draft', 'published'])->default('draft'); // Status tayang
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_schedules');
    }
};