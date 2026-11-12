@extends('adminlte::page')

@section('title', 'Spotify: '.$interprete->interprete)

@section('content_header')
  <h1><i class="fab fa-spotify text-success mr-1"></i> Spotify: {{ $interprete->interprete }}</h1>
@endsection

@section('content')
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if (session('import_errors'))
    <div class="alert alert-warning">
      <strong>Avisos de la importación:</strong>
      <ul class="mb-0">
        @foreach (session('import_errors') as $importError)
          <li>{{ $importError }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @unless ($configured)
    <div class="alert alert-warning">Spotify no está configurado en este entorno (faltan las credenciales en el <code>.env</code>).</div>
  @endunless
  @if ($error)
    <div class="alert alert-danger">{{ $error }}</div>
  @endif

  @if (filled($interprete->spotify_artist_id))
    <div class="card card-outline card-success shadow-sm">
      <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Artista vinculado</h3>
        <div class="card-tools ml-auto">
          <a href="https://open.spotify.com/artist/{{ $interprete->spotify_artist_id }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success"><i class="fas fa-external-link-alt mr-1"></i> Ver en Spotify</a>
          <form action="{{ route('backend.interpretes.spotify.unlink', $interprete) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Quitar el vínculo con Spotify? Los discos ya cargados no cambian.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-unlink mr-1"></i> Desvincular</button>
          </form>
        </div>
      </div>
      <div class="card-body py-2">
        <code>{{ $interprete->spotify_artist_id }}</code>
        @if ($interprete->spotify_albums_checked_at)
          <span class="text-muted ml-2">Última revisión de discos: {{ $interprete->spotify_albums_checked_at->format('d/m/Y H:i') }}</span>
        @endif
      </div>
    </div>

    @if ($preview)
      <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
          <h3 class="card-title mb-0">Discos en Spotify que el portal todavía no tiene</h3>
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">
            Spotify devolvió {{ $preview['total_spotify'] }} disco(s); {{ $preview['already_in_portal'] }} ya están en el portal.
            Los que elijas se crean como <strong>inactivos</strong> con portada y reproductor, y quedan en Moderación para que los revises.
            Los marcados como <em>posible duplicado</em> vienen destildados.
          </p>

          @if (count($preview['items']) === 0)
            <div class="alert alert-info mb-0">No hay discos nuevos para importar.</div>
          @else
            <form action="{{ route('backend.interpretes.spotify.import', $interprete) }}" method="POST">
              @csrf
              <div class="table-responsive">
                <table class="table table-hover table-sm align-middle">
                  <thead class="thead-light">
                    <tr>
                      <th style="width:40px"><input type="checkbox" id="spotify-check-all" aria-label="Marcar todos"></th>
                      <th style="width:70px">Portada</th>
                      <th>Disco</th>
                      <th>Año</th>
                      <th>Tipo</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($preview['items'] as $item)
                      <tr>
                        <td><input type="checkbox" class="spotify-album-check" name="albums[]" value="{{ $item['id'] }}" aria-label="Importar {{ $item['name'] }}" @checked(! $item['possible_duplicate'])></td>
                        <td>@if ($item['thumb'])<img src="{{ $item['thumb'] }}" alt="Portada de {{ $item['name'] }}" width="56" height="56" loading="lazy" class="img-thumbnail p-0">@endif</td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['year'] }}</td>
                        <td>{{ $item['type_label'] }}</td>
                        <td>@if ($item['possible_duplicate'])<span class="badge badge-warning">Posible duplicado</span>@endif</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              @error('albums')<div class="text-danger mb-2">{{ $message }}</div>@enderror
              <button type="submit" class="btn btn-primary"><i class="fas fa-download mr-1"></i> Crear discos seleccionados (inactivos)</button>
              <a href="{{ route('backend.interpretes.edit', $interprete) }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver al artista</a>
            </form>
          @endif
        </div>
      </div>
    @endif
  @else
    <div class="card card-outline card-primary shadow-sm">
      <div class="card-header"><h3 class="card-title mb-0">Elegí el artista correcto en Spotify</h3></div>
      <div class="card-body">
        <p class="text-muted">Este artista todavía no está vinculado. Al vincularlo, el portal podrá traer sus discos sin buscar por nombre cada vez.</p>

        @forelse ($candidates as $candidate)
          <div class="d-flex align-items-center border-bottom py-2">
            <div style="width:56px" class="mr-3 flex-shrink-0">
              @if ($candidate['image'])<img src="{{ $candidate['image'] }}" alt="" width="56" height="56" loading="lazy" class="img-thumbnail p-0 rounded-circle">@endif
            </div>
            <div class="flex-grow-1">
              <strong>{{ $candidate['name'] }}</strong>
              @if ($candidate['exact'])<span class="badge badge-success ml-1">Nombre exacto</span>@endif
              @if ($loop->first && $candidate['exact'] && collect($candidates)->where('exact', true)->count() > 1)<span class="badge badge-info ml-1">Sugerido: más seguidores</span>@endif
              <div class="text-muted small">
                {{ number_format($candidate['followers'], 0, ',', '.') }} seguidores
                @if ($candidate['genres']) · {{ implode(', ', $candidate['genres']) }}@endif
                · <a href="{{ $candidate['url'] }}" target="_blank" rel="noopener">abrir en Spotify</a>
              </div>
            </div>
            @if ($candidate['used_by'])
              <span class="text-muted small">Ya vinculado a «{{ $candidate['used_by'] }}»</span>
            @else
              <form action="{{ route('backend.interpretes.spotify.link', $interprete) }}" method="POST">
                @csrf
                <input type="hidden" name="spotify" value="{{ $candidate['id'] }}">
                <button type="submit" class="btn btn-sm btn-success">Vincular</button>
              </form>
            @endif
          </div>
        @empty
          @if ($configured && ! $error)
            <div class="alert alert-info">Spotify no devolvió candidatos con ese nombre. Podés pegar la URL del artista abajo.</div>
          @endif
        @endforelse
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header"><h3 class="card-title mb-0">O pegá el enlace del artista</h3></div>
      <div class="card-body">
        <form action="{{ route('backend.interpretes.spotify.link', $interprete) }}" method="POST" class="form-inline">
          @csrf
          <input type="text" name="spotify" class="form-control mr-2 flex-grow-1" style="min-width:280px" placeholder="https://open.spotify.com/artist/…" value="{{ old('spotify') }}" required>
          <button type="submit" class="btn btn-success">Vincular</button>
        </form>
        @error('spotify')<div class="text-danger mt-2">{{ $message }}</div>@enderror
        <a href="{{ route('backend.interpretes.edit', $interprete) }}" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left mr-1"></i> Volver al artista</a>
      </div>
    </div>
  @endif
@endsection

@section('js')
  <script>
    document.getElementById('spotify-check-all')?.addEventListener('change', function () {
      document.querySelectorAll('.spotify-album-check').forEach(function (box) { box.checked = this.checked; }, this);
    });
  </script>
@endsection
