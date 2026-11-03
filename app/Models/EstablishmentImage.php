<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EstablishmentImage extends Model
{
    protected $fillable = ['image_path', 'is_primary'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}
