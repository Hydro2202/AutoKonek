<?php

namespace App\Http\Requests\Admin;

use App\Models\FuelPrice;
use Illuminate\Foundation\Http\FormRequest;

/** A new weekly DOE price adjustment: one change per fuel, in pesos per liter (negative for a rollback). */
class StoreFuelPriceRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $latest = FuelPrice::max('effective_on');

        return [
            'effective_on' => ['required', 'date', ...($latest ? ['after:'.$latest] : [])],
            'gasoline' => ['required', 'numeric', 'between:-50,50', 'decimal:0,2'],
            'diesel' => ['required', 'numeric', 'between:-50,50', 'decimal:0,2'],
            'kerosene' => ['required', 'numeric', 'between:-50,50', 'decimal:0,2'],
            'source' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'effective_on.after' => 'The date must be after the latest week on record. To fix the latest week, remove it first.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'effective_on' => 'effective date',
            'gasoline' => 'gasoline change',
            'diesel' => 'diesel change',
            'kerosene' => 'kerosene change',
        ];
    }
}
