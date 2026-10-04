<?php

namespace App\Http\Requests\Major;

use Illuminate\Foundation\Http\FormRequest;

class StoreMajorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Major::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:majors,code'],
            'name' => ['required', 'string', 'max:100', 'unique:majors,name'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}