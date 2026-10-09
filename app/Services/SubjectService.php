<?php

namespace App\Services;

use App\Models\Subject;

class SubjectService
{
    public function create(array $data): Subject
    {
        return Subject::create($data);
    }

    public function update(Subject $subject, array $data): Subject
    {
        $subject->update($data);
        return $subject;
    }

    public function toggleStatus(Subject $subject): Subject
    {
        $subject->update([
            'is_active' => !$subject->is_active,
        ]);
        return $subject;
    }

    public function delete(Subject $subject): void
    {
        // BR-SUB-006: Prepared for future course relation checks
        $subject->delete();
    }
}