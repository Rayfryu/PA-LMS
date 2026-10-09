<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:100', 'unique:subjects,name'],
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }
}