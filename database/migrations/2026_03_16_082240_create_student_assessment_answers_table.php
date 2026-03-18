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
        Schema::create('student_assessment_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('user_accounts');
            $table->foreignId('school_assessment_id')->constrained('school_assessments');
            $table->foreignId('school_assessment_question_id')->constrained('school_assessment_questions');
            $table->json('answer_value')->nullable();
            $table->integer('question_score')->nullable();
            $table->enum('status_answer', ['draft', 'submitted']);
            $table->enum('grading_status', ['pending', 'graded'])->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->integer('answer_duration')->nullable();
            $table->integer('total_exam_duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_assessment_answers');
    }
};