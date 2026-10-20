@extends('adminlte::page')

@section('title', 'Moderación de Contenidos')

@section('content_header')
    <h1><i class="fas fa-tasks mr-2"></i>Centro de Moderación</h1>
@stop

@section('content')
@php $dash = fn ($v) => $v ?: '-'; @endphp
<div class="row">
    @if($total === 0)
        <div class="col-12">
            <div class="alert alert-light border text-muted">No hay contenido pendiente de moderación.</div>
        </div>
    @endif

    @if($events->isNotEmpty())
        <x-backend.moderation-card key="events" title="Eventos Pendientes" color="info" :count="$events->count()" :open="$open === 'events'" :columns="['Evento', 'Autor', 'Fecha Evento']">
            @foreach($events as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->user?->name ?? 'Sistema' }}</td>
                    <td>{{ $item->start_at?->format('d/m/Y') ?? '-' }}</td>
                    <x-backend.moderation-actions :edit="route('backend.events.edit', $item)" type="event" :id="$item->id" />
                </tr>
            @endforeach
        </x-backend.moderation-card>
    @endif

    @if($programs->isNotEmpty())
        <x-backend.moderation-card key="programs" title="Jornadas de Eventos Pendientes" color="info" :count="$programs->count()" :open="$open === 'programs'" :columns="['Jornada', 'Autor']">
            @foreach($programs as $program)
                <tr>
                    <td>{{ $program->title }}</td>
                    <td>{{ $program->creator?->name ?? 'Sistema' }}</td>
                    <x-backend.moderation-actions :edit="route('backend.event-programs.edit', $program)" reviewLabel="Revisar evento y jornadas" />
                </tr>
            @endforeach
        </x-backend.moderation-card>
    @endif

    @if($news->isNotEmpty())
        <x-backend.moderation-card key="news" title="Noticias Pendientes" color="warning" :count="$news->count()" :open="$open === 'news'" :columns="['Título', 'Autor', 'Fecha Carga']">
            @foreach($news as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->user?->name ?? 'Sistema' }}</td>
                    <td>{{ $item->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <x-backend.moderation-actions :edit="route('backend.news.edit', $item)" type="news" :id="$item->id" />
                </tr>
            @endforeach
        </x-backend.moderation-card>
    @endif

    @if($interpretes->isNotEmpty())
        <x-backend.moderation-card key="interpretes" title="Intérpretes Pendientes" color="success" :count="$interpretes->count()" :open="$open === 'interpretes'" :columns="['Intérprete', 'Autor', 'Fecha Carga']">
            @foreach($interpretes as $item)
                <tr>
                    <td>{{ $item->interprete }}</td>
                    <td>{{ $item->user?->name ?? 'Sistema' }}</td>
                    <td>{{ $item->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <x-backend.moderation-actions :edit="route('backend.interpretes.edit', $item)" type="interprete" :id="$item->id" />
                </tr>
            @endforeach
        </x-backend.moderation-card>
    @endif

    @if($albunes->isNotEmpty())
        <x-backend.moderation-card key="albumes" title="Discos Pendientes" color="info" :count="$albunes->count()" :open="$open === 'albumes'" :columns="['Disco', 'Autor', 'Fecha Carga']">
            @foreach($albunes as $item)
                <tr>
                    <td>{{ $item->album }}</td>
                    <td>{{ $item->user?->name ?? 'Sistema' }}</td>
                    <td>{{ $item->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <x-backend.moderation-actions :edit="route('backend.discos.edit', $item)" type="album" :id="$item->id" />
                </tr>
            @endforeach
        </x-backend.moderation-card>
    @endif

    @if($canciones->isNotEmpty())
        <x-backend.moderation-card key="canciones" title="Canciones Pendientes" color="secondary" :count="$canciones->count()" :open="$open === 'canciones'" :columns="['Canción', 'Autor', 'Fecha Carga']">
            @foreach($canciones as $item)
                <tr>
                    <td>{{ $item->cancion }}</td>
                    <td>{{ $item->user?->name ?? 'Sistema' }}</td>
                    <td>{{ $item->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <x-backend.moderation-actions :edit="route('backend.canciones.edit', $item)" type="cancion" :id="$item->id" />
                </tr>
            @endforeach
        </x-backend.moderation-card>
    @endif

    @if($festivales->isNotEmpty())
        <x-backend.moderation-card key="festivales" title="Festivales en Borrador" color="success" :count="$festivales->count()" :open="$open === 'festivales'" :columns="['Título', 'Autor', 'Provincia', 'Fecha Carga']">
            @foreach($festivales as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->user?->name ?? 'Sistema' }}</td>
                    <td>{{ $item->provincia?->nombre ?? '-' }}</td>
                    <td>{{ $item->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <x-backend.moderation-actions :edit="route('backend.festivales.edit', $item)" type="festival" :id="$item->id" />
                </tr>
            @endforeach
        </x-backend.moderation-card>
    @endif

    @if($pendingVenues->isNotEmpty())
        <x-backend.moderation-card key="venues" title="Lugares Propuestos" color="warning" :count="$pendingVenues->count()" :open="$open === 'venues'" :columns="['Lugar', 'Ciudad / Provincia']">
            @foreach($pendingVenues as $venue)
                <tr>
                    <td>{{ $venue->name }}</td>
                    <td>{{ $dash(trim(($venue->city ?? '').' / '.($venue->provincia?->nombre ?? ''), ' /')) }}</td>
                    <x-backend.moderation-actions :edit="route('backend.venues.edit', $venue)" reviewLabel="Revisar y activar" />
                </tr>
            @endforeach
        </x-backend.moderation-card>
    @endif
</div>
@stop
