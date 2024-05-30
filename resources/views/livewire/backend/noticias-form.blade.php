<div>
  @if (session()->has('message'))
    <div class="alert alert-success mb-3">{{ session('message') }}</div>
  @endif

  <form wire:submit.prevent="save">
    <div class="mb-3">
      <label for="titulo" class="form-label">Título</label>
      <input wire:model="titulo" type="text" id="titulo" name="titulo" class="form-control">
      @error('titulo')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>

    <div class="mb-3">
      <label for="noticia" class="form-label">Noticia</label>
      <textarea wire:model="noticia" id="noticia" name="noticia" class="form-control"></textarea>
      @error('noticia')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>

    <div class="mb-3">
      <label for="foto" class="form-label">Foto</label>
      <input wire:model="foto" type="file" id="foto" name="foto" accept="image/*" class="form-control">
      @if ($foto and $accion !== 'edit')
        <img src="{{ $foto->temporaryUrl() }}" class="mt-2 img-fluid">
      @endif
      @error('foto')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>

    <div class="mb-3">
      <label for="interpretes" class="form-label">Intérpretes</label>
      <select wire:model="interpretes" id="interpretes" name="interpretes[]" multiple class="form-select">
        @foreach ($todos_interpretes as $interprete)
          <option value="{{ $interprete->id }}">{{ $interprete->interprete }}</option>
        @endforeach
      </select>
      @error('interpretes')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>

    <div class="d-flex justify-content-end">
      <button type="submit" class="btn btn-primary">
        Guardar
      </button>
    </div>
  </form>
</div>
