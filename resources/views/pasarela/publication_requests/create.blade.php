@extends('adminlte::page')

@section('title', 'Solicitar Publicación')

@section('content_header')
    <h1>
        <i class="fas fa-paper-plane mr-2"></i>
        Solicitar Publicación
    </h1>
@stop

@section('content')
<div class="container-fluid">

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i>
            <strong>Revisá los errores:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-alt mr-1"></i>
                Contenido:
                <strong>{{ $content->title }}</strong>
                <span class="badge badge-secondary ml-2">{{ ucfirst($contentType) }}</span>
            </h3>
        </div>

        <form action="{{ route('pasarela.publication-requests.store') }}" method="POST">
            @csrf

            <input type="hidden" name="content_type" value="{{ $contentType }}">
            <input type="hidden" name="content_id"   value="{{ $contentId }}">

            <div class="card-body">

                {{-- Modo de publicación --}}
                <div class="form-group">
                    <label class="font-weight-bold">
                        <i class="fas fa-sliders-h mr-1"></i> Modo de publicación
                    </label>
                    <select name="mode" class="form-control @error('mode') is-invalid @enderror" required>
                        <option value="portal_only"  {{ old('mode') === 'portal_only'  ? 'selected' : '' }}>Solo portal</option>
                        <option value="social_only"  {{ old('mode') === 'social_only'  ? 'selected' : '' }}>Solo redes sociales</option>
                        <option value="full"         {{ old('mode') === 'full'         ? 'selected' : '' }}>Portal + redes sociales</option>
                    </select>
                    @error('mode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Canales destino --}}
                <div class="form-group">
                    <label class="font-weight-bold">
                        <i class="fas fa-broadcast-tower mr-1"></i> Canales destino
                    </label>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="wants_portal_publish"
                               name="wants_portal_publish" value="1"
                               {{ old('wants_portal_publish', true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="wants_portal_publish">
                            Publicar en el portal Mi Folklore Argentino
                        </label>
                    </div>

                    <div class="custom-control custom-checkbox mt-1">
                        <input type="checkbox" class="custom-control-input" id="wants_own_social"
                               name="wants_own_social" value="1"
                               {{ old('wants_own_social') ? 'checked' : '' }}
                               {{ $socialAccounts->isEmpty() ? 'disabled' : '' }}>
                        <label class="custom-control-label" for="wants_own_social">
                            Publicar en mis redes sociales conectadas
                            @if ($socialAccounts->isEmpty())
                                <small class="text-muted">(no tenés cuentas conectadas)</small>
                            @else
                                <small class="text-muted">({{ $socialAccounts->count() }} cuenta/s activa/s)</small>
                            @endif
                        </label>
                    </div>

                    <div class="custom-control custom-checkbox mt-1">
                        <input type="checkbox" class="custom-control-input" id="wants_portal_social"
                               name="wants_portal_social" value="1"
                               {{ old('wants_portal_social') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="wants_portal_social">
                            Publicar en las redes sociales del portal
                        </label>
                    </div>
                </div>

                {{-- Cuentas conectadas (informativo) --}}
                @if ($socialAccounts->isNotEmpty())
                    <div class="alert alert-info py-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Tus cuentas activas:</strong>
                        @foreach ($socialAccounts as $account)
                            <span class="badge badge-secondary ml-1">
                                <i class="fab fa-{{ $account->provider }} mr-1"></i>
                                {{ $account->account_name }}
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- Programación --}}
                <div class="form-group">
                    <label for="scheduled_at" class="font-weight-bold">
                        <i class="fas fa-calendar-alt mr-1"></i> Programar publicación (opcional)
                    </label>
                    <input type="datetime-local" id="scheduled_at" name="scheduled_at"
                           class="form-control @error('scheduled_at') is-invalid @enderror"
                           value="{{ old('scheduled_at') }}">
                    <small class="form-text text-muted">
                        Dejalo vacío para publicar de inmediato.
                    </small>
                    @error('scheduled_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane mr-1"></i> Enviar solicitud de publicación
                </button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary ml-2">
                    <i class="fas fa-arrow-left mr-1"></i> Cancelar
                </a>
            </div>
        </form>
    </div>

</div>
@stop
