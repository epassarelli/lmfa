@extends('adminlte::page')

@section('metaTitle', 'Listado de Noticias')

@section('content_header')
  <h1>Noticias</h1>
@stop

@section('content')
  <div class="card">
    <div class="card-header">
      <a href="{{ route('noticias.create') }}" class="btn btn-primary">Nueva Noticia</a>
    </div>
    <div class="card-body">
      <table id="noticias" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($noticias as $noticia)
            <tr>
              <td>{{ $noticia->id }}</td>
              <td>{{ $noticia->titulo }}</td>
              <td>
                <a href="{{ route('noticias.edit', $noticia) }}" class="btn btn-warning btn-sm">Editar</a>
                <form action="{{ route('noticias.destroy', $noticia) }}" method="POST" style="display:inline-block;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                </form>
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
      $('#noticias').DataTable();
    });
  </script>
@stop
