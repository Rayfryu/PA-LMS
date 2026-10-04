<?php

namespace App\Models;

use App\Enums\AcademicYearStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'start_date', 'end_date', 'status'])]
class AcademicYear extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => AcademicYearStatus::class,
        ];
    }

    public function isActive(): bool
    {
        return $this->status === AcademicYearStatus::Active;
    }

    public function isArchived(): bool
    {
        return $this->status === AcademicYearStatus::Archived;
    }

    public function isDraft(): bool
    {
        return $this->status === AcademicYearStatus::Draft;
    }

    // classes() relationship is intentionally omitted — the `classes` table
    // does not exist yet (Phase 2B). Add `hasMany(Class::class)` there.
}