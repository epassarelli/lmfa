@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content_header')
  <h1>Gestion de Interpretes</h1>
@stop

@section('content')
  <x-backend.list-help section="interprete" />

  <div class="card card-outline card-primary shadow-sm">

    <div class="card-header">
      <h3 class="card-title">Listado de Interpretes</h3>
      <div class="card-tools">
        <a href="{{ route('backend.interpretes.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i> Crear Interprete</a>
      </div>
    </div>

    <div class="card-body">

      @if ($message = Session::get('success'))
        <div class="alert alert-success">
          <p>{{ $message }}</p>
        </div>
      @endif

      <x-admin-listing-toolbar
        :action="route('backend.interpretes.index')"
        search-placeholder="Buscar por interprete o correo"
        :sort-options="[
          'noticias_count' => 'Noticias',
          'interprete' => 'Interprete',
          'correo' => 'Correo',
          'visitas' => 'Visitas',
          'shows_count' => 'Shows',
          'discos_count' => 'Discos',
          'canciones_count' => 'Canciones',
          'id' => 'ID',
        ]"
      />

      <div class="d-flex flex-wrap align-items-center mb-3">
        <span class="mr-2"><i class="fab fa-spotify text-success"></i> Spotify:</span>
        <a href="{{ route('backend.interpretes.index', request()->except('spotify', 'page')) }}" class="btn btn-xs mr-1 {{ request('spotify') ? 'btn-outline-secondary' : 'btn-secondary' }}">Todos</a>
        <a href="{{ route('backend.interpretes.index', array_merge(request()->except('page'), ['spotify' => 'sin_vincular'])) }}" class="btn btn-xs mr-1 {{ request('spotify') === 'sin_vincular' ? 'btn-secondary' : 'btn-outline-secondary' }}">Sin vincular</a>
        <a href="{{ route('backend.interpretes.index', array_merge(request()->except('page'), ['spotify' => 'vinculados'])) }}" class="btn btn-xs mr-3 {{ request('spotify') === 'vinculados' ? 'btn-secondary' : 'btn-outline-secondary' }}">Vinculados</a>
        @role('administrador')
          <form action="{{ route('backend.interpretes.spotify.bulk-link') }}" method="POST" class="d-inline" onsubmit="return confirm('Se buscarán en Spotify hasta 40 artistas activos sin vincular y se vincularán solo los de coincidencia exacta y única. ¿Continuar?');">
            @csrf
            <button type="submit" class="btn btn-xs btn-outline-success"><i class="fas fa-link mr-1"></i> Vincular pendientes (lote de 40)</button>
          </form>
        @endrole
      </div>

      <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="thead-light">
          <th>ID</th>
          <th>Foto</th>
          <th>Interprete</th>
          <th>Correo</th>
          <th>Chars</th>
          <th>Views</th>
          <th>News</th>
          <th>Shows</th>
          <th>Discos</th>
          <th>Songs</th>
          <th>Acciones</th>
        </thead>
        <tbody>
          @foreach ($interpretes as $interprete)
            <tr>
              <td>{{ $interprete->id }}</td>
              <td style="width:60px;padding:4px">
                @if ($interprete->images->isNotEmpty())
                  <x-optimized-image :image="$interprete->images->first()" variant="card" :minimal="true" style="width:50px;height:50px;object-fit:cover;display:block" class="rounded-circle" />
                @elseif($interprete->foto)
                  <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" alt="{{ $interprete->interprete }}"
                    style="width:50px;height:50px;object-fit:cover;display:block" class="rounded-circle">
                @endif
              </td>
              <td>{{ $interprete->interprete }}</td>
              <td>{{ $interprete->correo }}</td>
              <td>{{ strlen(strip_tags($interprete->biografia)) }}</td>
              <td>{{ $interprete->visitas }}</td>
              <td>{{ $interprete->noticias_count }}</td>
              <td>{{ $interprete->shows_count }}</td>
              <td>{{ $interprete->discos_count }}</td>
              <td>{{ $interprete->canciones_count }}</td>
              <td class="text-right" style="white-space: nowrap;">
                <div class="action-icons">
                  @can('update', $interprete)
                  <a class="btn {{ filled($interprete->spotify_artist_id) ? 'btn-success' : 'btn-outline-secondary' }}" href="{{ route('backend.interpretes.spotify', $interprete->id) }}" title="{{ filled($interprete->spotify_artist_id) ? 'Spotify: traer discos' : 'Vincular con Spotify' }}">
                    <i class="fab fa-spotify"></i>
                  </a>
                  @endcan

                  @can('update', $interprete)
                  <a class="btn btn-warning" href="{{ route('backend.interpretes.edit', $interprete->id) }}">
                    <i class="fas fa-edit"></i>
                  </a>
                  @endcan

                  @can('delete', $interprete)
                  <form action="{{ route('backend.interpretes.destroy', $interprete->id) }}" method="POST"
                    style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Estas seguro?')">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </form>
                  @endcan
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
      </div>

      <div class="card-footer bg-white px-0 pb-0 pt-3">
        {{ $interpretes->links() }}
      </div>

    </div>
  </div>
@stop
