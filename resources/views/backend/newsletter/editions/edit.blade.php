@extends('adminlte::page')

@section('title', 'Edición del Newsletter')

@section('content_header')
    <h1>Edición {{ $edition->period() }}
        @if($edition->isSent())<span class="badge badge-success">Enviada</span>
        @elseif($edition->isApproved())<span class="badge badge-info">Aprobada</span>
        @else<span class="badge badge-warning">Borrador</span>@endif
    </h1>
@stop

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row">
    <div class="col-lg-8">
        @if($edition->isDraft())
            <form method="POST" action="{{ route('backend.newsletter.editions.update', $edition) }}" id="edition-form">
                @csrf
                @method('PUT')
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header"><h3 class="card-title">Mensaje</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="subject">Asunto <span class="text-danger">*</span></label>
                            <input type="text" id="subject" name="subject" class="form-control" maxlength="150" required value="{{ old('subject', $edition->subject) }}">
                            <small class="form-text text-muted">Es lo primero que se ve en la bandeja de entrada. Que sea corto y concreto.</small>
                        </div>
                        <div class="form-group mb-0">
                            <label for="intro">Introducción</label>
                            <textarea id="intro" name="intro" rows="3" class="form-control" maxlength="1000">{{ old('intro', $edition->intro) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-header"><h3 class="card-title">Contenido</h3></div>
                    <div class="card-body">
                        <p class="small text-muted">Marcá lo que querés incluir. Las opciones salen de lo publicado en el período ({{ $edition->period() }}) y de la agenda de los próximos {{ \App\Services\NewsletterEditionService::AGENDA_DAYS }} días.</p>
                        @foreach(\App\Models\NewsletterEdition::SECTIONS as $type => $title)
                            <h6 class="mt-3">{{ $title }}</h6>
                            @php($chosen = old("selection.$type", $edition->selection[$type] ?? []))
                            @forelse($candidates[$type] ?? [] as $item)
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="sel-{{ $type }}-{{ $item['id'] }}" name="selection[{{ $type }}][]" value="{{ $item['id'] }}" @checked(in_array($item['id'], array_map('intval', $chosen), true))>
                                    <label class="custom-control-label" for="sel-{{ $type }}-{{ $item['id'] }}">{{ $item['title'] }}@if($item['meta']) <span class="text-muted">· {{ $item['meta'] }}</span>@endif</label>
                                </div>
                            @empty
                                <p class="small text-muted mb-0">Nada para incluir en esta sección.</p>
                            @endforelse
                        @endforeach
                    </div>
                    <div class="card-footer"><button type="submit" class="btn btn-primary">Guardar borrador</button></div>
                </div>
            </form>
        @else
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header"><h3 class="card-title">{{ $edition->subject }}</h3></div>
                <div class="card-body">
                    @if(filled($edition->intro))<p>{{ $edition->intro }}</p>@endif
                    @foreach(\App\Models\NewsletterEdition::SECTIONS as $type => $title)
                        @if(! empty($content[$type]))
                            <h6 class="mt-3">{{ $title }}</h6>
                            <ul>@foreach($content[$type] as $item)<li><a href="{{ $item['url'] }}" target="_blank" rel="noopener">{{ $item['title'] }}</a>@if(! empty($item['meta'])) <span class="text-muted">· {{ $item['meta'] }}</span>@endif</li>@endforeach</ul>
                        @endif
                    @endforeach
                </div>
                <div class="card-footer small text-muted">Contenido congelado al aprobar{{ $edition->approved_at ? ' ('.$edition->approved_at->format('d/m/Y H:i').')' : '' }}{{ $edition->approver ? ' por '.$edition->approver->name : '' }}.</div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header"><h3 class="card-title">Revisar</h3></div>
            <div class="card-body">
                <a href="{{ route('backend.newsletter.editions.preview', $edition) }}" target="_blank" class="btn btn-outline-info btn-block mb-3"><i class="fas fa-eye mr-1"></i> Ver vista previa</a>
                <form method="POST" action="{{ route('backend.newsletter.editions.test', $edition) }}">
                    @csrf
                    <label for="test-email" class="small text-muted mb-1">Enviar una prueba a</label>
                    <div class="input-group">
                        <input type="email" id="test-email" name="email" class="form-control" required value="{{ old('email', auth()->user()->email) }}">
                        <div class="input-group-append"><button type="submit" class="btn btn-info">Enviar</button></div>
                    </div>
                    <small class="form-text text-muted">Sale con «[PRUEBA]» en el asunto y no cuenta como envío.{{ $edition->test_sent_at ? ' Última prueba: '.$edition->test_sent_at->format('d/m H:i').'.' : '' }}</small>
                </form>
            </div>
        </div>

        <div class="card card-outline card-success shadow-sm">
            <div class="card-header"><h3 class="card-title">Publicar</h3></div>
            <div class="card-body">
                <p class="small mb-2">Destinatarios si se envía hoy: <strong>{{ $deliverable }}</strong> suscriptor(es) confirmado(s).</p>
                @if($edition->isDraft())
                    <form method="POST" action="{{ route('backend.newsletter.editions.approve', $edition) }}">
                        @csrf
                        <div class="form-group">
                            <label for="scheduled_at" class="small text-muted mb-1">Programar envío (opcional)</label>
                            <input type="datetime-local" id="scheduled_at" name="scheduled_at" class="form-control">
                            <small class="form-text text-muted">Si lo dejás vacío, queda aprobada y la enviás vos cuando quieras.</small>
                        </div>
                        <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Al aprobar se congela el contenido guardado. ¿Aprobar esta edición?');">Aprobar edición</button>
                        <p class="small text-muted mt-2 mb-0">Se aprueba lo que está guardado: guardá el borrador antes.</p>
                    </form>
                    <form method="POST" action="{{ route('backend.newsletter.editions.destroy', $edition) }}" class="mt-3" onsubmit="return confirm('¿Eliminar este borrador?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm btn-block">Eliminar borrador</button>
                    </form>
                @elseif($edition->isApproved())
                    <p class="small">Aprobada{{ $edition->scheduled_at ? ' y programada para el '.$edition->scheduled_at->format('d/m/Y H:i') : '' }}.</p>
                    <form method="POST" action="{{ route('backend.newsletter.editions.send', $edition) }}" onsubmit="return confirm('Se enviará a {{ $deliverable }} suscriptor(es) confirmado(s). No se puede deshacer. ¿Enviar ahora?');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block"><i class="fas fa-paper-plane mr-1"></i> Enviar ahora</button>
                    </form>
                    <form method="POST" action="{{ route('backend.newsletter.editions.unapprove', $edition) }}" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm btn-block">Volver a borrador</button>
                    </form>
                @else
                    <p class="small mb-1">Enviada el {{ $edition->sent_at->format('d/m/Y H:i') }} a <strong>{{ $edition->recipients_count }}</strong> destinatario(s).</p>
                    <p class="small text-muted mb-0">Entregados hasta ahora: {{ $sentCount }}. Una edición enviada no se puede modificar ni volver a enviar.</p>
                @endif
            </div>
        </div>
        <a href="{{ route('backend.newsletter.editions.index') }}" class="btn btn-link">← Volver a las ediciones</a>
    </div>
</div>
@stop
