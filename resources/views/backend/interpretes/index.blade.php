@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content_header')
  <h1>Gestión de Interpretes</h1>
@stop

@section('content')

  <div class="card">

    <div class="card-header text-right">
      <a href="{{ route('backend.interpretes.create') }}" class="btn btn-success mb-3"><i class="fas fa-plus"></i> Crear
        Interprete</a>
    </div>

    <div class="card-body">

      @if ($message = Session::get('success'))
        <div class="alert alert-success">
          <p>{{ $message }}</p>
        </div>
      @endif

      <table id="interpretes" class="table table-striped table-bordered table-hover">
        <thead>
          <th>ID</th>
          <th>Foto</th>
          <th>Interprete</th>
          {{-- <th>Slug</th> --}}
          <th>Correo</th>
          <th>Acciones</th>
        </thead>
        <tbody>
          @foreach ($interpretes as $interprete)
            <tr>
              <td>{{ $interprete->id }}</td>
              <td>
                <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" alt="{{ $interprete->interprete }}"
                  class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
              </td>
              <td>{{ $interprete->interprete }}</td>
              {{-- <td>{{ $interprete->slug }}</td> --}}
              <td>{{ $interprete->correo }}</td>
              {{-- <td class="text-center"><livewire:toggle-button :model="$interprete" field="estado"
                  key="{{ $interprete->id }}" />
              </td> --}}
              <td class="text-right" style="white-space: nowrap;">
                <div class="action-icons">
                  <a class="btn btn-primary" href="{{ route('backend.interpretes.show', $interprete->id) }}">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a class="btn btn-warning" href="{{ route('backend.interpretes.edit', $interprete->id) }}">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="{{ route('backend.interpretes.destroy', $interprete->id) }}" method="POST"
                    style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </form>
                </div>
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
    $(document).ready(function() {
      $('#interpretes').DataTable();
    });
  </script>
@stop
