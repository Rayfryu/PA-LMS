<?php

namespace App\Http\Requests\AcademicYear;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('academic_year'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $academicYear = $this->route('academic_year');

        return [
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('academic_years', 'name')->ignore($academicYear?->id),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ];
    }
}