<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('polls')->cascadeOnDelete();
            $table->foreignId('poll_option_id')->constrained('poll_options')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id'); // ID User Siswa
            $table->timestamps();
            
            // Mencegah 1 siswa vote 2 kali di polling yang sama
            $table->unique(['poll_id', 'student_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
    }
};
