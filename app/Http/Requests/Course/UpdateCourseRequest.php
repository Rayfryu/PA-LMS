<?php

namespace App\Http\Requests\Course;

use App\Models\Course;
use App\Models\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $roleName = $this->user()->role?->name ?? ($this->user()->role ?? '');
        return $roleName === 'admin';
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