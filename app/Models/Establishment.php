<?php

namespace App\Models;

use Database\Factories\EstablishmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Establishment extends Model
{
    /** @use HasFactory<EstablishmentFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Fields an owner may submit. "user_id", "status" and "rejection_reason"
     * are NOT fillable so an owner can never approve their own listing.
     */
    public const LISTING_FIELDS = [
        'category_id', 'name', 'description', 'address', 'city',
        'municipality', 'contact_number', 'contact_email', 'opening_hours',
    ];

    protected $fillable = self::LISTING_FIELDS;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('name');
    }

    public function images(): HasMany
    {
        return $this->hasMany(EstablishmentImage::class)->orderByDesc('is_primary')->orderBy('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /** Adds reviews_count and reviews_avg_rating columns. */
    public function scopeWithRatings(Builder $query): Builder
    {
        return $query->withCount('reviews')->withAvg('reviews', 'rating');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! filled($term)) {
            return $query;
        }

        // ILIKE is PostgreSQL's case-insensitive LIKE. Escape wildcards typed by the user.
        $like = '%'.addcslashes($term, '%_\\').'%';

        return $query->where(fn (Builder $q) => $q
            ->where('name', 'ilike', $like)
            ->orWhere('description', 'ilike', $like)
            ->orWhere('address', 'ilike', $like));
    }

    public function averageRating(): float
    {
        return round((float) ($this->reviews_avg_rating ?? $this->reviews()->avg('rating') ?? 0), 1);
    }

    public function reviewCount(): int
    {
        return (int) ($this->reviews_count ?? $this->reviews()->count());
    }

    /** Short "City, Municipality" label. */
    public function locationLabel(): string
    {
        return collect([$this->municipality, $this->city])->filter()->unique()->implode(', ');
    }

    /** Primary uploaded image, or a local category placeholder. */
    public function imageUrl(): string
    {
        $image = $this->images->firstWhere('is_primary', true) ?? $this->images->first();

        return $image ? $image->url() : asset('images/placeholders/'.($this->category?->slug ?? 'default').'.svg');
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'establishment';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /** Remove files from disk for all images (rows go with the cascade). */
    public function deleteImageFiles(): void
    {
        foreach ($this->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }
    }
}
