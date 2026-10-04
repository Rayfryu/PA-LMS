<?php

namespace App\Http\Requests\ClassRequest;

use App\Models\Role;
use App\Rules\ExistsWithRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('class'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $class = $this->route('class');

        return [
            // No status restriction here on purpose: the value may legitimately
            // be the class's own (possibly archived) academic year, unchanged.
            // ClassService::update() is what rejects an actual MOVE away from
            // an archived academic year (BR-CLS-004).
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'major_id' => ['required', 'integer', Rule::exists('majors', 'id')],
            'grade_level' => ['required', new Enum(\App\Enums\GradeLevel::class)],
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('classes', 'name')
                    ->where(fn ($query) => $query
                        ->where('academic_year_id', $this->input('academic_year_id'))
                        ->where('major_id', $this->input('major_id')))
                    ->ignore($class?->id),
            ],
            'homeroom_teacher_id' => ['nullable', 'integer', new ExistsWithRole(Role::TEACHER)],
            'capacity' => ['nullable', 'integer', 'min:1'],
        ];
    }
}