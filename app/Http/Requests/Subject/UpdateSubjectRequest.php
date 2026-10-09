<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => strtoupper(trim($this->code))]);
        }
        if ($this->has('name')) {
            $this->merge(['name' => trim($this->name)]);
        }
    }

    public function rules(): array
    {
        $subjectId = $this->route('subject')->id ?? $this->route('subject');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('subjects', 'name')->ignore($subjectId)],
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects', 'code')->ignore($subjectId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }
}