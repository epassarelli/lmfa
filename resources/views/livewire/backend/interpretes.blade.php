@section('title', 'Admin Interpretes')

<div>
  {{-- The Master doesn't talk, he acts. --}}
  <div class="container-fluid">

    <div class="row mb-3">

      <div class="col-md-8 mt-4 col-6">
        <h3>Interpretes</h3>
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
              <th scope="col">Interprete</th>
              <th scope="col">Interprete</th>
              <th scope="col">Usuario</th>
              <th scope="col" class="text-center" style="width: 15%">Acciones</th>
            </tr>
          </thead>

          <tbody>
            @foreach ($interpretes as $interprete)
              <tr>
                <td class="align-middle">{{ $interprete->id }}</td>
                <td class="align-middle">
                  <div class="flex-shrink-0 h-10 w-10">
                    <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" alt="" width="60">
                  </div>
                </td>
                <td class="align-middle">{{ $interprete->interprete }}</td>
                <td class="align-middle">{{ $interprete->interprete }}</td>
                <td class="align-middle">{{ date('Y-m-d', strtotime($interprete->publicar)) }}</td>
                <td class="align-middle">
                  <div class="d-flex flex-md-row gap-1 justify-content-evenly">
                    <div class="m-1 mt-3">
                      <livewire:toggle-button :model="$interprete" field="estado" key="{{ $interprete->id }}" />
                    </div>
                    <button wire:click="edit({{ $interprete->id }})" class="btn btn-sm btn-primary m-1"
                      data-toggle="modal" data-target="#roleModal" title="Editar"><i
                        class="fa-pencil-square-o"></i></button>
                    <button wire:click="$emit('alertDelete',{{ $interprete->id }})" class="btn btn-sm btn-danger m-1"
                      title="Eliminar"><i class="fas fa-trash-alt" style="color: white "></i></button>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>


        @if ($modal)
          @include('livewire.backend.interpretes-form')
        @endif




      </div>
    </div>
  </div>
</div>
