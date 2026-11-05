@props(['name', 'checked' => false])
<div>
    <label class="flex cursor-pointer items-start gap-2.5 text-sm text-ink">
        <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked)) class="mt-0.5 size-4 rounded" {{ $attributes }}>
        <span>{{ $slot }}</span>
    </label>
    @error($name) <p class="error-text">{{ $message }}</p> @enderror
</div>
