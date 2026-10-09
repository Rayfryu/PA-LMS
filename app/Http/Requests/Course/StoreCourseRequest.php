<?php

namespace App\Http\Requests\Course;

use App\Enums\AcademicYearStatus;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCourseRequest extends FormRequest
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
            'subject_id' => [
                'required',
                'exists:subjects,id',
                Rule::exists('subjects', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'school_class_id' => ['required', 'exists:classes,id'],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->subject_id && $this->school_class_id) {
                $schoolClass = SchoolClass::with('academicYear')->find($this->school_class_id);
                $subject = Subject::find($this->subject_id);

                if ($subject && !$subject->is_active) {
                    $validator->errors()->add('subject_id', 'Cannot create a course using an inactive Subject.');
                }

                if ($schoolClass) {
                    $academicYear = $schoolClass->academicYear;
                    $statusValue = $academicYear?->status instanceof AcademicYearStatus 
                        ? $academicYear->status->value 
                        : $academicYear?->status;

                    if ($statusValue === AcademicYearStatus::Archived->value) {
                        $validator->errors()->add('school_class_id', 'Cannot create a course for a class in an archived academic year.');
                    }

                    $exists = \App\Models\Course::where('subject_id', $this->subject_id)
                        ->where('school_class_id', $this->school_class_id)
                        ->where('academic_year_id', $schoolClass->academic_year_id)
                        ->exists();

                    if ($exists) {
                        $validator->errors()->add('subject_id', 'This subject is already offered to the selected class for this academic year.');
                    }
                }
            }
        });
    }
}