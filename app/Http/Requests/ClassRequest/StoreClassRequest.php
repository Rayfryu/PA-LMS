<?php

namespace App\Http\Requests\ClassRequest;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Rules\ExistsWithRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', SchoolClass::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // BR-CLS-003: a class may only be created under a draft or active academic year.
            'academic_year_id' => [
                'required', 'integer',
                Rule::exists('academic_years', 'id')->whereIn('status', ['draft', 'active']),
            ],
            // BR-CLS-005/006 (major side): only an active major accepts new classes.
            'major_id' => [
                'required', 'integer',
                Rule::exists('majors', 'id')->where('is_active', true),
            ],
            'grade_level' => ['required', new Enum(\App\Enums\GradeLevel::class)],
            // BR-CLS-009: unique within (academic_year_id, major_id).
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('classes', 'name')
                    ->where(fn ($query) => $query
                        ->where('academic_year_id', $this->input('academic_year_id'))
                        ->where('major_id', $this->input('major_id'))),
            ],
            // BR-CLS-005: homeroom teacher, if given, must be a User with role=teacher.
            'homeroom_teacher_id' => ['nullable', 'integer', new ExistsWithRole(Role::TEACHER)],
            // BR-CLS-007: positive integer when provided; class may have no fixed capacity.
            'capacity' => ['nullable', 'integer', 'min:1'],
        ];
    }
}