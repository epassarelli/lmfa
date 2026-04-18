@extends('adminlte::page')

@section('title', 'Panel del Publicador')

@section('content_header')
    <h1>
        <i class="fas fa-broadcast-tower mr-2"></i>
        Panel del Publicador — Pasarela de Contenidos
    </h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- Mensajes flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ================================================================
         FILA DE ACCESOS RÁPIDOS
    ================================================================ --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-body py-2">
                    <strong><i class="fas fa-bolt mr-1"></i> Accesos rápidos:</strong>
                    <a href="{{ route('pasarela.social-accounts.index') }}" class="btn btn-sm btn-outline-primary ml-2">
                        <i class="fas fa-share-alt mr-1"></i> Cuentas sociales
                    </a>
                    <a href="{{ route('backend.events.create') ?? '#' }}" class="btn btn-sm btn-outline-success ml-1">
                        <i class="fas fa-calendar-plus mr-1"></i> Nuevo evento
                    </a>
                    <a href="{{ route('backend.news.create') ?? '#' }}" class="btn btn-sm btn-outline-info ml-1">
                        <i class="fas fa-newspaper mr-1"></i> Nueva noticia
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================
         FILA 1: RESUMEN CUENTAS SOCIALES + PRÓXIMOS EVENTOS
    ================================================================ --}}
    <div class="row">

        {{-- Cuentas sociales conectadas --}}
        <div class="col-lg-4 col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-share-alt mr-1"></i> Cuentas Sociales
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('pasarela.social-accounts.index') }}"
                           class="btn btn-xs btn-primary">
                            <i class="fas fa-cog"></i> Gestionar
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($socialAccounts->isEmpty())
                        <div class="p-3 text-center text-muted small">
                            <i class="fas fa-unlink fa-lg mb-1 d-block"></i>
                            Sin cuentas conectadas.
                            <a href="{{ route('pasarela.social-accounts.index') }}">Conectar ahora</a>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($socialAccounts as $sa)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span>
                                    @switch($sa->provider)
                                        @case('facebook')  <i class="fab fa-facebook text-primary mr-1"></i> @break
                                        @case('instagram') <i class="fab fa-instagram text-danger mr-1"></i> @break
                                        @case('telegram')  <i class="fab fa-telegram text-info mr-1"></i>   @break
                                        @default           <i class="fas fa-globe mr-1"></i>
                                    @endswitch
                                    {{ $sa->account_name }}
                                </span>
                                @if($sa->status === 'active')
                                    <span class="badge badge-success">Activa</span>
                                @elseif($sa->status === 'expired')
                                    <span class="badge badge-warning">Expirada</span>
                                @else
                                    <span class="badge badge-secondary">Inactiva</span>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- Próximos eventos --}}
        <div class="col-lg-4 col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-1"></i> Próximos Eventos
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($upcomingEvents->isEmpty())
                        <div class="p-3 text-center text-muted small">Sin eventos próximos aprobados.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($upcomingEvents as $ev)
                            <li class="list-group-item py-2">
                                <strong class="d-block text-truncate" title="{{ $ev->title }}">
                                    {{ $ev->title }}
                                </strong>
                                <small class="text-muted">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $ev->start_at->format('d/m/Y H:i') }}
                                </small>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- Solicitudes de publicación recientes --}}
        <div class="col-lg-4 col-md-12">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-paper-plane mr-1"></i> Solicitudes de Publicación
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($requests->isEmpty())
                        <div class="p-3 text-center text-muted small">Sin solicitudes recientes.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($requests->take(5) as $req)
                            <li class="list-group-item py-2">
                                <div class="d-flex justify-content-between">
                                    <span class="text-truncate" style="max-width:180px">
                                        <small>{{ ucfirst($req->content_type) }} #{{ $req->content_id }}</small>
                                    </span>
                                    @switch($req->status)
                                        @case('pending')
                                            <span class="badge badge-secondary">Pendiente</span> @break
                                        @case('processing')
                                            <span class="badge badge-warning">Procesando</span> @break
                                        @case('done')
                                            <span class="badge badge-success">Listo</span> @break
                                        @case('failed')
                                            <span class="badge badge-danger">Error</span> @break
                                        @default
                                            <span class="badge badge-light">{{ $req->status }}</span>
                                    @endswitch
                                </div>
                                <small class="text-muted">
                                    {{ $req->created_at->diffForHumans() }}
                                    · {{ $req->targets->count() }} canal(es)
                                </small>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ================================================================
         FILA 2: MIS EVENTOS Y MIS NOTICIAS
    ================================================================ --}}
    <div class="row">

        {{-- Mis eventos --}}
        <div class="col-lg-6">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-day mr-1"></i> Mis Eventos
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($myEvents->isEmpty())
                        <div class="p-3 text-center text-muted small">Sin eventos cargados.</div>
                    @else
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myEvents as $ev)
                                <tr>
                                    <td class="text-truncate" style="max-width:200px">{{ $ev->title }}</td>
                                    <td>
                                        @switch($ev->editorial_status)
                                            @case('draft')
                                                <span class="badge badge-secondary">Borrador</span> @break
                                            @case('pending_review')
                                                <span class="badge badge-warning">En revisión</span> @break
                                            @case('approved')
                                                <span class="badge badge-success">Aprobado</span> @break
                                            @case('rejected')
                                                <span class="badge badge-danger">Rechazado</span> @break
                                            @default
                                                <span class="badge badge-light">{{ $ev->editorial_status }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <small>{{ $ev->start_at ? $ev->start_at->format('d/m/Y') : '—' }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        {{-- Mis noticias --}}
        <div class="col-lg-6">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-newspaper mr-1"></i> Mis Noticias
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($myNews->isEmpty())
                        <div class="p-3 text-center text-muted small">Sin noticias cargadas.</div>
                    @else
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myNews as $n)
                                <tr>
                                    <td class="text-truncate" style="max-width:200px">{{ $n->title }}</td>
                                    <td>
                                        @switch($n->editorial_status)
                                            @case('draft')
                                                <span class="badge badge-secondary">Borrador</span> @break
                                            @case('pending_review')
                                                <span class="badge badge-warning">En revisión</span> @break
                                            @case('approved')
                                                <span class="badge badge-success">Aprobado</span> @break
                                            @case('rejected')
                                                <span class="badge badge-danger">Rechazado</span> @break
                                            @default
                                                <span class="badge badge-light">{{ $n->editorial_status }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <small>{{ $n->created_at->format('d/m/Y') }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>
@stop

@section('css')
<style>
    .list-group-item { font-size: .9rem; }
    .table-sm td, .table-sm th { padding: .4rem .6rem; }
</style>
@stop
