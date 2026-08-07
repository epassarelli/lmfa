@extends('adminlte::page')

@section('title', 'Editar Usuario')

@section('content_header')
  <h1><i class="fas fa-user-edit mr-2"></i>Editar Usuario: {{ $user->name }}</h1>
@stop

@section('content')
  <div class="row">
    <div class="col-md-10 mx-auto">
      @if (session('success'))
        <div class="alert alert-success">
          {{ session('success') }}
        </div>
      @endif

      @if (session('plain_text_token'))
        <div class="alert alert-warning">
          <strong>Copiá este bearer token ahora.</strong> Por seguridad no volverá a mostrarse.
          <textarea class="form-control mt-2" rows="3" readonly>{{ session('plain_text_token') }}</textarea>
        </div>
      @endif

      <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card card-outline card-warning">
          <div class="card-header">
            <h3 class="card-title">Información General</h3>
          </div>
          <div class="card-body">
            @include('backend.users._form')
          </div>
          <div class="card-footer text-right">
            <a href="{{ route('users.index') }}" class="btn btn-default mr-2">
              Cancelar
            </a>
            <button type="submit" class="btn btn-primary shadow-sm">
              <i class="fas fa-save mr-1"></i> Guardar Cambios
            </button>
          </div>
        </div>
      </form>

      <div class="card card-outline card-info">
        <div class="card-header">
          <h3 class="card-title">Bearer Tokens API</h3>
        </div>
        <div class="card-body">
          <form action="{{ route('users.api-tokens.store', $user->id) }}" method="POST" class="mb-4">
            @csrf
            <div class="form-group">
              <label for="token_name">Nombre del token</label>
              <input
                type="text"
                id="token_name"
                name="token_name"
                class="form-control"
                value="{{ old('token_name', 'admin-issued-' . now()->format('Ymd-His')) }}"
                required>
              @error('token_name')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>
            <button type="submit" class="btn btn-success">Generar bearer token</button>
          </form>

          <h4>Tokens existentes</h4>

          @if ($apiTokens->isEmpty())
            <p class="text-muted mb-0">Este usuario no tiene tokens API activos.</p>
          @else
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Nombre</th>
                    <th>Permisos</th>
                    <th>Ultimo uso</th>
                    <th>Creado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($apiTokens as $token)
                    <tr>
                      <td>{{ $token->name }}</td>
                      <td>{{ $token->abilities ? implode(', ', $token->abilities) : '*' }}</td>
                      <td>{{ $token->last_used_at ? $token->last_used_at->format('d/m/Y H:i') : 'Nunca' }}</td>
                      <td>{{ $token->created_at ? $token->created_at->format('d/m/Y H:i') : '-' }}</td>
                      <td class="text-right">
                        <form action="{{ route('users.api-tokens.destroy', ['user' => $user->id, 'token' => $token->id]) }}" method="POST" style="display:inline-block;">
                          @csrf
                          @method('DELETE')
                          <button
                            type="submit"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('¿Seguro que querés revocar este bearer token?')">
                            Revocar
                          </button>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
