@extends('adminlte::page')

@section('title', 'Categories')

@section('content_header')
  <h1><i class="fas fa-list mr-2"></i>Categorías (Clasificados)</h1>
  <a href="{{ route('backend.categories.create') }}" class="btn btn-primary mb-3">Crear Categoría</a>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Slug</th>
            <th width="150">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($categories as $category)
            <tr>
              <td>{{ $category->id }}</td>
              <td>{{ $category->name }}</td>
              <td>{{ $category->slug }}</td>
              <td>
                <a href="{{ route('backend.categories.edit', $category) }}" class="btn btn-sm btn-warning">
                  <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('backend.categories.destroy', $category) }}" method="POST" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro?')">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
