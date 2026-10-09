<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Role;
use App\Models\User;

class CoursePolicy
{
    private function checkIsAdmin(User $user): bool
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return true;
        }

        $roleName = strtolower($user->role?->name ?? '');
        $roleSlug = strtolower($user->role?->slug ?? '');

        if (in_array('admin', [$roleName, $roleSlug], true)) {
            return true;
        }

        if ($user->role_id) {
            return Role::where('id', $user->role_id)
                ->where(function ($query) {
                    $query->where('name', 'like', 'admin')
                          ->orWhere('slug', 'like', 'admin');
                })->exists();
        }

        return false;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Course $course): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->checkIsAdmin($user);
    }

    public function update(User $user, Course $course): bool
    {
        return $this->checkIsAdmin($user);
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->checkIsAdmin($user);
    }
}