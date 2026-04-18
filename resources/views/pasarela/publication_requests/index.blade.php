@extends('adminlte::page')

@section('title', 'Mis Solicitudes de Publicación')

@section('content_header')
    <h1>
        <i class="fas fa-list-alt mr-2"></i>
        Mis Solicitudes de Publicación
    </h1>
@stop

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Historial de publicaciones</h3>
        </div>
        <div class="card-body p-0">
            @if ($requests->isEmpty())
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                    No tenés solicitudes de publicación todavía.
                </div>
            @else
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Contenido</th>
                            <th>Modo</th>
                            <th>Targets</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $req)
                            @php
                                $colors = [
                                    'pending'    => 'warning',
                                    'processing' => 'info',
                                    'done'       => 'success',
                                    'failed'     => 'danger',
                                ];
                                $color = $colors[$req->status] ?? 'secondary';
                            @endphp
                            <tr>
                                <td>{{ $req->id }}</td>
                                <td>
                                    <span class="badge badge-secondary">{{ class_basename($req->content_type) }}</span>
                                    #{{ $req->content_id }}
                                </td>
                                <td>{{ $req->mode }}</td>
                                <td>{{ $req->targets->count() }}</td>
                                <td><span class="badge badge-{{ $color }}">{{ $req->status }}</span></td>
                                <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('pasarela.publication-requests.show', $req) }}"
                                       class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        @if ($requests->hasPages())
            <div class="card-footer">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

</div>
@stop
