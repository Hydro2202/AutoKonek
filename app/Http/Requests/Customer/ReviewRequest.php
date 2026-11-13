<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    /** On a validation error, return to the review form rather than the top of the page. */
    protected function getRedirectUrl(): string
    {
        return parent::getRedirectUrl().'#review-form';
    }
}
