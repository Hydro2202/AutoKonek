@php $e = $establishment ?? null; @endphp
<div class="card space-y-4 p-6">
    <x-input name="name" label="Establishment name" :value="$e?->name" required />
    <x-select name="category_id" label="Category" required>
        <option value="">Choose a category...</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((int) old('category_id', $e?->category_id) === $category->id)>{{ $category->name }}</option>
        @endforeach
    </x-select>
    <x-textarea name="description" label="Description" :value="$e?->description" rows="5" hint="At least 20 characters. Describe what you offer." required />

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2"><x-input name="address" label="Street address" :value="$e?->address" required /></div>
        <x-input name="city" label="City" :value="$e?->city" required />
        <x-input name="municipality" label="Municipality / area" :value="$e?->municipality" />
        <x-input name="contact_number" type="tel" label="Contact number" :value="$e?->contact_number" />
        <x-input name="contact_email" type="email" label="Contact email" :value="$e?->contact_email" />
    </div>
    <x-textarea name="opening_hours" label="Operating hours" :value="$e?->opening_hours" rows="3" hint="Example: Mon-Sat 8:00 AM - 6:00 PM" />

    <div>
        <label for="images" class="label">{{ $e ? 'Add more images' : 'Images' }} (optional)</label>
        <input id="images" type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" data-preview="#image-names"
               class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-navy file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-navy-700">
        <p id="image-names" class="hint">JPG, PNG or WebP, up to 2 MB each, max 5 per upload.</p>
        @error('images') <p class="error-text">{{ $message }}</p> @enderror
        @error('images.*') <p class="error-text">{{ $message }}</p> @enderror
    </div>
</div>
