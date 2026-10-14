<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmissionController extends Controller
{
    private function authorizeStudent(Course $course): void
    {
        $user = auth()->user();
        
        abort_if(!$user || !$user->hasRole('student'), 403, 'Akses khusus siswa.');

        // Verifikasi keterdaftaran siswa jika tabel enrollment ada di database
        if (Schema::hasTable('student_enrollments')) {
            $isEnrolled = DB::table('student_enrollments')
                ->where('student_id', $user->id)
                ->where(function ($q) use ($course) {
                    $q->where('school_class_id', $course->school_class_id)
                      ->orWhere('course_id', $course->id);
                })->exists();
            abort_if(!$isEnrolled, 403, 'Anda tidak terdaftar dalam kelas mata pelajaran ini.');
        } elseif (Schema::hasTable('enrollments')) {
            $isEnrolled = DB::table('enrollments')
                ->where('user_id', $user->id)
                ->where(function ($q) use ($course) {
                    $q->where('school_class_id', $course->school_class_id)
                      ->orWhere('course_id', $course->id);
                })->exists();
            abort_if(!$isEnrolled, 403, 'Anda tidak terdaftar dalam kelas mata pelajaran ini.');
        }

        if ($course->academicYear && $course->academicYear->status !== 'active') {
            abort(403, 'Tahun akademik ini telah diarsipkan.');
        }
    }

    public function index(Course $course)
    {
        $this->authorizeStudent($course);

        $assignments = Assignment::where('course_id', $course->id)
            ->whereIn('status', ['published', 'closed'])
            ->with(['studentSubmission' => function ($q) {
                $q->where('student_id', auth()->id());
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Student/Assignments/Index', [
            'course' => $course->load(['subject', 'schoolClass']),
            'assignments' => $assignments,
        ]);
    }

    public function show(Course $course, Assignment $assignment)
    {
        $this->authorizeStudent($course);
        abort_if($assignment->course_id !== $course->id, 404);
        abort_if($assignment->status === 'draft', 403, 'Tugas belum dipublikasikan.');

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', auth()->id())
            ->first();

        return Inertia::render('Student/Assignments/Show', [
            'course' => $course->load(['subject', 'schoolClass']),
            'assignment' => $assignment,
            'submission' => $submission,
        ]);
    }

    public function submit(Request $request, Course $course, Assignment $assignment)
    {
        $this->authorizeStudent($course);
        abort_if($assignment->course_id !== $course->id, 404);

        if ($assignment->status === 'closed') {
            return redirect()->back()->withErrors(['submission' => 'Tugas ini telah ditutup.']);
        }

        if ($assignment->isPastDue()) {
            return redirect()->back()->withErrors(['submission' => 'Batas waktu pengumpulan tugas telah lewat.']);
        }

        $allowedMimes = $assignment->allowed_file_types ?? 'pdf,doc,docx,zip,rar,jpg,png';
        $maxKB = ($assignment->max_file_size_mb ?? 10) * 1024;

        $request->validate([
            'text_response' => 'nullable|string',
            'attachment' => "nullable|file|mimes:{$allowedMimes}|max:{$maxKB}",
        ]);

        if (!$request->text_response && !$request->hasFile('attachment')) {
            return redirect()->back()->withErrors(['submission' => 'Kirimkan teks jawaban atau sertakan file lampiran.']);
        }

        $studentId = auth()->id();
        $existingSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $studentId)
            ->first();

        $attachmentPath = $existingSubmission?->attachment_path;

        if ($request->hasFile('attachment')) {
            $newPath = $request->file('attachment')->store('assignment_submissions', 'local');
            
            if ($attachmentPath && Storage::disk('local')->exists($attachmentPath)) {
                Storage::disk('local')->delete($attachmentPath);
            }
            $attachmentPath = $newPath;
        }

        if ($existingSubmission) {
            $existingSubmission->update([
                'text_response' => $request->text_response,
                'attachment_path' => $attachmentPath,
                'submitted_at' => Carbon::now('Asia/Jakarta'),
                'status' => 'resubmitted',
            ]);
        } else {
            AssignmentSubmission::create([
                'assignment_id' => $assignment->id,
                'student_id' => $studentId,
                'text_response' => $request->text_response,
                'attachment_path' => $attachmentPath,
                'submitted_at' => Carbon::now('Asia/Jakarta'),
                'status' => 'submitted',
            ]);
        }

        return redirect()->back()->with('success', 'Tugas berhasil dikirim.');
    }
}