<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\EstablishmentImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    private const MAX_IMAGES = 8;

    public function index(Establishment $establishment)
    {
        Gate::authorize('manage', $establishment);

        return view('owner.images', ['establishment' => $establishment->load('images')]);
    }

    public function store(Request $request, Establishment $establishment): RedirectResponse
    {
        Gate::authorize('manage', $establishment);

        $request->validate([
            'images' => ['required', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'images.required' => 'Please choose at least one image.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be JPG, PNG or WebP.',
            'images.*.max' => 'Each image must be 2 MB or smaller.',
        ]);

        if ($establishment->images()->count() + count($request->file('images')) > self::MAX_IMAGES) {
            return back()->with('error', 'An establishment can have at most '.self::MAX_IMAGES.' images.');
        }

        self::saveUploads($establishment, $request->file('images'));

        return back()->with('success', 'Images uploaded.');
    }

    public function makePrimary(Establishment $establishment, EstablishmentImage $image): RedirectResponse
    {
        Gate::authorize('manage', $establishment);
        abort_unless($image->establishment_id === $establishment->id, 404);

        $establishment->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Main image updated.');
    }

    public function destroy(Establishment $establishment, EstablishmentImage $image): RedirectResponse
    {
        Gate::authorize('manage', $establishment);
        abort_unless($image->establishment_id === $establishment->id, 404);

        $wasPrimary = $image->is_primary;
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        // Keep a main image if any remain.
        if ($wasPrimary) {
            $establishment->images()->first()?->update(['is_primary' => true]);
        }

        return back()->with('success', 'Image removed.');
    }

    /**
     * Store uploaded files. Laravel generates a random file name, so the user's
     * original name (and any path tricks in it) is never used.
     *
     * @param  array<\Illuminate\Http\UploadedFile>  $files
     */
    public static function saveUploads(Establishment $establishment, array $files): void
    {
        $hasPrimary = $establishment->images()->where('is_primary', true)->exists();

        foreach ($files as $file) {
            $path = $file->store('establishments/'.$establishment->id, 'public');
            $establishment->images()->create(['image_path' => $path, 'is_primary' => ! $hasPrimary]);
            $hasPrimary = true;
        }
    }
}
