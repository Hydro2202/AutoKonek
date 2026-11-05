@props(['name', 'label', 'value' => null, 'hint' => null, 'required' => false, 'rows' => 4])
<div>
    <label for="{{ $name }}" class="label">{{ $label }}@if ($required)<span class="text-danger-700" aria-hidden="true"> *</span>@endif</label>
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" @required($required)
              @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
              {{ $attributes->merge(['class' => 'input '.($errors->has($name) ? 'input-error' : '')]) }}>{{ old($name, $value) }}</textarea>
    @if ($hint) <p class="hint">{{ $hint }}</p> @endif
    @error($name) <p id="{{ $name }}-error" class="error-text">{{ $message }}</p> @enderror
</div>
