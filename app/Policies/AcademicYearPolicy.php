<?php

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\User;

class AcademicYearPolicy
{
    public function viewAny(User $user): bool
    {
        // Admin, Teacher, and Student can all view — read-only for non-admins.
        return true;
    }

    public function view(User $user, AcademicYear $academicYear): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, AcademicYear $academicYear): bool
    {
        return $user->isAdmin();
    }

    public function transition(User $user, AcademicYear $academicYear): bool
    {
        return $user->isAdmin();
    }
}