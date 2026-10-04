<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

/**
 * Phase 1 policy foundation.
 *
 * Only admins may view/manage role assignments. LMS domain resources
 * (courses, assignments, assessments, etc.) get their own policies in
 * later phases once those models exist — this establishes the pattern.
 */
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Role $role): bool
    {
        return $user->isAdmin();
    }
}
