@props(['name', 'label', 'hint' => null, 'required' => false])
<div>
    <label for="{{ $name }}" class="label">{{ $label }} @if ($required)<span class="text-danger" aria-hidden="true">*</span>@endif</label>
    <select id="{{ $name }}" name="{{ $name }}" @required($required)
            {{ $attributes->merge(['class' => 'input '.($errors->has($name) ? 'input-error' : '')]) }}>
        {{ $slot }}
    </select>
    @if ($hint) <p class="hint">{{ $hint }}</p> @endif
    @error($name) <p class="error-text">{{ $message }}</p> @enderror
</div>
