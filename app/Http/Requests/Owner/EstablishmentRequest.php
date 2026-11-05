<?php

namespace App\Http\Requests\Owner;

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
        $images = config('autokonek.images');

        return [
            // Only active categories may be chosen.
            'category_id' => ['required', Rule::exists('categories', 'id')->where('is_active', true)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'min:20', 'max:3000'],
            // The place comes from the official Philippine list: region and province follow from the city.
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'barangay' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            // GPS position is optional, but latitude and longitude go together.
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'opening_hours' => ['nullable', 'string', 'max:1000'],
            'images' => ['nullable', 'array', 'max:'.$images['max_per_upload']],
            'images.*' => ['image', 'mimes:'.implode(',', $images['mimes']), 'max:'.$images['max_kilobytes']],
        ];
    }

    public function attributes(): array
    {
        return [
            'city_id' => 'city or municipality',
            'barangay' => 'barangay',
        ];
    }

    public function messages(): array
    {
        return [
            'city_id.required' => 'Please choose the city or municipality where the business is.',
            'latitude.required_with' => 'Enter the latitude too, or clear the longitude.',
            'longitude.required_with' => 'Enter the longitude too, or clear the latitude.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be JPG, PNG or WebP.',
            'images.*.max' => 'Each image must be 2 MB or smaller.',
        ];
    }
}
