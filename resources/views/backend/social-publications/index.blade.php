@extends('adminlte::page')
@section('title', 'Publicaciones en redes')
@section('content_header')<h1><i class="fas fa-share-alt"></i> Publicaciones en redes</h1>@stop
@section('content')
@php
    $kinds = \App\Services\Publication\SocialPublicationSettings::KINDS;
    $providers = \App\Services\Publication\SocialPublicationSettings::PROVIDERS;
    $icons = ['facebook' => 'fab fa-facebook text-primary', 'instagram' => 'fab fa-instagram text-danger'];
    $badges = ['success' => 'success', 'published' => 'success', 'failed' => 'danger', 'uncertain' => 'warning', 'processing' => 'info', 'reserved' => 'info', 'pending' => 'secondary', 'cancelled' => 'light', 'empty' => 'light', 'error' => 'danger', 'skipped' => 'light'];
    $labels = ['success' => 'Publicado', 'published' => 'Publicado', 'failed' => 'Falló', 'uncertain' => 'Incierto', 'processing' => 'Enviando', 'reserved' => 'Reservado', 'pending' => 'Pendiente', 'cancelled' => 'Cancelado', 'empty' => 'Cola vacía', 'error' => 'Error', 'skipped' => 'Omitido'];
@endphp
@foreach(['success', 'warning'] as $flash)@if(session($flash))<div class="alert alert-{{ $flash }}">{{ session($flash) }}</div>@endif @endforeach
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="card card-outline card-primary"><div class="card-body py-2 d-flex flex-wrap align-items-center" style="gap:1rem">
    <span>Ejecutor: <strong class="badge badge-{{ $settings['executor'] === 'laravel' ? 'success' : 'secondary' }}">{{ $settings['executor'] === 'laravel' ? 'Laravel (servidor)' : 'n8n (Docker local)' }}</strong></span>
    @if($settings['paused'])<span class="badge badge-warning"><i class="fas fa-pause"></i> En pausa</span>@endif
    @foreach($providers as $p => $name)
        <span><i class="{{ $icons[$p] }}"></i> Token:
        @if(!$tokens[$p]['configured'])<span class="text-danger">sin cargar</span>
        @elseif($tokens[$p]['inherited'])<span class="text-muted">usa el de Facebook</span>
        @elseif($tokens[$p]['check_ok'] === false)<span class="text-danger">inválido</span>
        @else<span class="text-success">cargado</span>@endif</span>
    @endforeach
    <span class="text-muted small ml-auto">Horarios en hora argentina.</span>
</div>@if($blocked)<div class="card-footer py-1 small text-muted"><i class="fas fa-info-circle"></i> {{ $blocked }}</div>@endif</div>

<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><a class="nav-link @if($tab==='log') active @endif" href="{{ route('social-publications.index', ['tab' => 'log']) }}"><i class="fas fa-list"></i> Log</a></li>
    <li class="nav-item"><a class="nav-link @if($tab==='manual') active @endif" href="{{ route('social-publications.index', ['tab' => 'manual']) }}"><i class="fas fa-paper-plane"></i> Envío manual</a></li>
    <li class="nav-item"><a class="nav-link @if($tab==='config') active @endif" href="{{ route('social-publications.index', ['tab' => 'config']) }}"><i class="fas fa-cog"></i> Configuración</a></li>
</ul>
<div class="tab-content border border-top-0 bg-white p-3 mb-3">
@if($tab === 'log')
    @include('backend.social-publications.partials.log')
@elseif($tab === 'manual')
    @include('backend.social-publications.partials.manual')
@else
    @include('backend.social-publications.partials.config')
@endif
</div>
@stop
