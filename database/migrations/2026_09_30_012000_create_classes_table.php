<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('major_id')->constrained('majors')->restrictOnDelete();
            $table->string('grade_level'); // X | XI | XII — see App\Enums\GradeLevel
            $table->string('name'); // full descriptive name, e.g. "XI RPL 1" — NOT a suffix to concatenate with grade_level
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('capacity')->nullable();
            $table->timestamps();

            // BR-CLS-009: class name unique within (academic_year, major)
            $table->unique(['academic_year_id', 'major_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};