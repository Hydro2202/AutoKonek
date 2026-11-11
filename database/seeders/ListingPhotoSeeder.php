<?php

namespace Database\Seeders;

use App\Models\Establishment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches freely licensed photos (from database/data/listing-photos/) to the demo
 * listings that were created from OpenStreetMap, matched by their "osm_ref".
 * Each photo keeps its author and licence credit. Safe to run more than once.
 */
class ListingPhotoSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<string, array{file: string, credit: string, credit_url: string}> $photos */
        $photos = json_decode(file_get_contents(database_path('data/listing-photos.json')), true, 512, JSON_THROW_ON_ERROR)['photos'];

        foreach ($photos as $osmRef => $photo) {
            $establishment = Establishment::where('osm_ref', $osmRef)->first();

            if (! $establishment || $establishment->images()->where('credit', $photo['credit'])->exists()) {
                continue;
            }

            $path = 'establishments/'.$establishment->id.'/'.$photo['file'];
            Storage::disk('public')->put($path, file_get_contents(database_path('data/listing-photos/'.$photo['file'])));

            $establishment->images()->update(['is_primary' => false]);
            $establishment->images()->create([
                'image_path' => $path,
                'is_primary' => true,
                'credit' => $photo['credit'],
                'credit_url' => $photo['credit_url'],
            ]);
        }
    }
}
