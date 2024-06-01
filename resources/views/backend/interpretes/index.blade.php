@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="row">
      <div class="col-12">
        <h1 class="mb-3">Interpretes</h1>
        <a href="{{ route('interpretes.create') }}" class="btn btn-primary mb-3">Crear Interprete</a>
        @if ($message = Session::get('success'))
          <div class="alert alert-success">
            <p>{{ $message }}</p>
          </div>
        @endif
        <table class="table table-bordered">
          <tr>
            <th>ID</th>
            <th>Interprete</th>
            <th>Slug</th>
            <th>Correo</th>
            <th>Acciones</th>
          </tr>
          @foreach ($interpretes as $interprete)
            <tr>
              <td>{{ $interprete->id }}</td>
              <td>{{ $interprete->interprete }}</td>
              <td>{{ $interprete->slug }}</td>
              <td>{{ $interprete->correo }}</td>
              <td>
                <form action="{{ route('interpretes.destroy', $interprete->id) }}" method="POST">
                  <a class="btn btn-info" href="{{ route('interpretes.show', $interprete->id) }}">Mostrar</a>
                  <a class="btn btn-primary" href="{{ route('interpretes.edit', $interprete->id) }}">Editar</a>
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
              </td>
            </tr>
          @endforeach
        </table>
      </div>
    </div>
  </div>
@endsection
