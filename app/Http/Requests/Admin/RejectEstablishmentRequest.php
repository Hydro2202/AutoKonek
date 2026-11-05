<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RejectEstablishmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Please give the owner a reason for rejecting this listing.',
            'rejection_reason.min' => 'The reason should be at least 10 characters so the owner knows what to fix.',
        ];
    }
}
