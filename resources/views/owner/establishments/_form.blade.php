{{-- Listing form shared by "create" and "edit". Sections have their explanation on the left. --}}
@php
    $e = $establishment ?? null;
    $regionId = $location['regionId'];
    // Regions such as Metro Manila have no provinces, so their cities are listed directly.
    $hasProvinces = $location['provinces']->isNotEmpty();
@endphp

<div class="divide-y divide-line rounded-lg border border-line bg-white">
    <section class="grid gap-6 p-6 lg:grid-cols-[13rem_1fr]">
        <div>
            <h2 class="text-2xl">Basics</h2>
            <p class="mt-1 text-sm text-muted">What the place is and what it offers.</p>
        </div>
        <div class="space-y-5">
            <x-form.input name="name" label="Establishment name" :value="$e?->name" required />
            <x-form.select name="category_id" label="Category" required>
                <option value="">Choose a category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('category_id', $e?->category_id) === $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-form.select>
            <x-form.textarea name="description" label="Description" :value="$e?->description" rows="5" hint="At least 20 characters. Say what you offer and what makes you different." required />
        </div>
    </section>

    <section class="grid gap-6 p-6 lg:grid-cols-[13rem_1fr]">
        <div>
            <h2 class="text-2xl">Location</h2>
            <p class="mt-1 text-sm text-muted">Choose from the official list of Philippine places. Drivers find you by city or province.</p>
        </div>
        <div data-location-picker data-provinces-url="{{ route('places.provinces') }}" data-cities-url="{{ route('places.cities') }}" class="space-y-5">
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="region_id" class="label">Region <span class="text-danger-700" aria-hidden="true">*</span></label>
                    <select id="region_id" name="region_id" data-loc="region" class="input" required>
                        <option value="">Choose a region</option>
                        @foreach ($location['regions'] as $region)
                            <option value="{{ $region->id }}" @selected($regionId === $region->id)>{{ $region->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div data-loc-wrap="province" @hidden($regionId && ! $hasProvinces)>
                    <label for="province_id" class="label">Province <span class="text-danger-700" aria-hidden="true">*</span></label>
                    <select id="province_id" name="province_id" data-loc="province" class="input" @disabled(! $hasProvinces)>
                        <option value="">Choose a province</option>
                        @foreach ($location['provinces'] as $province)
                            <option value="{{ $province->id }}" @selected($location['provinceId'] === $province->id)>{{ $province->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="city_id" class="label">City or municipality <span class="text-danger-700" aria-hidden="true">*</span></label>
                    <select id="city_id" name="city_id" data-loc="city" required @disabled($location['cities']->isEmpty())
                            @class(['input', 'input-error' => $errors->has('city_id')])>
                        <option value="">Choose a city or municipality</option>
                        @foreach ($location['cities'] as $city)
                            <option value="{{ $city->id }}" @selected($location['cityId'] === $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                    @error('city_id') <p class="error-text">{{ $message }}</p> @enderror
                </div>
            </div>
            <p data-loc-note class="error-text" role="status"></p>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input name="barangay" label="Barangay" :value="$e?->barangay" hint="Optional." />
                <div class="sm:col-span-2"><x-form.input name="address" label="Street address" :value="$e?->address" hint="Street, building or landmark, for example: Quezon Avenue, beside the public market." required /></div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 p-6 lg:grid-cols-[13rem_1fr]">
        <div>
            <h2 class="text-2xl">Map pin</h2>
            <p class="mt-1 text-sm text-muted">Optional. A pin makes the Get directions button take drivers to your exact spot.</p>
        </div>
        <div class="space-y-4">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input name="latitude" type="text" inputmode="decimal" label="Latitude" :value="$e?->latitude" placeholder="13.937600" />
                <x-form.input name="longitude" type="text" inputmode="decimal" label="Longitude" :value="$e?->longitude" placeholder="121.619100" />
            </div>
            <div>
                <button type="button" data-use-location data-no-loading class="btn-outline"><x-ui.icon name="crosshair" class="size-4" /> Use my current location</button>
                <p data-geo-message class="hint" role="status"></p>
                <p class="hint">Not at the shop right now? In Google Maps, right-click the spot and click the numbers to copy the latitude and longitude. Leave both empty to let maps search your address.</p>
            </div>
        </div>
    </section>

    <section class="grid gap-6 p-6 lg:grid-cols-[13rem_1fr]">
        <div>
            <h2 class="text-2xl">Contact and hours</h2>
            <p class="mt-1 text-sm text-muted">Optional, but listings with a phone number get more visits.</p>
        </div>
        <div class="space-y-5">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input name="contact_number" type="tel" label="Phone number" :value="$e?->contact_number" placeholder="0917 123 4567" />
                <x-form.input name="contact_email" type="email" label="Email" :value="$e?->contact_email" />
            </div>
            <x-form.textarea name="opening_hours" label="Opening hours" :value="$e?->opening_hours" rows="3" hint="For example: Mon to Sat, 8:00 AM to 6:00 PM" />
        </div>
    </section>

    <section class="grid gap-6 p-6 lg:grid-cols-[13rem_1fr]">
        <div>
            <h2 class="text-2xl">Photos</h2>
            <p class="mt-1 text-sm text-muted">The first photo becomes the main image.</p>
        </div>
        <div>
            <label for="images" class="label">{{ $e ? 'Add more photos' : 'Upload photos' }} (optional)</label>
            <input id="images" type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" data-preview="#image-names" class="file-input">
            <p id="image-names" class="hint">JPG, PNG or WebP, up to 2 MB each, up to 5 at a time.</p>
            @error('images') <p class="error-text">{{ $message }}</p> @enderror
            @error('images.*') <p class="error-text">{{ $message }}</p> @enderror
        </div>
    </section>
</div>
