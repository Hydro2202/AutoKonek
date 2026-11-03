@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false])
@php $id = $attributes->get('id', $name); @endphp
<div>
    <label for="{{ $id }}" class="label">{{ $label }} @if ($required)<span class="text-danger" aria-hidden="true">*</span>@endif</label>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
           @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
           @required($required)
           {{ $attributes->except('id')->merge(['class' => 'input '.($errors->has($name) ? 'input-error' : '')]) }}>
    @if ($hint) <p class="hint">{{ $hint }}</p> @endif
    @error($name) <p class="error-text">{{ $message }}</p> @enderror
</div>
