<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    private function authorizeTeacher(Course $course): void
    {
        abort_if(auth()->id() !== $course->teacher_id, 403, 'Anda tidak berhak mengelola tugas pada mata pelajaran ini.');
        
        // Verifikasi tahun akademik aktif
        if ($course->academicYear && $course->academicYear->status !== 'active') {
            abort(403, 'Tahun akademik ini telah diarsipkan dan bersifat read-only.');
        }
    }

    public function index(Course $course)
    {
        $this->authorizeTeacher($course);

        $assignments = Assignment::where('course_id', $course->id)
            ->withCount('submissions')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Teacher/Assignments/Index', [
            'course' => $course->load(['subject', 'schoolClass']),
            'assignments' => $assignments,
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeTeacher($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_at' => 'nullable|date',
            'allowed_file_types' => 'nullable|string',
            'max_file_size_mb' => 'nullable|integer|min:1|max:50',
            'status' => 'required|in:draft,published,closed',
        ]);

        Assignment::create([
            'course_id' => $course->id,
            'teacher_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'allowed_file_types' => $validated['allowed_file_types'] ?? 'pdf,doc,docx,zip,rar,jpg,png',
            'max_file_size_mb' => $validated['max_file_size_mb'] ?? 10,
            'status' => $validated['status'],
        ]);

        return redirect()->back()->with('success', 'Tugas berhasil dibuat.');
    }

    public function update(Request $request, Course $course, Assignment $assignment)
    {
        $this->authorizeTeacher($course);
        abort_if($assignment->course_id !== $course->id, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_at' => 'nullable|date',
            'allowed_file_types' => 'nullable|string',
            'max_file_size_mb' => 'nullable|integer|min:1|max:50',
            'status' => 'required|in:draft,published,closed',
        ]);

        $assignment->update($validated);

        return redirect()->back()->with('success', 'Tugas berhasil diperbarui.');
    }

    public function submissions(Course $course, Assignment $assignment)
    {
        $this->authorizeTeacher($course);
        abort_if($assignment->course_id !== $course->id, 404);

        $submissions = $assignment->submissions()
            ->with('student:id,name,email')
            ->orderBy('submitted_at', 'desc')
            ->get();

        return Inertia::render('Teacher/Assignments/Submissions', [
            'course' => $course->load(['subject', 'schoolClass']),
            'assignment' => $assignment,
            'submissions' => $submissions,
        ]);
    }

    public function destroy(Course $course, Assignment $assignment)
    {
        $this->authorizeTeacher($course);
        abort_if($assignment->course_id !== $course->id, 404);

        // Hapus file attachment dari semua submissions terkait
        foreach ($assignment->submissions as $submission) {
            if ($submission->attachment_path && Storage::disk('local')->exists($submission->attachment_path)) {
                Storage::disk('local')->delete($submission->attachment_path);
            }
        }

        $assignment->delete();

        return redirect()->back()->with('success', 'Tugas berhasil dihapus.');
    }
}