<?php

namespace App\Models;

use App\Enums\GradeLevel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Named SchoolClass, not Class — `class` is a reserved word in PHP and cannot
// be used as a class name (a bare variable $class is fine, which is why
// route-model-binding parameters elsewhere in this file are still $class).
#[Fillable(['academic_year_id', 'major_id', 'grade_level', 'name', 'homeroom_teacher_id', 'capacity'])]
class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected function casts(): array
    {
        return [
            'grade_level' => GradeLevel::class,
            'capacity' => 'integer',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    // classStudents() / courses() are intentionally omitted — those tables
    // don't exist yet (Phase 2C+). Add hasMany() relations there.
}