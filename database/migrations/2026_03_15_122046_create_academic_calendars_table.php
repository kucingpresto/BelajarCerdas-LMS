<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_calendars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_partner_id'); 
            $table->date('date'); 
            $table->string('title'); 
            $table->string('type'); 
            $table->string('display')->default('outline'); 
            $table->string('color'); 
            $table->enum('status', ['draft', 'published'])->default('draft'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_calendars');
    }
};