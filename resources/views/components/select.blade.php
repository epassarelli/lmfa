@props(['name', 'label' => null, 'options' => [], 'selected' => '', 'required' => false])

<div class="form-group">
  <label for="{{ $name }}">
    {{ $label ?? ucfirst($name) }}
    @if ($required)
      <span class="text-danger">*</span>
    @endif
  </label>
  <select name="{{ $name }}" id="{{ $name }}" class="form-control {{ $attributes->get('class') }}"
    {{ $required ? 'required' : '' }} {{ $attributes->except('class') }}>
    @foreach ($options as $key => $option)
      <option value="{{ is_object($option) ? $option->id : $key }}"
        {{ collect(old($name, $selected))->contains(is_object($option) ? $option->id : $key) ? 'selected' : '' }}>
        {{ is_object($option) ? $option->interprete ?? ($option->name ?? ($option->nombre ?? $option->title)) : $option }}
      </option>
    @endforeach
  </select>

  @error($name)
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>
