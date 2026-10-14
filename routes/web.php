<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\MajorController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\StudentEnrollmentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\CourseController;

// Import tambahan untuk Learning Material
use App\Http\Controllers\Teacher\LearningMaterialController;
use App\Http\Controllers\DownloadController;

// Import tambahan untuk Assignment
use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Student\AssignmentSubmissionController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Rute Unduh File Terproteksi (Akses untuk Guru Pembuat & Siswa yang Enrolled)
    Route::get('/downloads/materials/{material}', [DownloadController::class, 'downloadMaterial'])->name('materials.download');
    Route::get('/downloads/submissions/{submission}', [DownloadController::class, 'downloadSubmission'])->name('submissions.download');

    // Rute Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

        Route::get('courses/{course}/schedules', [\App\Http\Controllers\Admin\CourseScheduleController::class, 'index'])->name('courses.schedules.index');
        Route::post('courses/{course}/schedules', [\App\Http\Controllers\Admin\CourseScheduleController::class, 'store'])->name('courses.schedules.store');
        Route::put('courses/{course}/schedules/{schedule}', [\App\Http\Controllers\Admin\CourseScheduleController::class, 'update'])->name('courses.schedules.update');
        Route::delete('courses/{course}/schedules/{schedule}', [\App\Http\Controllers\Admin\CourseScheduleController::class, 'destroy'])->name('courses.schedules.destroy');

        Route::prefix('classes/{class}/students')->name('classes.students.')->group(function () {
            Route::get('/', [StudentEnrollmentController::class, 'index'])->name('index');
            Route::post('/', [StudentEnrollmentController::class, 'store'])->name('store');
            Route::post('/{enrollment}/transfer', [StudentEnrollmentController::class, 'transfer'])->name('transfer');
            Route::post('/{enrollment}/withdraw', [StudentEnrollmentController::class, 'withdraw'])->name('withdraw');
        });

        Route::resource('academic-years', AcademicYearController::class)
            ->except(['show', 'destroy']);
        Route::post('academic-years/{academic_year}/activate', [AcademicYearController::class, 'activate'])
            ->name('academic-years.activate');
        Route::post('academic-years/{academic_year}/archive', [AcademicYearController::class, 'archive'])
            ->name('academic-years.archive');

        Route::resource('subjects', SubjectController::class)->except(['create', 'show', 'edit']);
        Route::post('subjects/{subject}/toggle-status', [SubjectController::class, 'toggleStatus'])->name('subjects.toggle-status');

        Route::resource('majors', MajorController::class)->except(['show', 'destroy']);
        Route::resource('classes', ClassController::class)->except(['show', 'destroy']);

        Route::resource('courses', CourseController::class)->except(['create', 'show', 'edit']);
        Route::post('courses/{course}/toggle-status', [CourseController::class, 'toggleStatus'])->name('courses.toggle-status');
        Route::put('courses/{course}/assign-teacher', [CourseController::class, 'assignTeacher'])->name('courses.assign-teacher');
    });


    // Rute Guru (Teacher)
    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', TeacherDashboardController::class)->name('dashboard');

        // Rute Manajemen Materi oleh Guru
        Route::get('courses/{course}/materials', [LearningMaterialController::class, 'index'])->name('courses.materials.index');
        Route::post('courses/{course}/materials', [LearningMaterialController::class, 'store'])->name('courses.materials.store');
        Route::put('courses/{course}/materials/{material}', [LearningMaterialController::class, 'update'])->name('courses.materials.update');
        Route::delete('courses/{course}/materials/{material}', [LearningMaterialController::class, 'destroy'])->name('courses.materials.destroy');

        // Rute Manajemen Tugas (Assignment) oleh Guru
        Route::prefix('courses/{course}')->name('courses.')->group(function () {
            Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
            Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
            Route::put('/assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
            Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
            Route::get('/assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])->name('assignments.submissions');
        });
    });

    // Rute Siswa (Student)
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');

        // Rute Tugas (Assignment) untuk Siswa
        Route::prefix('courses/{course}')->name('courses.')->group(function () {
            Route::get('/assignments', [AssignmentSubmissionController::class, 'index'])->name('assignments.index');
            Route::get('/assignments/{assignment}', [AssignmentSubmissionController::class, 'show'])->name('assignments.show');
            Route::post('/assignments/{assignment}/submit', [AssignmentSubmissionController::class, 'submit'])->name('assignments.submit');
        });
    });
});