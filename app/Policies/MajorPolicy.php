<?php

namespace App\Policies;

use App\Models\Major;
use App\Models\User;

class MajorPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Major $major): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Major $major): bool
    {
        return $user->isAdmin();
    }
}