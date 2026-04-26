@extends('adminlte::page')

@section('title', 'Etiquetas')

@section('content_header')
  <h1><i class="fas fa-tags mr-2"></i>Etiquetas (Clasificados)</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header text-right">
        <a href="{{ route('backend.tags.create') }}" class="btn btn-success">
            <i class="fas fa-plus mr-1"></i> Nueva Etiqueta
        </a>
    </div>
    <div class="card-body">
        <table id="tags-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th width="50">ID</th>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th width="150">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tags as $tag)
                    <tr>
                        <td>{{ $tag->id }}</td>
                        <td>{{ $tag->name }}</td>
                        <td><code>{{ $tag->slug }}</code></td>
                        <td>
                            <a href="{{ route('backend.tags.edit', $tag) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('backend.tags.destroy', $tag) }}" method="POST" style="display:inline;">
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

@section('js')
<script>
    $(document).ready(function() {
        $('#tags-table').DataTable();
    });
</script>
@endsection
