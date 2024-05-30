<div>
  <div class="container py-8">
    <div class="max-w-7xl mx-auto">
      <div class="card shadow-sm p-4">

        <h2 class="fw-semibold fs-4 text-dark">
          {{ __('Gestión de gacetillas de prensa') }}
        </h2>

        @if ($modal)
          @include('livewire.backend.noticias-form')
        @else
          <div class="row mb-3">
            <div class="col-sm-4"></div>
            <div class="col-sm-4 py-3">
              {{-- <input type="text" placeholder="Texto a buscar" wire:model="search" class="form-control" /> --}}
            </div>
            <div class="col-sm-4 text-end">
              <button wire:click="create()" class="btn btn-success btn-sm">
                + Nueva gacetilla
              </button>
            </div>
          </div>

          <table class="table table-striped">
            <thead>
              <tr>
                <th class="cursor-pointer" wire:click="order('id')">Id
                  @if ($sort == 'id')
                    @if ($order == 'asc')
                      <i class="fas fa-sort-alpha-up-alt float-end mt-1"></i>
                    @else
                      <i class="fas fa-sort-alpha-down-alt float-end mt-1"></i>
                    @endif
                  @else
                    <i class="fas fa-sort float-end mt-1"></i>
                  @endif
                </th>
                <th class="cursor-pointer">Imagen</th>
                <th class="cursor-pointer" wire:click="order('titulo')">Titulo
                  @if ($sort == 'titulo')
                    @if ($order == 'asc')
                      <i class="fas fa-sort-alpha-up-alt float-end mt-1"></i>
                    @else
                      <i class="fas fa-sort-alpha-down-alt float-end mt-1"></i>
                    @endif
                  @else
                    <i class="fas fa-sort float-end mt-1"></i>
                  @endif
                </th>
                <th class="cursor-pointer" wire:click="order('publicar')">Publicar
                  @if ($sort == 'publicar')
                    @if ($order == 'asc')
                      <i class="fas fa-sort-alpha-up-alt float-end mt-1"></i>
                    @else
                      <i class="fas fa-sort-alpha-down-alt float-end mt-1"></i>
                    @endif
                  @else
                    <i class="fas fa-sort float-end mt-1"></i>
                  @endif
                </th>
                <th class="cursor-pointer">Interpretes</th>
                <th class="cursor-pointer">Usuario</th>
                <th class="cursor-pointer" wire:click="order('estado')">Estado
                  @if ($sort == 'estado')
                    @if ($order == 'asc')
                      <i class="fas fa-sort-alpha-up-alt float-end mt-1"></i>
                    @else
                      <i class="fas fa-sort-alpha-down-alt float-end mt-1"></i>
                    @endif
                  @else
                    <i class="fas fa-sort float-end mt-1"></i>
                  @endif
                </th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($noticias as $noticia)
                <tr>
                  <td class="border px-4 py-2">{{ $noticia->id }}</td>
                  <td class="border px-4 py-2">
                    {{-- <div class="flex-shrink-0 h-10 w-10"> --}}
                    <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="" width="80">
                    {{-- </div> --}}
                  </td>
                  <td class="border px-4 py-2">{{ $noticia->titulo }}</td>
                  <td class="border px-4 py-2">{{ $noticia->publicar }}</td>
                  <td class="border px-4 py-2">{{ $noticia->interprete->interprete }}
                    {{-- @foreach ($noticia->interpretes as $interprete)
                                            <span class="badge bg-secondary">{{ $interprete->interprete }}</span>
                                        @endforeach --}}
                  </td>
                  <td class="border px-4 py-2">{{ $noticia->user->name }}</td>
                  <td class="border px-4 py-2 text-center">
                    {{-- <livewire:toggle-button :model="$noticia" field="estado" key="{{ $noticia->id }}" /> --}}
                  </td>
                  <td class="border px-4 py-2 text-center">
                    <button wire:click="edit({{ $noticia->id }})" class="btn btn-primary btn-sm">
                      Editar
                    </button>
                    <button wire:click="$emit('alertDelete', {{ $noticia->id }})" class="btn btn-danger btn-sm">
                      Borrar
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>

          {{-- {{ $noticias->links() }} --}}
        @endif
      </div>
    </div>
  </div>

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
