<div class="form-group">
  <label for="titulo">Título del Mito</label>
  <input type="text" name="titulo" id="titulo" class="form-control" value="{{ old('titulo', $mito->titulo ?? '') }}"
    required>
</div>

<div class="form-group">
  <label for="mito">Mito</label>
  <textarea name="mito" id="mito" class="form-control" rows="4" required>{{ old('mito', $mito->mito ?? '') }}</textarea>
</div>

<div class="form-group">
  <label for="foto">Foto</label>
  <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png">
  @if (isset($mito) && $mito->foto)
    <div class="mt-2">
      <img src="{{ asset('storage/' . $mito->foto) }}" alt="Foto de {{ $mito->titulo }}" style="max-height: 80px;">
    </div>
  @endif
</div>

@if (Auth::user()->hasRole('administrador'))
  <div class="form-group">
    <label for="slug">Slug</label>
    <input type="text" name="slug" id="slug" class="form-control"
      value="{{ old('slug', $mito->slug ?? '') }}" required>
  </div>
@endif

<div class="form-group">
  <label for="publicar">Fecha de Publicación</label>
  <input type="datetime-local" name="publicar" id="publicar" class="form-control"
    value="{{ old('publicar', isset($mito) ? $mito->publicar->format('Y-m-d\TH:i') : '') }}" required>
</div>
