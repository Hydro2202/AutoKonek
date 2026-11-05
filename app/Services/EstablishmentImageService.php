<?php

namespace App\Services;

use App\Models\Establishment;
use App\Models\EstablishmentImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** Stores, promotes and removes establishment images on the public disk. */
class EstablishmentImageService
{
    private const DISK = 'public';

    /**
     * Store uploaded files. Laravel generates a random file name, so the visitor's
     * original name (and any path tricks in it) is never used.
     *
     * @param  array<int, UploadedFile>  $files
     */
    public function store(Establishment $establishment, array $files): void
    {
        $hasPrimary = $establishment->images()->where('is_primary', true)->exists();

        foreach ($files as $file) {
            $path = $file->store('establishments/'.$establishment->id, self::DISK);
            $establishment->images()->create(['image_path' => $path, 'is_primary' => ! $hasPrimary]);
            $hasPrimary = true;
        }
    }

    public function wouldExceedLimit(Establishment $establishment, int $incoming): bool
    {
        return $establishment->images()->count() + $incoming > config('autokonek.images.max_per_establishment');
    }

    public function makePrimary(Establishment $establishment, EstablishmentImage $image): void
    {
        $establishment->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);
    }

    public function delete(Establishment $establishment, EstablishmentImage $image): void
    {
        $wasPrimary = $image->is_primary;

        Storage::disk(self::DISK)->delete($image->image_path);
        $image->delete();

        // Always keep a main image while any remain.
        if ($wasPrimary) {
            $establishment->images()->first()?->update(['is_primary' => true]);
        }
    }

    /** Remove all files for an establishment that is about to be deleted. */
    public function deleteAllFiles(Establishment $establishment): void
    {
        foreach ($establishment->images as $image) {
            Storage::disk(self::DISK)->delete($image->image_path);
        }
    }
}
