@props(['back' => null])
{{-- Botones estándar de los formularios del backoffice (los de Canciones): Guardar, Reset y Volver al listado. --}}
<button type="submit" class="btn btn-primary">
  <i class="fas fa-save"></i> Guardar
</button>
<button type="reset" class="btn btn-secondary">
  <i class="fas fa-undo"></i> Reset
</button>
<a href="{{ $back ?? url()->previous() }}" class="btn btn-secondary">
  <i class="fas fa-arrow-left"></i> Volver al listado
</a>
