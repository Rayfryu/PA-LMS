<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Course::class);

        $query = Course::with(['subject', 'schoolClass.major', 'academicYear']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('subject', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })->orWhereHas('schoolClass', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }

        $courses = $query->latest()->paginate(10)->withQueryString();

        // Get selectable active subjects & classes for creation form
        $activeSubjects = Subject::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $schoolClasses = SchoolClass::with('academicYear')->orderBy('name')->get(['id', 'name', 'academic_year_id']);

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses,
            'subjects' => $activeSubjects,
            'schoolClasses' => $schoolClasses,
            'filters' => $request->only(['search', 'class_id']),
            'can' => [
                'create' => $request->user()->can('create', Course::class),
                'update' => $request->user()->can('update', new Course()),
                'delete' => $request->user()->can('delete', new Course()),
            ],
        ]);
    }

    public function store(StoreCourseRequest $request, CourseService $service)
    {
        Gate::authorize('create', Course::class);

        $service->create($request->validated());

        return back()->with('success', 'Course offering created successfully.');
    }

    public function update(UpdateCourseRequest $request, Course $course, CourseService $service)
    {
        Gate::authorize('update', $course);

        $service->update($course, $request->validated());

        return back()->with('success', 'Course offering updated successfully.');
    }

    public function toggleStatus(Course $course, CourseService $service)
    {
        Gate::authorize('update', $course);

        $service->toggleStatus($course);

        return back()->with('success', 'Course status updated successfully.');
    }

    public function destroy(Course $course, CourseService $service)
    {
        Gate::authorize('delete', $course);

        try {
            $service->delete($course);
            return back()->with('success', 'Course offering deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}