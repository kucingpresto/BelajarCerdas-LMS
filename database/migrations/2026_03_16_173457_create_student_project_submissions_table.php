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
        Schema::create('student_project_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('user_accounts');
            $table->foreignId('school_assessment_id')->constrained('school_assessments');
            $table->enum('submission_type', ['file','text']);
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->longText('text_answer')->nullable();
            $table->integer('score')->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->enum('grading_status',['pending','graded'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_project_submissions');
    }
};