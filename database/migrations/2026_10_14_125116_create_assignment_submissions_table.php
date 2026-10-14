<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->text('text_response')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamp('submitted_at');
            $table->enum('status', ['submitted', 'resubmitted'])->default('submitted');
            $table->timestamps();

            // Mencegah duplikasi submission record per siswa & tugas
            $table->unique(['assignment_id', 'student_id']);
            $table->index(['student_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};