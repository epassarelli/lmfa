@extends('adminlte::page')

@section('title', 'Moderación de Contenidos')

@section('content_header')
    <h1><i class="fas fa-tasks mr-2"></i>Centro de Moderación</h1>
@stop

@section('content')
<div class="row">
    <!-- Noticias -->
    <div class="col-md-12">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">Noticias Pendientes ({{ $news->count() }})</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Fecha Carga</th>
                            <th style="width: 150px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($news as $item)
                        <tr>
                            <td>{{ $item->title }}</td>
                            <td>{{ $item->user?->name ?? 'Sistema' }}</td>
                            <td>{{ $item->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>
                                <a href="{{ route('backend.news.edit', $item) }}" class="btn btn-xs btn-primary"><i class="fas fa-eye"></i> Revisar</a>
                                <form action="{{ route('backend.moderation.publish', ['type' => 'news', 'id' => $item->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-success"><i class="fas fa-check"></i> Publicar</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">No hay noticias pendientes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Eventos -->
    <div class="col-md-12">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Eventos Pendientes ({{ $events->count() }})</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Evento</th>
                            <th>Autor</th>
                            <th>Fecha Evento</th>
                            <th style="width: 150px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $item)
                        <tr>
                            <td>{{ $item->title }}</td>
                            <td>{{ $item->user?->name ?? 'Sistema' }}</td>
                            <td>{{ $item->start_at?->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('backend.events.edit', $item) }}" class="btn btn-xs btn-primary"><i class="fas fa-eye"></i> Revisar</a>
                                <form action="{{ route('backend.moderation.publish', ['type' => 'event', 'id' => $item->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-success"><i class="fas fa-check"></i> Publicar</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">No hay eventos pendientes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Otros -->
    <div class="col-md-12">
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">Otros Contenidos (Interpretes, Discos, Canciones)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Nombre</th>
                            <th>Autor</th>
                            <th style="width: 150px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($interpretes as $item)
                        <tr>
                            <td><span class="badge badge-success">Artista</span></td>
                            <td>{{ $item->interprete }}</td>
                            <td>{{ $item->user?->name }}</td>
                            <td>
                                <a href="{{ route('backend.interpretes.edit', $item) }}" class="btn btn-xs btn-primary">Revisar</a>
                                <form action="{{ route('backend.moderation.publish', ['type' => 'interprete', 'id' => $item->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-success">Publicar</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @foreach($albunes as $item)
                        <tr>
                            <td><span class="badge badge-info">Disco</span></td>
                            <td>{{ $item->album }}</td>
                            <td>{{ $item->user?->name }}</td>
                            <td>
                                <a href="{{ route('backend.discos.edit', $item) }}" class="btn btn-xs btn-primary">Revisar</a>
                                <form action="{{ route('backend.moderation.publish', ['type' => 'album', 'id' => $item->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-success">Publicar</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @foreach($canciones as $item)
                        <tr>
                            <td><span class="badge badge-dark">Letra</span></td>
                            <td>{{ $item->cancion }}</td>
                            <td>{{ $item->user?->name }}</td>
                            <td>
                                <a href="{{ route('backend.canciones.edit', $item) }}" class="btn btn-xs btn-primary">Revisar</a>
                                <form action="{{ route('backend.moderation.publish', ['type' => 'cancion', 'id' => $item->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-success">Publicar</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
