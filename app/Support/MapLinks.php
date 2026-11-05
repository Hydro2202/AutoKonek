<?php

namespace App\Support;

use App\Models\Establishment;

/**
 * Builds the "get directions" and map links for an establishment.
 *
 * No map API key is needed: these are plain links that open the visitor's own maps app.
 * When the listing has GPS coordinates they are used; otherwise the address is searched.
 * Google Maps and Waze start from the visitor's current location by default.
 * (The in-page route map is drawn by resources/js/modules/route-map.js.)
 */
class MapLinks
{
    public function __construct(private readonly Establishment $establishment) {}

    public static function for(Establishment $establishment): self
    {
        return new self($establishment);
    }

    public function hasCoordinates(): bool
    {
        return $this->establishment->latitude !== null && $this->establishment->longitude !== null;
    }

    /** Opens Google Maps with driving directions to the place. */
    public function googleDirections(): string
    {
        return 'https://www.google.com/maps/dir/?'.http_build_query([
            'api' => 1,
            'destination' => $this->destination(),
            'travelmode' => 'driving',
        ]);
    }

    /** Opens Waze and starts navigating. */
    public function waze(): string
    {
        $query = $this->hasCoordinates()
            ? ['ll' => $this->coordinates(), 'navigate' => 'yes']
            : ['q' => $this->destination(), 'navigate' => 'yes'];

        return 'https://waze.com/ul?'.http_build_query($query);
    }

    /** Shows the place on OpenStreetMap (or searches for the address). */
    public function openStreetMap(): string
    {
        if (! $this->hasCoordinates()) {
            return 'https://www.openstreetmap.org/search?'.http_build_query(['query' => $this->destination()]);
        }

        [$lat, $lng] = [$this->establishment->latitude, $this->establishment->longitude];

        return "https://www.openstreetmap.org/?mlat={$lat}&mlon={$lng}#map=17/{$lat}/{$lng}";
    }

    private function coordinates(): string
    {
        return $this->establishment->latitude.','.$this->establishment->longitude;
    }

    /** Coordinates when known, otherwise a full address that map apps can search. */
    private function destination(): string
    {
        if ($this->hasCoordinates()) {
            return $this->coordinates();
        }

        return collect([
            $this->establishment->name,
            $this->establishment->address,
            $this->establishment->barangay,
            $this->establishment->city?->name,
            $this->establishment->city?->areaName(),
            'Philippines',
        ])->filter()->implode(', ');
    }
}
