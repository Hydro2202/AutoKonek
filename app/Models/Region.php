<?php

namespace App\Models;

use Database\Factories\RegionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    /** @use HasFactory<RegionFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['psgc_code', 'name', 'official_name', 'island_group'];

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class)->orderBy('name');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /** For example "Region IV-A (CALABARZON)" or "National Capital Region (NCR)". */
    public function label(): string
    {
        return $this->official_name === $this->name
            ? $this->name
            : $this->official_name.' ('.$this->name.')';
    }
}
