<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseSchedule\CourseScheduleRequest; // BENAR// SALAH
use App\Models\Course;
use App\Models\CourseSchedule;
use Inertia\Inertia;

class CourseScheduleController extends Controller
{
    public function index(Course $course)
    {
        $course->load(['subject', 'schoolClass.academicYear', 'teacher', 'schedules' => function($q) {
            $q->orderByRaw("FIELD(day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat')")
              ->orderBy('start_time');
        }]);

        return Inertia::render('Admin/CourseSchedules/Index', [
            'course' => $course
        ]);
    }

    public function store(CourseScheduleRequest $request, Course $course)
    {
        $course->schedules()->create($request->validated());
        return back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function update(CourseScheduleRequest $request, Course $course, CourseSchedule $schedule)
    {
        $schedule->update($request->validated());
        return back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Course $course, CourseSchedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'Jadwal berhasil dihapus.');
    }
}