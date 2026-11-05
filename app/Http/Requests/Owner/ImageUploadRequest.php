<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class ImageUploadRequest extends FormRequest
{
    public function rules(): array
    {
        $images = config('autokonek.images');

        return [
            'images' => ['required', 'array', 'max:'.$images['max_per_upload']],
            'images.*' => ['image', 'mimes:'.implode(',', $images['mimes']), 'max:'.$images['max_kilobytes']],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'Please choose at least one image.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be JPG, PNG or WebP.',
            'images.*.max' => 'Each image must be 2 MB or smaller.',
        ];
    }
}
