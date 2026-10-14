<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LearningMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LearningMaterialController extends Controller
{
    public function index(Course $course)
    {
        // Pastikan guru ini yang mengajar course tersebut
       // Jika 1 course punya 1 guru (ada kolom teacher_id)
abort_if($course->teacher_id !== auth()->id(), 403, 'Unauthorized access.');
        $materials = $course->learningMaterials()->latest()->get();

        return Inertia::render('Teacher/LearningMaterials/Index', [
            'course' => $course,
            'materials' => $materials,
        ]);
    }

    public function store(Request $request, Course $course)
    {
         abort_if($course->teacher_id !== auth()->id(), 403, 'Unauthorized access.');

      $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            // Tambahkan format gambar dan video (jpg, jpeg, png, mp4, avi, mkv)
            // Ubah batas ukuran menjadi 51200 (50MB) karena ukuran video biasanya besar
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,rar,jpg,jpeg,png,mp4,avi,mkv|max:51200', 
            'status' => 'required|in:draft,published',
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            // Disimpan di disk private ('local'), bukan 'public' agar aman
            $path = $request->file('attachment')->store('learning_materials', 'local');
        }

        $course->learningMaterials()->create([
            'teacher_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'content' => $validated['content'] ?? null,
            'attachment_path' => $path,
            'status' => $validated['status'],
            'published_at' => $validated['status'] === 'published' ? now() : null,
        ]);

        return redirect()->back()->with('success', 'Materi berhasil ditambahkan.');
    }

    public function destroy(Course $course, LearningMaterial $material)
    {
        abort_if($material->teacher_id !== auth()->id(), 403);

        if ($material->attachment_path) {
            Storage::disk('local')->delete($material->attachment_path);
        }

        $material->delete();

        return redirect()->back()->with('success', 'Materi berhasil dihapus.');
    }
}