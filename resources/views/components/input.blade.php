@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => '',
    'required' => false,
    'min' => null,
    'max' => null,
])

<div class="form-group">
  <label for="{{ $name }}" class="form-label">
    {{ $label ?? ucfirst($name) }}
    @if ($required)
      <span class="text-danger">*</span>
    @endif
  </label>
  <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" class="form-control"
    value="{{ old($name, $value) }}" {{ $required ? 'required' : '' }}
    @if ($min) min="{{ $min }}" @endif
    @if ($max) max="{{ $max }}" @endif {{ $attributes }}>
  @error($name)
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>
