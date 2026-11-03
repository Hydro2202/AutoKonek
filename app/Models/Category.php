<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function establishments(): HasMany
    {
        return $this->hasMany(Establishment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Icon name used by the <x-icon> component. */
    public function icon(): string
    {
        return match ($this->slug) {
            'gas-stations' => 'fuel',
            'car-wash' => 'droplet',
            'auto-repair' => 'wrench',
            'tire-and-vulcanizing-shops' => 'tire',
            'parking-facilities' => 'parking',
            'battery-services' => 'battery',
            default => 'car',
        };
    }
}
