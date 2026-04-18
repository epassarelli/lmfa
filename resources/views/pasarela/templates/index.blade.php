@extends('adminlte::page')

@section('title', 'Templates de Publicación')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Templates por Canal</h1>
        <a href="{{ route('pasarela.templates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Template
        </a>
    </div>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>Proveedor</th>
                    <th>Tipo de contenido</th>
                    <th>Variante</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $template)
                    <tr>
                        <td>
                            <span class="badge badge-info">{{ $template->provider }}</span>
                        </td>
                        <td>
                            {{ $template->content_type ? class_basename($template->content_type) : '<em>Global</em>' }}
                        </td>
                        <td><code>{{ $template->variant_name }}</code></td>
                        <td>
                            @if($template->is_active)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('pasarela.templates.edit', $template) }}"
                               class="btn btn-sm btn-outline-primary">Editar</a>
                            <form method="POST"
                                  action="{{ route('pasarela.templates.destroy', $template) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('¿Eliminar este template?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No hay templates configurados.
                            <a href="{{ route('pasarela.templates.create') }}">Crear el primero</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($templates->hasPages())
        <div class="card-footer">
            {{ $templates->links() }}
        </div>
    @endif
</div>
@endsection
