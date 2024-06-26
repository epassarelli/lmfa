<div class="form-group">
  <label for="titulo">Título de la Comida</label>
  <input type="text" name="titulo" id="titulo" class="form-control" value="{{ old('titulo', $comida->titulo ?? '') }}"
    required>
</div>

<div class="form-group">
  <label for="receta">Receta</label>
  <textarea name="receta" id="receta" class="form-control" rows="4" required>{{ old('receta', $comida->receta ?? '') }}</textarea>
</div>

<div class="form-group">
  <label for="foto">Foto</label>
  <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png">
  @if (isset($comida) && $comida->foto)
    <div class="mt-2">
      <img src="{{ asset('storage/' . $comida->foto) }}" alt="Foto de {{ $comida->titulo }}" style="max-height: 80px;">
    </div>
  @endif
</div>

@if (Auth::user()->hasRole('administrador'))
  <div class="form-group">
    <label for="slug">Slug</label>
    <input type="text" name="slug" id="slug" class="form-control"
      value="{{ old('slug', $comida->slug ?? '') }}" required>
  </div>
@endif

<div class="form-group">
  <label for="publicar">Fecha de Publicación</label>
  <input type="datetime-local" name="publicar" id="publicar" class="form-control"
    value="{{ old('publicar', isset($comida) ? $comida->publicar->format('Y-m-d\TH:i') : '') }}" required>
</div>
