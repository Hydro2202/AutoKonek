@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false])
@php $id = $attributes->get('id', $name); @endphp
<div>
    <label for="{{ $id }}" class="label">{{ $label }}@if ($required)<span class="text-danger-700" aria-hidden="true"> *</span>@endif</label>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
           @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
           @required($required)
           @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
           {{ $attributes->except('id')->merge(['class' => 'input '.($errors->has($name) ? 'input-error' : '')]) }}>
    @if ($hint) <p class="hint">{{ $hint }}</p> @endif
    @error($name) <p id="{{ $id }}-error" class="error-text">{{ $message }}</p> @enderror
</div>
