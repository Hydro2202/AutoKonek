<?php

namespace App\Http\Requests\Customer;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'target_type' => ['required', Rule::in(['establishment', 'review'])],
            'target_id' => ['required', 'integer'],
            'reason' => ['required', Rule::in(array_keys(Report::REASONS))],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
