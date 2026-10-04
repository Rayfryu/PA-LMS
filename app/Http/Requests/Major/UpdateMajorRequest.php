<?php

namespace App\Http\Requests\Major;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMajorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('major'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $major = $this->route('major');

        return [
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('majors', 'code')->ignore($major?->id),
            ],
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('majors', 'name')->ignore($major?->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}