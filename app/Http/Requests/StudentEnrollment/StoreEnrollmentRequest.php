<?php

namespace App\Http\Requests\StudentEnrollment;

use App\Rules\ExistsWithRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', new ExistsWithRole('student')],
            'enrolled_at' => ['required', 'date'],
        ];
    }
}