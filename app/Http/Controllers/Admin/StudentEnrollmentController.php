<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\ClassStudent;
use App\Models\User;
use App\Services\StudentEnrollmentService;
use App\Http\Requests\StudentEnrollment\StoreEnrollmentRequest;
use App\Http\Requests\StudentEnrollment\TransferEnrollmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class StudentEnrollmentController extends Controller
{
    public function index(SchoolClass $class)
    {
        Gate::authorize('viewAny', ClassStudent::class);
        $class->load(['academicYear', 'major', 'homeroomTeacher']);
        
        $enrollments = ClassStudent::with('user')
            ->where('class_id', $class->id)
            ->orderBy('status')
            ->get();

        $activeCount = $enrollments->where('status', \App\Enums\EnrollmentStatus::Active)->count();

        // Get eligible students for the dropdown
        $eligibleStudents = User::where('role_id', \App\Models\Role::where('name', 'student')->first()->id)->get();

        return Inertia::render('Admin/Classes/Students/Index', [
            'schoolClass' => $class,
            'enrollments' => $enrollments,
            'activeCount' => $activeCount,
            'eligibleStudents' => $eligibleStudents,
        ]);
    }

    public function store(StoreEnrollmentRequest $request, SchoolClass $class, StudentEnrollmentService $service)
    {
        Gate::authorize('create', ClassStudent::class);
        
        try {
            $service->enroll($class, $request->user_id, $request->enrolled_at);
            return back()->with('success', 'Student successfully enrolled.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function transfer(TransferEnrollmentRequest $request, SchoolClass $class, ClassStudent $enrollment, StudentEnrollmentService $service)
    {
        Gate::authorize('update', $enrollment);
        $newClass = SchoolClass::findOrFail($request->new_class_id);

        try {
            $service->transfer($enrollment, $newClass, $request->transfer_date);
            return back()->with('success', 'Student successfully transferred.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function withdraw(Request $request, SchoolClass $class, ClassStudent $enrollment, StudentEnrollmentService $service)
    {
        Gate::authorize('delete', $enrollment);
        $request->validate(['withdraw_date' => 'required|date']);

        try {
            $service->withdraw($enrollment, $request->withdraw_date);
            return back()->with('success', 'Student successfully withdrawn.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}