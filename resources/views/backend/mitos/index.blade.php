@extends('adminlte::page')

@section('title', 'Mitos')

@section('content_header')
  <h1>Gestion de Mitos</h1>
@stop

@section('content')
  @if (session('success'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Exito',
        text: '{{ session('success') }}'
      });
    </script>
  @endif
  <div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
      <h3 class="card-title">Listado de Mitos</h3>
      <div class="card-tools">
        <a href="{{ route('backend.mitos.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i> Crear Mito</a>
      </div>
    </div>
    <div class="card-body">
      <x-admin-listing-toolbar
        :action="route('backend.mitos.index')"
        search-placeholder="Buscar por titulo, contenido, visitas o ID"
        :sort-options="[
          'titulo' => 'Titulo',
          'visitas' => 'Visitas',
          'estado' => 'Estado',
          'id' => 'ID',
        ]"
      />

      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th>Titulo</th>
              <th>Foto</th>
              <th>Caracteres</th>
              <th>Visitas</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($mitos as $mito)
              <tr>
                <td>{{ $mito->titulo }}</td>
                <td style="width:60px;padding:4px">
                  @if ($mito->images->isNotEmpty())
                    <x-optimized-image :image="$mito->images->first()" variant="card" :minimal="true" style="width:50px;height:50px;object-fit:cover;display:block" class="rounded-circle" />
                  @elseif($mito->foto)
                    <img src="{{ asset('storage/' . $mito->foto) }}" alt="{{ $mito->titulo }}"
                      style="width:50px;height:50px;object-fit:cover;display:block" class="rounded-circle">
                  @endif
                </td>
                <td>{{ strlen(strip_tags($mito->mito)) }}</td>
                <td>{{ $mito->visitas }}</td>
                <td>
                  @if($mito->estado == 1)
                    <span class="badge badge-success">Activo</span>
                  @else
                    <span class="badge badge-secondary">Inactivo</span>
                  @endif
                </td>
                <td class="text-right" style="white-space: nowrap;">
                  <a href="{{ route('backend.mitos.edit', $mito) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i>
                  </a>
                  @can('delete', $mito)
                    <form action="{{ route('backend.mitos.destroy', $mito) }}" method="POST" style="display:inline-block;">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger" onclick="return confirm('Estas seguro de eliminar este mito?')">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  @endcan
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No hay mitos para los filtros seleccionados.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="card-footer bg-white px-0 pb-0 pt-3">
        {{ $mitos->links() }}
      </div>
    </div>
  </div>
@stop
