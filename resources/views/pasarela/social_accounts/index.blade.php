@extends('adminlte::page')

@section('title', 'Mis Cuentas Sociales')

@section('content_header')
    <h1>
        <i class="fas fa-share-alt mr-2"></i>
        Cuentas Sociales Conectadas
    </h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- Mensajes flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">

        {{-- =========================================================
             LISTADO DE CUENTAS CONECTADAS
        ========================================================== --}}
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plug mr-1"></i> Cuentas conectadas
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($accounts->isEmpty())
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-unlink fa-2x mb-2 d-block"></i>
                            Todavía no conectaste ninguna cuenta social.
                        </div>
                    @else
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Red</th>
                                    <th>Nombre de cuenta</th>
                                    <th>Perfil / Página</th>
                                    <th>Estado</th>
                                    <th>Expira</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accounts as $account)
                                <tr>
                                    <td>
                                        @switch($account->provider)
                                            @case('facebook')
                                                <span class="badge badge-primary">
                                                    <i class="fab fa-facebook mr-1"></i>Facebook
                                                </span>
                                                @break
                                            @case('instagram')
                                                <span class="badge badge-danger">
                                                    <i class="fab fa-instagram mr-1"></i>Instagram
                                                </span>
                                                @break
                                            @case('telegram')
                                                <span class="badge badge-info">
                                                    <i class="fab fa-telegram mr-1"></i>Telegram
                                                </span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ $account->provider }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $account->account_name }}</td>
                                    <td>{{ $account->page_or_profile_name ?? '—' }}</td>
                                    <td>
                                        @if($account->status === 'active')
                                            <span class="badge badge-success">Activa</span>
                                        @elseif($account->status === 'expired')
                                            <span class="badge badge-warning">Expirada</span>
                                        @else
                                            <span class="badge badge-secondary">Desconectada</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($account->token_expires_at)
                                            {{ $account->token_expires_at->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('pasarela.social-accounts.destroy', $account) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Desconectar esta cuenta? Las publicaciones pendientes podrían fallar.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-unlink"></i> Desconectar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        {{-- =========================================================
             FORMULARIO CONECTAR NUEVA CUENTA
        ========================================================== --}}
        <div class="col-lg-4">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle mr-1"></i> Conectar nueva cuenta
                    </h3>
                </div>
                <div class="card-body">

                    <div class="alert alert-warning py-2 small">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>MVP manual:</strong> ingresá el token de acceso que generaste desde la
                        plataforma (Meta Developers, BotFather de Telegram, etc.).
                        En una fase siguiente esto se reemplaza por flujo OAuth.
                    </div>

                    <form action="{{ route('pasarela.social-accounts.store') }}" method="POST">
                        @csrf

                        {{-- Proveedor --}}
                        <div class="form-group">
                            <label for="provider">Red social <span class="text-danger">*</span></label>
                            <select name="provider" id="provider"
                                    class="form-control @error('provider') is-invalid @enderror"
                                    required>
                                <option value="">-- Seleccioná --</option>
                                @foreach($providers as $key => $label)
                                    <option value="{{ $key }}" {{ old('provider') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('provider')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nombre de cuenta --}}
                        <div class="form-group">
                            <label for="account_name">Nombre de usuario / handle <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" id="account_name"
                                   class="form-control @error('account_name') is-invalid @enderror"
                                   value="{{ old('account_name') }}"
                                   placeholder="@mi_cuenta"
                                   required>
                            @error('account_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ID externo --}}
                        <div class="form-group">
                            <label for="account_external_id">ID externo (de la plataforma) <span class="text-danger">*</span></label>
                            <input type="text" name="account_external_id" id="account_external_id"
                                   class="form-control @error('account_external_id') is-invalid @enderror"
                                   value="{{ old('account_external_id') }}"
                                   placeholder="123456789"
                                   required>
                            <small class="form-text text-muted">
                                Facebook/Instagram: Page ID o User ID. Telegram: Chat ID.
                            </small>
                            @error('account_external_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nombre de página / perfil --}}
                        <div class="form-group">
                            <label for="page_or_profile_name">Nombre de página / perfil</label>
                            <input type="text" name="page_or_profile_name" id="page_or_profile_name"
                                   class="form-control @error('page_or_profile_name') is-invalid @enderror"
                                   value="{{ old('page_or_profile_name') }}"
                                   placeholder="Mi Folklore Argentino">
                            @error('page_or_profile_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Token --}}
                        <div class="form-group">
                            <label for="token">Token de acceso <span class="text-danger">*</span></label>
                            <textarea name="token" id="token" rows="3"
                                      class="form-control @error('token') is-invalid @enderror"
                                      placeholder="Pegá aquí el access token..."
                                      required>{{ old('token') }}</textarea>
                            <small class="form-text text-muted">
                                El token se almacena cifrado. Nunca se muestra en claro.
                            </small>
                            @error('token')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Scopes (opcional) --}}
                        <div class="form-group">
                            <label for="scopes">Permisos / scopes (separados por coma)</label>
                            <input type="text" name="scopes" id="scopes"
                                   class="form-control @error('scopes') is-invalid @enderror"
                                   value="{{ old('scopes') }}"
                                   placeholder="pages_manage_posts, publish_to_groups">
                            @error('scopes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-link mr-1"></i> Conectar cuenta
                        </button>
                    </form>
                </div>
            </div>

            {{-- Link al dashboard --}}
            <a href="{{ route('pasarela.dashboard') }}" class="btn btn-outline-secondary btn-block">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Panel del Publicador
            </a>
        </div>

    </div>
</div>
@stop

@section('css')
<style>
    .table td { vertical-align: middle; }
</style>
@stop
