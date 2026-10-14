<?php

namespace App\Models;
use App\Models\LearningMaterial;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'school_class_id',
        'academic_year_id',
        'teacher_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schedules()
    {
        return $this->hasMany(CourseSchedule::class);
    }

    // Tambahkan ini untuk relasi ke tabel learning_materials
    public function learningMaterials()
    {
        return $this->hasMany(LearningMaterial::class);
    }

    // Tambahkan ini jika Course kamu ditangani oleh banyak guru (Many-to-Many)
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'course_teacher', 'course_id', 'teacher_id');
    }
}