<form action="{{ route('noticias.update', $noticia->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="title">Título</label>
        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
            value="{{ old('title', $noticia->title) }}" required>
    </div>


    <div class="form-group">
        <label for="interpretes">Intérpretes</label>
        <select name="interpretes[]" id="interpretes" class="form-control @error('interpretes') is-invalid @enderror"
            multiple>
            @foreach ($interpretes as $interprete)
                <option value="{{ $interprete->id }}" @if ($noticia->interpretes->contains($interprete->id)) selected @endif>
                    {{ $interprete->name }}</option>
            @endforeach
        </select>
        @error('interpretes')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="biography">Biografía</label>
        <textarea name="biography" id="biography" class="form-control ckeditor @error('biography') is-invalid @enderror"
            rows="10">{{ old('biography', $noticia->biography->content ?? '') }}</textarea>
        @error('biography')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
