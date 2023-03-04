{{-- @extends('layouts.app') --}}

<x-slot name="header">
    <h1>Listado de Noticias</h1>
</x-slot>

{{-- @section('content') --}}
<div class="container">


    <div class="my-3">
        <a href="{{ route('noticias.create') }}" class="btn btn-primary">Crear Noticia</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Slug</th>
                    <th>Autor</th>
                    <th>Intérpretes</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($noticias as $noticia)
                    <tr>
                        <td>{{ $noticia->titulo }}</td>
                        <td>{{ $noticia->slug }}</td>
                        <td>{{ $noticia->user->name }}</td>
                        <td>
                            @foreach ($noticia->interpretes as $interprete)
                                {{ $interprete->interprete }}
                                @if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        </td>
                        <td>{{ $noticia->status ? 'Publicada' : 'Borrador' }}</td>
                        <td>
                            <a href="{{ route('noticias.show', $noticia->id) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('noticias.edit', $noticia->id) }}"
                                class="btn btn-sm btn-primary">Editar</a>
                            <form action="{{ route('noticias.destroy', $noticia->id) }}" method="post"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('¿Estás seguro de que deseas eliminar esta noticia?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $noticias->links() }}
</div>
{{-- @endsection --}}
