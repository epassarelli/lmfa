@extends('adminlte::page')

@section('title', 'Comidas')

@section('content_header')
  <h1>Gestión de Comidas</h1>
@stop

@section('content')
  @if (session('success'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: '{{ session('success') }}'
      });
    </script>
  @endif
  <div class="card">
    <div class="card-header text-right">
      <a href="{{ route('backend.comidas.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear Comida</a>
    </div>
    <div class="card-body">
      <table id="comidas-table" class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>Título</th>
            <th>Foto</th>
            <th>Caract's</th>
            <th>Visitas</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($comidas as $comida)
            <tr>
              <td>{{ $comida->titulo }}</td>
              <td style="width:60px;padding:4px">
                @if ($comida->images->isNotEmpty())
                  <x-optimized-image :image="$comida->images->first()" variant="card" :minimal="true" style="width:50px;height:50px;object-fit:cover;display:block" class="rounded-circle" />
                @elseif($comida->foto)
                  <img src="{{ asset('storage/' . $comida->foto) }}" alt="{{ $comida->titulo }}"
                    style="width:50px;height:50px;object-fit:cover;display:block" class="rounded-circle">
                @endif
              </td>
              <td>{{ strlen(strip_tags($comida->receta)) }}</td>
              {{-- <td>{{ $comida->user->name }}</td> --}}
              <td>{{ $comida->visitas }}</td>
              <td>{{ $comida->estado == 1 ? 'Activo' : 'Inactivo' }}</td>
              <td class="text-right" style="white-space: nowrap;">
                <a href="{{ route('backend.comidas.edit', $comida) }}" class="btn btn-warning">
                  <i class="fas fa-edit"></i>
                </a>
                @can('delete', $comida)
                  <form action="{{ route('backend.comidas.destroy', $comida) }}" method="POST"
                    style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                      onclick="return confirm('¿Estás seguro de eliminar esta comida?')">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </form>
                @endcan
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@stop

@section('js')
  <script>
    $(function() {
      $('#comidas-table').DataTable();
    });
  </script>
@stop
