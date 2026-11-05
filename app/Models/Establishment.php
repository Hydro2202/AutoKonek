<?php

namespace App\Models;

use App\Support\MapLinks;
use Database\Factories\EstablishmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'category_id', 'name', 'description', 'address', 'city_id', 'barangay',
        'latitude', 'longitude', 'contact_number', 'contact_email', 'opening_hours',
    ];

    protected $fillable = self::LISTING_FIELDS;

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
        ];
    }

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

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
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

    /** Filter by the place the business is in. Any combination of region, province and city. */
    public function scopeInPlace(Builder $query, ?int $regionId, ?int $provinceId, ?int $cityId): Builder
    {
        return $query
            ->when($cityId, fn (Builder $q) => $q->where('city_id', $cityId))
            ->when($provinceId && ! $cityId, fn (Builder $q) => $q->whereHas('city', fn (Builder $c) => $c->where('province_id', $provinceId)))
            ->when($regionId && ! $provinceId && ! $cityId, fn (Builder $q) => $q->whereHas('city', fn (Builder $c) => $c->where('region_id', $regionId)));
    }

    /**
     * Adds a "distance_km" column (straight-line, haversine formula) from the given point.
     * Listings without coordinates get NULL and are sorted last.
     */
    public function scopeWithDistanceFrom(Builder $query, float $latitude, float $longitude): Builder
    {
        $earthRadiusKm = 6371;

        return $query->addSelect(['distance_km' => fn ($sub) => $sub->selectRaw(
            "CASE WHEN establishments.latitude IS NULL THEN NULL ELSE {$earthRadiusKm} * 2 * asin(sqrt(
                power(sin(radians(establishments.latitude - ?) / 2), 2) +
                cos(radians(?)) * cos(radians(establishments.latitude)) *
                power(sin(radians(establishments.longitude - ?) / 2), 2)
            )) END",
            [$latitude, $latitude, $longitude]
        )]);
    }

    public function averageRating(): float
    {
        return round((float) ($this->reviews_avg_rating ?? $this->reviews()->avg('rating') ?? 0), 1);
    }

    public function reviewCount(): int
    {
        return (int) ($this->reviews_count ?? $this->reviews()->count());
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function mapLinks(): MapLinks
    {
        return MapLinks::for($this);
    }

    /** For example "Brgy. Ibabang Dupay, Lucena City, Quezon". */
    public function locationLabel(): string
    {
        return collect([
            filled($this->barangay) ? 'Brgy. '.$this->barangay : null,
            $this->city?->name,
            $this->city?->areaName(),
        ])->filter()->implode(', ');
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
}
