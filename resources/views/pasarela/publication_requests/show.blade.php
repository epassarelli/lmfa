@extends('adminlte::page')

@section('title', 'Solicitud de Publicación #' . $publicationRequest->id)

@section('content_header')
    <h1>
        <i class="fas fa-tasks mr-2"></i>
        Solicitud de Publicación
        <small class="text-muted ml-2">#{{ $publicationRequest->id }}</small>
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

    <div class="row">
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Datos de la solicitud</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">ID:</th>
                            <td>{{ $publicationRequest->id }}</td>
                        </tr>
                        <tr>
                            <th>Tipo de contenido:</th>
                            <td>{{ ucfirst(class_basename($publicationRequest->content_type)) }}</td>
                        </tr>
                        <tr>
                            <th>ID de contenido:</th>
                            <td>{{ $publicationRequest->content_id }}</td>
                        </tr>
                        <tr>
                            <th>Modo:</th>
                            <td>
                                <span class="badge badge-secondary">{{ $publicationRequest->mode }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending'   => 'warning',
                                        'processing'=> 'info',
                                        'done'      => 'success',
                                        'failed'    => 'danger',
                                    ];
                                    $color = $statusColors[$publicationRequest->status] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $color }}">{{ $publicationRequest->status }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Publicar en portal:</th>
                            <td>
                                @if ($publicationRequest->wants_portal_publish)
                                    <i class="fas fa-check text-success"></i> Sí
                                @else
                                    <i class="fas fa-times text-muted"></i> No
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Redes propias:</th>
                            <td>
                                @if ($publicationRequest->wants_own_social)
                                    <i class="fas fa-check text-success"></i> Sí
                                @else
                                    <i class="fas fa-times text-muted"></i> No
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Redes del portal:</th>
                            <td>
                                @if ($publicationRequest->wants_portal_social)
                                    <i class="fas fa-check text-success"></i> Sí
                                @else
                                    <i class="fas fa-times text-muted"></i> No
                                @endif
                            </td>
                        </tr>
                        @if ($publicationRequest->scheduled_at)
                            <tr>
                                <th>Programado para:</th>
                                <td>{{ $publicationRequest->scheduled_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>Creado:</th>
                            <td>{{ $publicationRequest->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bullseye mr-1"></i>
                        Targets ({{ $publicationRequest->targets->count() }})
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if ($publicationRequest->targets->isEmpty())
                        <div class="p-3 text-muted">No se generaron targets.</div>
                    @else
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Destino</th>
                                    <th>Estado</th>
                                    <th>Prioridad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($publicationRequest->targets as $target)
                                    @php
                                        $targetColors = [
                                            'pending'    => 'warning',
                                            'processing' => 'info',
                                            'success'    => 'success',
                                            'failed'     => 'danger',
                                        ];
                                        $tc = $targetColors[$target->status] ?? 'secondary';
                                    @endphp
                                    <tr>
                                        <td>
                                            <i class="fab fa-{{ $target->provider === 'native_portal' ? 'globe' : $target->provider }} mr-1"></i>
                                            {{ $target->provider }}
                                        </td>
                                        <td>{{ $target->destination_type ?? '-' }}</td>
                                        <td><span class="badge badge-{{ $tc }}">{{ $target->status }}</span></td>
                                        <td>{{ $target->priority }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('pasarela.publication-requests.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Mis solicitudes
    </a>

</div>
@stop
