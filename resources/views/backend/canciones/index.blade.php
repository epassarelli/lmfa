@extends('adminlte::page')

@section('title', 'Canciones')

@section('content_header')
  <h1>Gestion de Canciones</h1>
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
      <h3 class="card-title">Listado de Canciones</h3>
      <div class="card-tools">
        <a href="{{ route('backend.canciones.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i> Crear Cancion</a>
      </div>
    </div>

    <div class="card-body">
      <form id="canciones-filters" class="mb-3">
        <div class="rounded border bg-light p-3">
        <div class="row align-items-end">
          <div class="col-md-5">
            <label for="songs-search" class="small text-muted mb-1">Buscar</label>
            <input
              id="songs-search"
              type="text"
              class="form-control"
              placeholder="Buscar por cancion, interprete o visitas..."
              value="{{ request('search') }}"
            >
          </div>

          <div class="col-md-3">
            <label for="songs-status" class="small text-muted mb-1">Estado</label>
            <select id="songs-status" class="form-control">
              <option value="all" @selected(($status ?? 'all') === 'all')>Todos</option>
              <option value="active" @selected(($status ?? 'all') === 'active')>Activas</option>
              <option value="pending" @selected(($status ?? 'all') === 'pending')>Pendientes</option>
            </select>
          </div>

          <div class="col-md-4">
            <div class="d-flex">
              <button type="button" id="songs-apply" class="btn btn-primary mr-2">
                <i class="fas fa-search mr-1"></i> Aplicar
              </button>
              <button type="button" id="songs-clear" class="btn btn-outline-secondary">
                Limpiar
              </button>
            </div>
          </div>
        </div>
        </div>
      </form>

      <div class="table-responsive">
        <table id="canciones-table" class="table table-hover mb-0 w-100">
          <thead class="thead-light">
            <tr>
              <th>Cancion</th>
              <th>Interprete</th>
              <th>Visitas</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
@stop

@section('js')
  <script>
    $(function() {
      const params = new URLSearchParams(window.location.search);
      const initialSearch = params.get('search') || $('#songs-search').val() || '';
      const initialStatus = params.get('status') || $('#songs-status').val() || 'all';
      const initialOrderColumn = Number(params.get('order_column') || 0);
      const initialOrderDirection = params.get('order_direction') || 'asc';

      $('#songs-search').val(initialSearch);
      $('#songs-status').val(initialStatus);

      const table = $('#canciones-table').DataTable({
        processing: true,
        serverSide: true,
        searchDelay: 300,
        ajax: {
          url: '{{ route('backend.canciones.get') }}',
          data: function(d) {
            d.status = $('#songs-status').val();
          }
        },
        order: [[initialOrderColumn, initialOrderDirection]],
        columns: [
          { data: 'cancion', name: 'cancion' },
          { data: 'interprete', name: 'interprete' },
          { data: 'visitas', name: 'visitas' },
          { data: 'estado', name: 'estado' },
          { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        language: {
          search: 'Buscar:',
          zeroRecords: 'No se encontraron canciones',
          info: 'Mostrando _START_ a _END_ de _TOTAL_ canciones',
          infoEmpty: 'Mostrando 0 a 0 de 0 canciones',
          infoFiltered: '(filtradas de _MAX_ canciones)',
          lengthMenu: 'Mostrar _MENU_ canciones',
          paginate: {
            first: 'Primera',
            last: 'Ultima',
            next: 'Siguiente',
            previous: 'Anterior'
          },
          processing: 'Procesando...'
        },
        initComplete: function() {
          if (initialSearch) {
            table.search(initialSearch).draw();
          }
        }
      });

      function syncSongsUrl() {
        const currentSearch = $('#songs-search').val().trim();
        const currentStatus = $('#songs-status').val();
        const currentOrder = table.order()[0] || [0, 'asc'];
        const url = new URL(window.location.href);

        if (currentSearch) {
          url.searchParams.set('search', currentSearch);
        } else {
          url.searchParams.delete('search');
        }

        if (currentStatus && currentStatus !== 'all') {
          url.searchParams.set('status', currentStatus);
        } else {
          url.searchParams.delete('status');
        }

        url.searchParams.set('order_column', currentOrder[0]);
        url.searchParams.set('order_direction', currentOrder[1]);
        window.history.replaceState({}, '', url.toString());
      }

      function applySongsFilters() {
        table.search($('#songs-search').val().trim()).draw();
      }

      $('#songs-apply').on('click', function() {
        applySongsFilters();
      });

      $('#songs-search').on('keydown', function(event) {
        if (event.key === 'Enter') {
          event.preventDefault();
          applySongsFilters();
        }
      });

      $('#songs-status').on('change', function() {
        table.draw();
      });

      $('#songs-clear').on('click', function() {
        $('#songs-search').val('');
        $('#songs-status').val('all');
        table.search('').order([[0, 'asc']]).draw();
      });

      table.on('draw.dt order.dt', function() {
        syncSongsUrl();
      });
    });
  </script>
@stop
