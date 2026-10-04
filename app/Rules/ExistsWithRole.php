<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that a submitted user id both exists AND has the required role.
 * A plain `exists:users,id` rule only proves referential integrity — it says
 * nothing about role, which is exactly the gap BR-ROLE-* (Phase 2 report)
 * flagged: FK constraints can't express "this user must be a teacher".
 *
 * Reusable across any field that references a User by a specific role
 * (homeroom_teacher_id here; course_teachers.teacher_id, class_students.user_id
 * in later phases), so the check lives in one place instead of being
 * re-implemented per Form Request.
 */
class ExistsWithRole implements ValidationRule
{
    public function __construct(protected string $roleSlug) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return; // combine with a `nullable` rule when the field is optional
        }

        $user = User::query()->find($value);

        if (! $user) {
            $fail("The selected {$attribute} does not exist.");

            return;
        }

        if (! $user->hasRole($this->roleSlug)) {
            $fail("The selected {$attribute} must be a user with the '{$this->roleSlug}' role.");
        }
    }
}