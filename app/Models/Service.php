<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = ['name', 'description', 'price'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }
}
