<?php

namespace App\Policies;

use App\Models\ClassStudent;
use App\Models\User;

class ClassStudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, ClassStudent $classStudent): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, ClassStudent $classStudent): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, ClassStudent $classStudent): bool
    {
        return $user->isAdmin();
    }
}