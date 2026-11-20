<form method="GET" class="form-row align-items-end mb-3">
    <input type="hidden" name="tab" value="manual">
    <div class="col-6 col-md-3"><label class="small mb-0" for="m-kind">Tipo</label>
        <select id="m-kind" name="m_kind" class="form-control form-control-sm" onchange="this.form.submit()">@foreach($kinds as $k => $name)<option value="{{ $k }}" @selected($mKind===$k)>{{ $name }}</option>@endforeach</select></div>
    <div class="col-6 col-md-3"><label class="small mb-0" for="m-network">Red</label>
        <select id="m-network" name="m_network" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="both" @selected($mNetwork==='both')>Facebook + Instagram</option>
            <option value="facebook" @selected($mNetwork==='facebook')>Solo Facebook</option>
            <option value="instagram" @selected($mNetwork==='instagram')>Solo Instagram</option>
        </select></div>
    <noscript><div class="col-md-2"><button class="btn btn-sm btn-secondary">Ver cola</button></div></noscript>
</form>

<p class="small text-muted mb-2">
    Último enviado a {{ $mNetwork === 'instagram' ? 'Instagram' : 'Facebook' }}:
    @if($lastSent)<strong>#{{ $lastSent->ledger_content_id }} {{ $lastSent->request?->content?->title }}</strong> ({{ $lastSent->published_at?->timezone($tz)->format('d/m/Y H:i') }})@else ninguno todavía.@endif
    Abajo están los no enviados que siguen en la cola, en el orden en que saldrían. Si elegís uno que no es el siguiente, los anteriores siguen en la cola.
    @if($mNetwork === 'instagram') Instagram solo ofrece lo que ya salió en Facebook.@endif
</p>

@if($queue->isEmpty())
    <div class="alert alert-light">No hay {{ mb_strtolower($kinds[$mKind]) }} pendientes en la cola.</div>
@else
<form method="POST" action="{{ route('social-publications.preview') }}" class="form-row align-items-end">@csrf
    <input type="hidden" name="content_kind" value="{{ $mKind }}"><input type="hidden" name="network" value="{{ $mNetwork }}">
    <div class="col-md-9"><label class="small mb-0" for="m-content">Contenido</label>
        <select id="m-content" name="content_id" class="form-control">
        @foreach($queue as $i => $item)
            <option value="{{ $item->id }}" @selected(($preview['content_id'] ?? null) === $item->id)>#{{ $item->id }} · {{ $item->title }}
                @if($mKind === 'events' && $item->start_at) ({{ $item->start_at->timezone($tz)->format('d/m/Y') }})@endif{{ $i === 0 ? ' — siguiente en la cola' : '' }}</option>
        @endforeach
        </select></div>
    <div class="col-md-3"><button class="btn btn-outline-primary btn-block"><i class="fas fa-eye"></i> Vista previa</button></div>
</form>
@endif

@if($preview)
<div class="card mt-3"><div class="card-body"><div class="row">
    <div class="col-md-4 mb-3"><img src="{{ route('social-publications.preview.asset', $preview['hash']) }}" alt="Placa de {{ $preview['title'] }}" class="img-fluid border" width="1080" height="1350" loading="lazy"></div>
    <div class="col-md-8">
        <label class="small mb-1">Texto que se publicará</label>
        <pre class="border bg-light p-2 small" style="white-space:pre-wrap">{{ $preview['copy'] }}</pre>
        @if($blocked)
            <div class="alert alert-warning small">No se puede publicar ahora: {{ $blocked }}</div>
        @else
        <form method="POST" action="{{ route('social-publications.publish') }}" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=&quot;fas fa-spinner fa-spin&quot;></i> Publicando…';">@csrf
            <input type="hidden" name="content_kind" value="{{ $mKind }}"><input type="hidden" name="network" value="{{ $mNetwork }}"><input type="hidden" name="content_id" value="{{ $preview['content_id'] }}">
            <label class="small d-block"><input type="checkbox" name="confirmed" value="1" required> Confirmo publicar #{{ $preview['content_id'] }} en {{ ['both' => 'Facebook e Instagram', 'facebook' => 'Facebook', 'instagram' => 'Instagram'][$mNetwork] }} ahora.</label>
            <button class="btn btn-primary"><i class="fas fa-paper-plane"></i> Publicar ahora</button>
        </form>
        @endif
    </div>
</div></div></div>
@endif
