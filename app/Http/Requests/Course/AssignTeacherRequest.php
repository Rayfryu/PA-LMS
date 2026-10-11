<?php

namespace App\Http\Requests\Course;

use App\Enums\AcademicYearStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AssignTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return true;
        }

        $roleSlug = strtolower($user->role?->slug ?? $user->role?->name ?? '');
        if (in_array($roleSlug, ['admin', 'administrator'], true)) {
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

    public function rules(): array
    {
        return [
            'teacher_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $user = User::with('role')->find($value);
                        $roleSlug = strtolower($user?->role?->slug ?? $user?->role?->name ?? '');
                        if ($roleSlug !== 'teacher') {
                            $fail('User yang dipilih harus memiliki role guru (teacher).');
                        }
                    }
                },
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $course = $this->route('course');
            if ($course) {
                $course->loadMissing('schoolClass.academicYear');
                $academicYear = $course->schoolClass?->academicYear;

                if ($academicYear) {
                    $statusValue = $academicYear->status instanceof AcademicYearStatus 
                        ? $academicYear->status->value 
                        : $academicYear->status;

                    if ($statusValue === 'archived' || $statusValue === AcademicYearStatus::Archived->value) {
                        $validator->errors()->add('teacher_id', 'Tidak dapat menetapkan atau mengubah pengajar untuk kelas di tahun ajaran yang diarsip.');
                    }
                }
            }
        });
    }
}