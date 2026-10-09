<?php

namespace App\Http\Requests\StudentEnrollment;

use Illuminate\Foundation\Http\FormRequest;

class TransferEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'new_class_id' => ['required', 'integer', 'exists:classes,id', 'different:current_class_id'],
            'transfer_date' => ['required', 'date'],
        ];
    }
}