@extends('adminlte::page')

@section('title', 'Eventos')

@section('content_header')
  <h1><i class="fas fa-calendar-alt mr-2"></i>Gestion de Eventos</h1>
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

  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title">Listado de Eventos</h3>
      <div class="card-tools">
        <a href="{{ route('backend.events.create') }}" class="btn btn-success">
          <i class="fas fa-plus mr-1"></i> Nuevo Evento
        </a>
      </div>
    </div>
    <div class="card-body">
      <table id="events-table" class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Titulo</th>
            <th>Interprete</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($events as $event)
            <tr>
              <td data-order="{{ $event->start_at }}">
                {{ $event->start_at ? $event->start_at->format('d-m-Y') : '-' }}
              </td>
              <td><strong>{{ $event->title }}</strong></td>
              <td>{{ $event->interpretes->first()->interprete ?? '-' }}</td>
              <td>
                @switch($event->editorial_status)
                    @case('published') <span class="badge badge-success">Publicado</span> @break
                    @case('draft') <span class="badge badge-secondary">Borrador</span> @break
                    @case('pending_review') <span class="badge badge-warning">En revision</span> @break
                    @default <span class="badge badge-light">{{ $event->editorial_status }}</span>
                @endswitch
              </td>
              <td class="text-right" style="white-space: nowrap;">
                <a href="{{ route('backend.events.edit', $event) }}" class="btn btn-sm btn-warning">
                  <i class="fas fa-edit"></i>
                </a>
                @can('delete', $event)
                  <form action="{{ route('backend.events.destroy', $event) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"
                      onclick="return confirm('Estas seguro de eliminar este evento?')">
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
      $('#events-table').DataTable({
        "order": [[ 0, "desc" ]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Spanish.json"
        }
      });
    });
  </script>
@stop
