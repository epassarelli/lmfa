@extends('adminlte::page')

@section('title', 'Ediciones del Newsletter')

@section('content_header')
    <h1>Ediciones del Newsletter</h1>
@stop

@section('content')
<details class="callout callout-info mb-3" data-newsletter-editions-help>
    <summary class="font-weight-bold" style="cursor:pointer"><i class="fas fa-question-circle mr-1"></i> ¿Cómo funciona el envío?</summary>
    <div class="mt-2 small">
        <ol class="pl-3 mb-0">
            <li><strong>Borrador:</strong> cada lunes se arma solo con lo de la semana pasada y la agenda de los próximos 14 días. También podés crearlo a mano.</li>
            <li><strong>Revisión:</strong> elegís qué entra, editás el asunto y la introducción, mirás la vista previa y te mandás una prueba.</li>
            <li><strong>Aprobación:</strong> al aprobar se congela el contenido. Nada se envía sin este paso.</li>
            <li><strong>Envío:</strong> «Enviar ahora» o programado. Solo reciben el newsletter los suscriptores que confirmaron su correo.</li>
        </ol>
    </div>
</details>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title mb-0">Ediciones</h3>
        <div class="card-tools ml-auto d-flex align-items-center">
            <span class="text-muted small mr-3">Suscriptores confirmados: <strong>{{ $deliverable }}</strong></span>
            <a href="{{ route('backend.newsletter.index') }}" class="btn btn-sm btn-outline-secondary mr-2">Suscriptores</a>
            <form method="POST" action="{{ route('backend.newsletter.editions.store') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i> Armar borrador de la semana pasada</button>
            </form>
        </div>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr><th>Período</th><th>Asunto</th><th>Estado</th><th>Destinatarios</th><th>Enviada</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($editions as $edition)
                    <tr>
                        <td>{{ $edition->period() }}</td>
                        <td>{{ $edition->subject }}</td>
                        <td>
                            @if($edition->isSent())<span class="badge badge-success">Enviada</span>
                            @elseif($edition->isApproved())<span class="badge badge-info">Aprobada{{ $edition->scheduled_at ? ' · programada '.$edition->scheduled_at->format('d/m H:i') : '' }}</span>
                            @else<span class="badge badge-warning">Borrador</span>@endif
                        </td>
                        <td>{{ $edition->isSent() ? $edition->recipients_count : '-' }}</td>
                        <td>{{ $edition->sent_at ? $edition->sent_at->format('d/m/Y H:i') : '-' }}</td>
                        <td class="text-right"><a href="{{ route('backend.newsletter.editions.edit', $edition) }}" class="btn btn-sm btn-outline-primary">{{ $edition->isDraft() ? 'Revisar' : 'Ver' }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Todavía no hay ediciones. Armá el borrador de la semana pasada para empezar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $editions->links() }}</div>
</div>
@stop
