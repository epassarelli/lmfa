@section('title', 'Admin Gacetillas')

<div>
  {{-- The Master doesn't talk, he acts. --}}
  <div class="container-fluid">

    <div class="row mb-3">

      <div class="col-md-8 mt-4 col-6">
        <h3>Gacetillas de prensa</h3>
      </div>

      <div class="col-md-4 text-right mt-3 mt-md-4 col-6">
        <button wire:click="create" class="btn btn-success" data-toggle="modal" data-target="#roleModal"><i
            class="fas fa-plus-circle mr-2" style="color: white;"></i>Agregar</button>
      </div>

    </div>

    <div class="row">

      <div class="table-responsive">

        <table class="table table-hover table-striped table-bordered mt-3 datatable" id="myTable">
          <thead>
            <tr>
              <th scope="col">COD</th>
              <th scope="col">Imagen</th>
              <th scope="col">Titulo</th>
              <th scope="col">Interprete</th>
              <th scope="col">Usuario</th>
              <th scope="col" class="text-center" style="width: 15%">Acciones</th>
            </tr>
          </thead>

          <tbody>

            @foreach ($noticias as $noticia)
              <tr>
                <td class="align-middle">{{ $noticia->id }}</td>
                <td class="align-middle">
                  <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="" width="80">
                </td>
                <td class="align-middle">{{ $noticia->titulo }}</td>
                <td class="align-middle">{{ $noticia->interprete->interprete }}
                </td>
                <td class="align-middle">{{ $noticia->user->name }}</td>
                <td class="align-middle">
                  <div class="d-flex flex-md-row gap-1 justify-content-evenly">
                    <div class="m-1 mt-3">
                      <livewire:toggle-button :model="$noticia" field="estado" key="{{ $noticia->id }}" />
                    </div>
                    <button wire:click="edit({{ $noticia->id }})" data-toggle="modal" data-target="#roleModal"
                      title="Editar"><i class="fa fa-edit" style="color: orange "></i></button>
                    <button wire:click="$emit('alertDelete',{{ $noticia->id }})" title="Eliminar"><i
                        class="fas fa-trash-alt" aria-hidden="true" style="color: red "></i></button>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

      </div>

    </div>

  </div>


  @if ($modal)
    @include('livewire.backend.noticias-form')
  @endif

  <script>
    $(document).ready(function() {
      $('#myTable').DataTable({
        "stateSave": true, // Habilita guardar el estado
        "language": {
          "lengthMenu": "Mostrar _MENU_ elementos por página",
          "zeroRecords": "No se encontraron resultados",
          "info": "Mostrando página _PAGE_ de _PAGES_",
          "infoEmpty": "No hay registros disponibles",
          "infoFiltered": "(filtrados de _MAX_ registros totales)",
          "search": "Buscar:",
          "paginate": {
            "first": "Primero",
            "last": "Último",
            "next": "Siguiente",
            "previous": "Anterior"
          },
        }
      });
    });
  </script>

  @push('scripts')
    <script src="{{ asset('vendor/ckeditor5/ckeditor.js') }}"></script>
    <script>
      ClassicEditor.create(document.querySelector('#noticia'))
        .then(editor => {
          editor.model.document.on('change:data', () => {
            Livewire.emit('noticiaUpdated', editor.getData());
          });
        })
        .catch(error => {
          console.error(error);
        });
    </script>
  @endpush


</div>
