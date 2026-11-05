<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\ImageUploadRequest;
use App\Models\Establishment;
use App\Models\EstablishmentImage;
use App\Services\EstablishmentImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ImageController extends Controller
{
    public function __construct(private readonly EstablishmentImageService $images) {}

    public function index(Establishment $establishment): View
    {
        Gate::authorize('manage', $establishment);

        return view('owner.images', ['establishment' => $establishment->load('images')]);
    }

    public function store(ImageUploadRequest $request, Establishment $establishment): RedirectResponse
    {
        Gate::authorize('manage', $establishment);

        $files = $request->file('images');

        if ($this->images->wouldExceedLimit($establishment, count($files))) {
            return back()->with('error', 'An establishment can have at most '.config('autokonek.images.max_per_establishment').' images.');
        }

        $this->images->store($establishment, $files);

        return back()->with('success', 'Images uploaded.');
    }

    public function makePrimary(Establishment $establishment, EstablishmentImage $image): RedirectResponse
    {
        Gate::authorize('manage', $establishment);
        abort_unless($image->establishment_id === $establishment->id, 404);

        $this->images->makePrimary($establishment, $image);

        return back()->with('success', 'Main image updated.');
    }

    public function destroy(Establishment $establishment, EstablishmentImage $image): RedirectResponse
    {
        Gate::authorize('manage', $establishment);
        abort_unless($image->establishment_id === $establishment->id, 404);

        $this->images->delete($establishment, $image);

        return back()->with('success', 'Image removed.');
    }
}
