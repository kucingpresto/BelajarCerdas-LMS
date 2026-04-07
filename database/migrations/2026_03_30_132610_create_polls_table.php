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
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_partner_id')->constrained()->cascadeOnDelete(); // Penting agar data tidak bercampur antar sekolah
            $table->unsignedBigInteger('teacher_id'); // ID Guru yang membuat
            $table->string('question'); // Pertanyaan polling
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polls');
    }
};
