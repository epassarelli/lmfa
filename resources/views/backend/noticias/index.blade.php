@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content_header')
  <span>Gestión de Noticias</span>
@stop
@php
  use Carbon\Carbon;
@endphp
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
            <th>Publicar</th>
            {{-- <th>Foto</th> --}}
            <th>Título</th>
            <th>Interprete</th>
            <th>Views</th>
            {{-- <th>Estado</th> --}}
            <th>Acciones</th>
          </tr>
        </thead>

        <tbody>
          @foreach ($noticias as $noticia)
            <tr>
              <td data-order="{{ $noticia->publicar ?? '' }}">
                {{ $noticia->publicar ? Carbon::parse($noticia->publicar)->format('d-m-Y') : '-' }}
              </td>
              <td class="position-relative">
                <span class="noticia-titulo" style="cursor: default;">
                  {{ $noticia->titulo }}
                  @if ($noticia->foto)
                    <div class="noticia-hover-img">
                      <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="Imagen de noticia">
                    </div>
                  @endif
                </span>
              </td>
              <td>
                @if ($noticia->interprete)
                  <strong>{{ $noticia->interprete->interprete }}</strong>

                  @if ($noticia->interpretes->isNotEmpty())
                    <br><small>{{ $noticia->interpretes->pluck('interprete')->join(', ') }}</small>
                  @endif
                @else
                  --- Sin intérpretes
                @endif
              </td>

              <td>{{ $noticia->visitas }}</td>

              <td class="text-right" style="white-space: nowrap;">

                {{-- <div class="d-inline-block align-middle">

                  <livewire:toggle-button :model="$noticia" field="estado" :key="$noticia->id" />

                </div> --}}

                @can('update', $noticia)
                  <a href="{{ route('backend.noticias.edit', $noticia) }}" class="btn btn-xs  btn-warning"><i
                      class="fas fa-edit"></i></a>
                @endcan

                @can('delete', $noticia)
                  <form id="delete-form-{{ $noticia->id }}" action="{{ route('backend.noticias.destroy', $noticia) }}"
                    method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelete({{ $noticia->id }})">
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

@section('css')
  <style>
    .noticia-hover-img {
      display: none;
      position: absolute;
      bottom: 20%;
      left: 50%;
      transform: translateX(-50%);
      margin-top: 8px;
      width: 150px;
      height: auto;
      border: 1px solid #ccc;
      background: #fff;
      padding: 4px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      z-index: 1000;
    }

    .noticia-titulo:hover .noticia-hover-img {
      display: block;
    }

    .noticia-hover-img img {
      max-width: 100%;
      height: auto;
      border-radius: 4px;
    }
  </style>
@endsection
