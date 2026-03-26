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
        Schema::create('subject_passing_grade_criterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user_accounts');
            $table->foreignId('school_partner_id')->constrained('school_partners');
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->foreignId('mapel_id')->constrained('mapels');
            $table->string('school_year');
            $table->integer('kkm_value');
            $table->timestamps();

            $table->unique(['school_partner_id', 'kelas_id', 'mapel_id', 'school_year'], 'unique_subject_kkm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_passing_grade_criterias');
    }
};