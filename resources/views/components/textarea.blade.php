@props([
    'name',
    'label' => null,
    'value' => '',
    'required' => false,
    'editor' => false,
    'editorProfile' => null,
])

<div class="form-group">
    <label for="{{ $name }}">
        {{ $label ?? ucfirst($name) }}
        @if ($required)<span class="text-danger">*</span>@endif
    </label>
    <textarea name="{{ $name }}" id="{{ $editor ? 'editor' : $name }}" class="form-control" rows="5" @if($required) required @endif @if($editorProfile) data-ckeditor-profile="{{ $editorProfile }}" @endif>{{ old($name, $value) }}</textarea>
    @error($name)
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
