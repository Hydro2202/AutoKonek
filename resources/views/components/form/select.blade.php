@props(['name', 'label', 'hint' => null, 'required' => false])
<div>
    <label for="{{ $name }}" class="label">{{ $label }}@if ($required)<span class="text-danger-700" aria-hidden="true"> *</span>@endif</label>
    <select id="{{ $name }}" name="{{ $name }}" @required($required)
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
            {{ $attributes->merge(['class' => 'input '.($errors->has($name) ? 'input-error' : '')]) }}>
        {{ $slot }}
    </select>
    @if ($hint) <p class="hint">{{ $hint }}</p> @endif
    @error($name) <p id="{{ $name }}-error" class="error-text">{{ $message }}</p> @enderror
</div>
