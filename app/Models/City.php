<?php

namespace App\Models;

use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A city or a municipality (see is_city). */
class City extends Model
{
    /** @use HasFactory<CityFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['region_id', 'province_id', 'psgc_code', 'name', 'is_city'];

    protected function casts(): array
    {
        return ['is_city' => 'boolean'];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function establishments(): HasMany
    {
        return $this->hasMany(Establishment::class);
    }

    /** The province, or "Metro Manila" / the region for places that have no province. */
    public function areaName(): string
    {
        if ($this->province) {
            return $this->province->name;
        }

        return $this->region?->name === 'NCR' ? 'Metro Manila' : (string) $this->region?->name;
    }

    /** For example "Lucena City, Quezon". */
    public function fullName(): string
    {
        return $this->name.', '.$this->areaName();
    }
}
