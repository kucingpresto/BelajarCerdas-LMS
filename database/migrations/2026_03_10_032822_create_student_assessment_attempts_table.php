<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('user_accounts');
            $table->foreignId('school_assessment_id')->constrained('school_assessments');
            $table->timestamp('start_time');
            $table->timestamp('expire_time');
            $table->integer('tab_switch_count')->default(0);
            $table->enum('status', ['in_progress', 'submitted', 'cheating', 'timeout'])->default('in_progress');
            $table->timestamps();

            $table->unique(['student_id', 'school_assessment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_assessment_attempts');
    }
};