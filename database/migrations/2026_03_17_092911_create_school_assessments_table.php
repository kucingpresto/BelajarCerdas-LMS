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
        Schema::create('school_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts');
            $table->foreignId('school_partner_id')->constrained('school_partners');
            $table->foreignId('school_class_id')->constrained('school_classes');
            $table->foreignId('mapel_id')->constrained('mapels');
            $table->foreignId('assessment_type_id')->constrained('school_assessment_types');
            $table->string('title');
            $table->text('assessment_instruction')->nullable();
            $table->integer('duration')->nullable();
            $table->unsignedTinyInteger('semester');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('assessment_value_file')->nullable();
            $table->string('assessment_original_filename')->nullable();
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->boolean('show_score')->default(false);
            $table->boolean('show_answer')->default(false);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_assessments');
    }
};