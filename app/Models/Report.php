<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    public const REASONS = [
        'inaccurate_information' => 'Inaccurate information',
        'inappropriate_content' => 'Inappropriate content',
        'spam' => 'Spam or advertising',
        'closed_or_nonexistent' => 'Business closed or does not exist',
        'other' => 'Other',
    ];

    // user_id, targets, status and resolver are set by the application only.
    protected $fillable = ['reason', 'description'];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? ucfirst(str_replace('_', ' ', $this->reason));
    }
}
