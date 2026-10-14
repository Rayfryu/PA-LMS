<?php

namespace App\Http\Controllers;

use App\Models\LearningMaterial;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function downloadMaterial(LearningMaterial $material)
    {
        $isOwner = auth()->id() === $material->teacher_id;
        $isEnrolledStudent = auth()->user()->hasRole('student') && $material->status === 'published'; 
        
        abort_if(!$isOwner && !$isEnrolledStudent, 403, 'Anda tidak memiliki akses ke berkas ini.');
        abort_if(!$material->attachment_path || !Storage::disk('local')->exists($material->attachment_path), 404, 'File tidak ditemukan.');

        return Storage::disk('local')->download($material->attachment_path);
    }

    public function downloadSubmission(AssignmentSubmission $submission)
    {
        $user = auth()->user();
        $assignment = $submission->assignment;
        $course = $assignment->course;

        // Guru pemampu course atau Siswa pemilik submission
        $isTeacherOwner = $user->id === $course->teacher_id;
        $isStudentOwner = $user->id === $submission->student_id;

        abort_if(!$isTeacherOwner && !$isStudentOwner, 403, 'Anda tidak berhak mengunduh lampiran tugas ini.');
        abort_if(!$submission->attachment_path || !Storage::disk('local')->exists($submission->attachment_path), 404, 'File lampiran tidak ditemukan.');

        return Storage::disk('local')->download($submission->attachment_path);
    }
}