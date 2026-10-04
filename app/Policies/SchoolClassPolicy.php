<?php

namespace App\Policies;

use App\Models\SchoolClass;
use App\Models\User;

// Named SchoolClassPolicy, not ClassPolicy — Laravel's policy auto-discovery
// maps a model to App\Policies\{ModelName}Policy by exact name, and our
// model is named SchoolClass (see SchoolClass.php for why), so the policy
// must follow suit for auto-discovery to find it.
class SchoolClassPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SchoolClass $class): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, SchoolClass $class): bool
    {
        return $user->isAdmin();
    }
}