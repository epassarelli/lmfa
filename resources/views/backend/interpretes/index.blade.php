@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content_header')
  <h1>Gestión de Interpretes</h1>
@stop

@section('content')

  <div class="card">

    <div class="card-header text-right">
      <a href="{{ route('interpretes.create') }}" class="btn btn-primary mb-3">Crear Interprete</a>
    </div>

    <div class="card-body">

      @if ($message = Session::get('success'))
        <div class="alert alert-success">
          <p>{{ $message }}</p>
        </div>
      @endif

      <table id="interpretes" class="table table-striped table-bordered">
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
              <td class="text-right">
                <div class="action-icons">
                  <a class="btn btn-info" href="{{ route('interpretes.show', $interprete->id) }}">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a class="btn btn-warning" href="{{ route('interpretes.edit', $interprete->id) }}">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="{{ route('interpretes.destroy', $interprete->id) }}" method="POST"
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
