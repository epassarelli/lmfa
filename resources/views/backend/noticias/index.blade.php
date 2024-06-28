@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content_header')
  <h1>Gestión de Noticias</h1>
@stop

@section('content')

  <div class="card">

    <div class="card-header text-right">
      @can('create noticia')
        <a href="{{ route('backend.noticias.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Crear Noticia</a>
      @endcan
    </div>

    <div class="card-body">
      <table id="noticias" class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Foto</th>
            <th>Título</th>
            <th>Interprete</th>
            <th>Vistas</th>
            <th>Acciones</th>
          </tr>
        </thead>

        <tbody>
          @foreach ($noticias as $noticia)
            <tr>
              <td>
                @if ($noticia->created_at)
                  {{ $noticia->created_at->format('d/m/Y H:i') }}
                @else
                  --- Sin fecha
                @endif
              </td>
              <td>
                <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="{{ $noticia->titulo }}"
                  class="img-fluid img-thumbnail" style="width: 100px; height: auto; cursor: pointer;" data-toggle="modal"
                  data-target="#imageModal" data-image="{{ asset('storage/noticias/' . $noticia->foto) }}">
              </td>
              <td>{{ $noticia->titulo }}</td>
              <td>
                @if ($noticia->interprete)
                  {{ $noticia->interprete->interprete }}
                @else
                  --- Sin intérprete
                @endif
              </td>
              <td>{{ $noticia->visitas }}</td>
              {{-- <td class="text-center"><livewire:toggle-button :model="$noticia" field="estado"
                  key="{{ $noticia->id }}" />
              </td> --}}
              <td class="text-right" style="white-space: nowrap;">
                @can('update', $noticia)
                  <a href="{{ route('backend.noticias.edit', $noticia) }}" class="btn btn-warning"><i
                      class="fas fa-edit"></i></a>
                @endcan

                @can('delete', $noticia)
                  <form id="delete-form-{{ $noticia->id }}" action="{{ route('backend.noticias.destroy', $noticia) }}"
                    method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $noticia->id }})">
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

  <!-- Modal -->
  <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="imageModalLabel">Imagen</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center">
          <img id="modalImage" src="" alt="Imagen" class="img-fluid">
        </div>
      </div>
    </div>
  </div>



@stop

@section('js')
  <script>
    $(document).ready(function() {

      $('#noticias').DataTable({
        "order": [
          [1, "desc"]
        ] // Ordenar por la columna de fecha de creación
      });

      // $('#noticias').DataTable({
      //   processing: true,
      //   serverSide: true,
      //   ajax: '{{ route('backend.noticias.index') }}',
      //   columns: [{
      //       data: 'titulo',
      //       name: 'titulo'
      //     },
      //     {
      //       data: 'interprete',
      //       name: 'interprete.interprete'
      //     },
      //     {
      //       data: 'user',
      //       name: 'user.name'
      //     },
      //     {
      //       data: 'action',
      //       name: 'action',
      //       orderable: false,
      //       searchable: false
      //     }
      //   ],
      //   order: [
      //     [0, 'desc']
      //   ]
      // });

      $('#imageModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var imageSrc = button.data('image');
        var modal = $(this);
        modal.find('#modalImage').attr('src', imageSrc);
      });


    });

    function confirmDelete(noticiaId) {
      confirmDialog('Esta acción no se puede deshacer', function() {
        document.getElementById(`delete-form-${noticiaId}`).submit();
      });
    }
  </script>
  @include('sweetalert::alert')
  @include('components.confirm_delete')
@stop
