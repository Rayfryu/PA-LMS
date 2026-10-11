<?php

namespace App\Http\Requests\Course;

use App\Models\Course;
use App\Models\Role;
use App\Models\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        // Pengecekan multi-metode agar sinkron dengan StoreCourseRequest
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

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'school_class_id' => ['required', 'exists:classes,id'],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $course = $this->route('course');
            $courseId = $course instanceof Course ? $course->id : $course;

            if ($this->subject_id && $this->school_class_id) {
                $schoolClass = SchoolClass::with('academicYear')->find($this->school_class_id);

                if ($schoolClass) {
                    $exists = Course::where('subject_id', $this->subject_id)
                        ->where('school_class_id', $this->school_class_id)
                        ->where('academic_year_id', $schoolClass->academic_year_id)
                        ->where('id', '!=', $courseId)
                        ->exists();

                    if ($exists) {
                        $validator->errors()->add('subject_id', 'This subject is already offered to the selected class.');
                    }
                }
            }
        });
    }
}