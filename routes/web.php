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


Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

   Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

    

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



    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', TeacherDashboardController::class)->name('dashboard');
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
    });
});
