<div wire:click="toggle" style="cursor: pointer;">
  <div class="custom-control custom-switch d-inline-block">
    <input type="checkbox" class="custom-control-input" id="estadoSwitch{{ $model->id }}"
      {{ $model->{$field} ? 'checked' : '' }}>
    <label class="custom-control-label" for="estadoSwitch{{ $model->id }}"></label>
  </div>
</div>
