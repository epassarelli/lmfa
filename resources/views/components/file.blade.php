@props([
    'name',
    'label' => null,
    'value' => null,
    'path' => '',
    'required' => false,
])

<div class="form-group">
    <label for="{{ $name }}">
        {{ $label ?? ucfirst($name) }}
        @if ($required)<span class="text-danger">*</span>@endif
    </label>
    <input type="file" name="{{ $name }}" id="{{ $name }}" class="form-control" @if($required) required @endif>
    @if ($value)
        <div class="mt-2">
            <img src="{{ asset('storage/' . $path . '/' . $value) }}" alt="{{ $label ?? $name }}" style="max-height: 80px;">
        </div>
    @endif
    @error($name)
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
