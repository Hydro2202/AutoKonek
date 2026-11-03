<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validates the owner's listing form. Status is never accepted from the request. */
class EstablishmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() ?? false;
    }

    public function rules(): array
    {
        return [
            // Only active categories may be chosen.
            'category_id' => ['required', Rule::exists('categories', 'id')->where('is_active', true)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'min:20', 'max:3000'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'municipality' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'opening_hours' => ['nullable', 'string', 'max:1000'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be JPG, PNG or WebP.',
            'images.*.max' => 'Each image must be 2 MB or smaller.',
        ];
    }
}
