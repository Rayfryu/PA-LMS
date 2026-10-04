<?php

namespace App\Services;

use App\Enums\AcademicYearStatus;
use App\Models\AcademicYear;
use Illuminate\Validation\ValidationException;

/**
 * Owns Academic Year state-transition rules (BR-AY-001/002/003) so this
 * logic lives in one place instead of being duplicated across the web
 * controller and any future API controller for this resource.
 */
class AcademicYearService
{
    /**
     * Activate a draft academic year. Rejects if any other academic year
     * is already active (BR-AY-001) — does NOT auto-archive others.
     *
     * @throws ValidationException
     */
    public function activate(AcademicYear $academicYear): AcademicYear
    {
        $this->assertTransitionAllowed($academicYear, AcademicYearStatus::Active);

        $alreadyActive = AcademicYear::query()
            ->where('status', AcademicYearStatus::Active)
            ->where('id', '!=', $academicYear->id)
            ->exists();

        if ($alreadyActive) {
            throw ValidationException::withMessages([
                'status' => 'Another academic year is already active. Archive it first before activating a new one.',
            ]);
        }

        $academicYear->update(['status' => AcademicYearStatus::Active]);

        return $academicYear->refresh();
    }

    /**
     * Archive a draft or active academic year. Permanent — archived can
     * never transition anywhere else (BR-AY-002).
     *
     * @throws ValidationException
     */
    public function archive(AcademicYear $academicYear): AcademicYear
    {
        $this->assertTransitionAllowed($academicYear, AcademicYearStatus::Archived);

        $academicYear->update(['status' => AcademicYearStatus::Archived]);

        return $academicYear->refresh();
    }

    /**
     * @throws ValidationException
     */
    protected function assertTransitionAllowed(AcademicYear $academicYear, AcademicYearStatus $target): void
    {
        if (! $academicYear->status->canTransitionTo($target)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition academic year from [{$academicYear->status->value}] to [{$target->value}].",
            ]);
        }
    }
}