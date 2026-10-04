<?php

namespace App\Services;

use App\Models\SchoolClass;
use Illuminate\Validation\ValidationException;

/**
 * Owns Class create/update logic that goes beyond plain field validation.
 * Field-shape validation (types, existence, role, active-major-on-create,
 * academic-year-status-on-create, name uniqueness) lives in the Form
 * Requests, since those are static, context-free rules Laravel's validator
 * already expresses well. What belongs here is logic that depends on
 * comparing the entity's CURRENT state to the incoming change — the same
 * split used by AcademicYearService in Phase 2A.
 */
class ClassService
{
    public function create(array $data): SchoolClass
    {
        return SchoolClass::query()->create($data);
    }

    /**
     * BR-CLS-004: a class belonging to an archived (historical) academic
     * year can never be moved to a different academic year. Every other
     * field remains editable.
     *
     * @throws ValidationException
     */
    public function update(SchoolClass $class, array $data): SchoolClass
    {
        $currentAcademicYearId = $class->academic_year_id;
        $incomingAcademicYearId = (int) ($data['academic_year_id'] ?? $currentAcademicYearId);

        if ($class->academicYear->isArchived() && $incomingAcademicYearId !== $currentAcademicYearId) {
            throw ValidationException::withMessages([
                'academic_year_id' => 'This class belongs to an archived academic year and cannot be moved to another one.',
            ]);
        }

        $class->update($data);

        return $class->refresh();
    }
}