<form action="{{ route('noticias.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="form-group">
        <label for="title">Título</label>
        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
            value="{{ old('title') }}" required>
        @error('title')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="slug">Slug</label>
        <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror"
            value="{{ old('slug') }}" required>
        @error('slug')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="article">Artículo</label>
        <textarea name="article" id="article" class="form-control @error('article') is-invalid @enderror" rows="10"
            required>{{ old('article') }}</textarea>
        @error('article')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="photo">Foto</label>
        <input type="file" name="photo" id="photo"
            class="form-control-file @error('photo') is-invalid @enderror" required>
        @error('photo')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="user_id">Autor</label>
        <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}</option>
            @endforeach
        </select>
        @error('user_id')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="status">Estado</label>
        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Borrador</option>
            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Publicado</option>
        </select>
        @error('status')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="interpretes">Intérpretes</label>
        <select name="interpretes[]" id="interpretes" class="form-control @error('interpretes') is-invalid @enderror"
            multiple>
            @foreach ($interpretes as $interprete)
                <option value="{{ $interprete->id }}"
                    {{ in_array($interprete->id, old('interpretes', [])) ? 'selected' : '' }}>
                    {{ $interprete->interprete }}
                </option>
            @endforeach

        </select>
        @error('interpretes')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
