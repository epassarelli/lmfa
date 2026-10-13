@extends('adminlte::page')

@section('metaTitle', 'Mis datos')

@section('content_header')
  <h1>Mis datos</h1>
@stop

@section('content')
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="row">
    {{-- Columna izquierda: identidad y resumen --}}
    <div class="col-lg-4">
      <div class="card card-outline card-primary shadow-sm">
        <div class="card-body text-center">
          <img src="{{ $user->avatarUrl() }}" alt="Avatar de {{ $user->name }}" class="img-circle elevation-2 mb-2" width="96" height="96">
          <h4 class="mb-0">{{ $user->name }}</h4>
          <p class="text-muted mb-2">{{ $user->email }}</p>
          <span class="badge badge-primary">{{ $user->roleLabel() }}</span>
        </div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Miembro desde</span><span>{{ optional($user->created_at)->format('d/m/Y') ?? '—' }}</span></li>
          <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Último ingreso</span><span>{{ optional($user->last_login_at)->format('d/m/Y H:i') ?? '—' }}</span></li>
        </ul>
      </div>

      <div class="card card-outline card-info shadow-sm">
        <div class="card-header"><h3 class="card-title">Mis fichas</h3></div>
        <div class="card-body">
          @if (count($managed))
            <ul class="list-unstyled mb-3">
              @foreach ($managed as $item)
                <li class="mb-1">
                  <span class="badge badge-light">{{ $item['label'] }}</span>
                  <a href="{{ $item['edit_url'] }}">{{ $item['name'] }}</a>
                </li>
              @endforeach
            </ul>
          @else
            <p class="text-muted">Todavía no administrás ninguna ficha.</p>
          @endif
          <div class="small text-muted mb-2">
            Mis solicitudes: {{ $claims['pending'] }} pendientes · {{ $claims['approved'] }} aprobadas · {{ $claims['rejected'] }} rechazadas
          </div>
          @if ($claims['to_review'] > 0)
            <div class="alert alert-warning py-1 px-2 small mb-2">Tenés {{ $claims['to_review'] }} solicitud(es) para revisar en tus fichas.</div>
          @endif
          <a href="{{ route('backend.my-entities.index') }}" class="btn btn-outline-primary btn-sm">Ir a Mis fichas</a>
        </div>
      </div>
    </div>

    {{-- Columna derecha: formularios y contenido --}}
    <div class="col-lg-8">
      <div class="card card-outline card-success shadow-sm">
        <div class="card-header"><h3 class="card-title">Datos personales</h3></div>
        <form action="{{ route('backend.account.update') }}" method="POST">
          @csrf
          @method('PUT')
          <div class="card-body">
            @if ($errors->profile->any())
              <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->profile->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <div class="form-group">
              <label for="name">Nombre</label>
              <input type="text" name="name" id="name" class="form-control" maxlength="255" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-group">
              <label for="phone">Teléfono o WhatsApp (opcional)</label>
              <input type="text" name="phone" id="phone" class="form-control" maxlength="50" value="{{ old('phone', $user->phone) }}">
            </div>
            <div class="form-group mb-0">
              <label for="email">Email</label>
              <input type="email" id="email" class="form-control" value="{{ $user->email }}" readonly disabled>
              <small class="text-muted">Para cambiar tu email, <a href="{{ url('/contacto') }}">escribinos</a>.</small>
            </div>
          </div>
          <div class="card-footer"><button type="submit" class="btn btn-success">Guardar datos</button></div>
        </form>
      </div>

      <div class="card card-outline card-warning shadow-sm">
        <div class="card-header"><h3 class="card-title">Cambiar contraseña</h3></div>
        <form action="{{ route('backend.account.password') }}" method="POST" autocomplete="off">
          @csrf
          @method('PUT')
          <div class="card-body">
            @if ($errors->password->any())
              <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->password->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <div class="form-group">
              <label for="current_password">Contraseña actual</label>
              <input type="password" name="current_password" id="current_password" class="form-control" autocomplete="current-password" required>
              @if ($user->google_id || $user->facebook_id)
                <small class="text-muted">Si ingresaste con Google o Facebook y no conocés tu contraseña, usá <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>.</small>
              @endif
            </div>
            <div class="form-group">
              <label for="password">Contraseña nueva (mínimo {{ \App\Support\UserRules::PASSWORD_MIN }} caracteres)</label>
              <input type="password" name="password" id="password" class="form-control" minlength="{{ \App\Support\UserRules::PASSWORD_MIN }}" autocomplete="new-password" required>
            </div>
            <div class="form-group mb-0">
              <label for="password_confirmation">Repetir contraseña nueva</label>
              <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password" required>
            </div>
          </div>
          <div class="card-footer"><button type="submit" class="btn btn-warning">Cambiar contraseña</button></div>
        </form>
      </div>

      <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header"><h3 class="card-title">Mi contenido</h3></div>
        <div class="card-body p-0">
          @if (count($content))
            <table class="table table-sm mb-0">
              <thead><tr><th>Tipo</th><th class="text-right">Total</th><th class="text-right">Publicados</th><th class="text-right">En revisión</th></tr></thead>
              <tbody>
                @foreach ($content as $row)
                  <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="text-right">{{ $row['total'] }}</td>
                    <td class="text-right"><span class="badge badge-success">{{ $row['published'] }}</span></td>
                    <td class="text-right"><span class="badge badge-warning">{{ $row['review'] }}</span></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @else
            <p class="text-muted p-3 mb-0">Todavía no cargaste contenido.</p>
          @endif
        </div>
      </div>

      <div class="card card-outline card-info shadow-sm">
        <div class="card-header"><h3 class="card-title">Newsletter</h3></div>
        @php($subscribed = $newsletter && $newsletter->isDeliverable())
        @php($awaiting = $newsletter && $newsletter->isUnconfirmed())
        <form action="{{ route('backend.account.newsletter') }}" method="POST">
          @csrf
          @method('PUT')
          <div class="card-body">
            <p class="mb-2">
              Resumen semanal del portal a <strong>{{ $user->email }}</strong>:
              @if ($subscribed)
                <span class="badge badge-success">Suscripto</span>
              @elseif ($awaiting)
                <span class="badge badge-warning">Falta confirmar</span>
              @else
                <span class="badge badge-secondary">No suscripto</span>
              @endif
            </p>
            @if ($awaiting)
              <p class="small text-muted mb-0">Te enviamos un correo con un botón para confirmar. Revisá también la carpeta de spam. Hasta que lo confirmes no recibís el newsletter.</p>
            @endif
            <input type="hidden" name="subscribe" value="{{ $subscribed ? 0 : 1 }}">
          </div>
          <div class="card-footer">
            <button type="submit" class="btn {{ $subscribed ? 'btn-outline-secondary' : 'btn-info' }}">{{ $subscribed ? 'Darme de baja' : ($awaiting ? 'Reenviar el correo de confirmación' : 'Suscribirme') }}</button>
          </div>
        </form>
      </div>

      @if ($sessionsEnabled)
        <div class="card card-outline card-primary shadow-sm" id="sessions">
          <div class="card-header"><h3 class="card-title">Sesiones activas</h3></div>
          <form action="{{ route('backend.account.sessions.logout-others') }}" method="POST" autocomplete="off">
            @csrf
            <div class="card-body">
              @if ($errors->sessions->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->sessions->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
              @endif
              <ul class="list-group mb-3">
                @foreach ($sessions as $session)
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                      <i class="fas fa-fw fa-laptop text-muted"></i> {{ $session['device'] }}
                      @if ($session['current']) <span class="badge badge-success ml-1">Esta sesión</span> @endif
                      <small class="d-block text-muted">{{ $session['ip'] ?? 'IP desconocida' }} · última actividad {{ $session['last_active']->diffForHumans() }}</small>
                    </span>
                  </li>
                @endforeach
              </ul>
              <p class="small text-muted">Si no reconocés alguna sesión, cerrala y cambiá tu contraseña. Cerrar las otras sesiones no cierra esta.</p>
              <div class="form-group mb-0">
                <label for="sessions_current_password">Contraseña actual</label>
                <input type="password" name="current_password" id="sessions_current_password" class="form-control" autocomplete="current-password" required>
              </div>
            </div>
            <div class="card-footer"><button type="submit" class="btn btn-outline-primary">Cerrar las otras sesiones</button></div>
          </form>
        </div>
      @endif

      <div class="card card-outline card-danger shadow-sm" id="delete-account">
        <div class="card-header"><h3 class="card-title">Eliminar mi cuenta</h3></div>
        @if (! $canDelete)
          <div class="card-body"><p class="mb-0 text-muted">Las cuentas de administrador no se pueden eliminar desde acá: lo hace otro administrador.</p></div>
        @else
          <form action="{{ route('backend.account.destroy') }}" method="POST" autocomplete="off">
            @csrf
            @method('DELETE')
            <div class="card-body">
              @if ($errors->delete->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->delete->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
              @endif
              <p>Esta acción es <strong>definitiva</strong> y no se puede deshacer.</p>
              <ul class="small">
                <li><strong>Se borra:</strong> tu acceso, tus datos personales (nombre, email, teléfono), tus vínculos con fichas, tus solicitudes pendientes, tu suscripción al newsletter, tus mensajes de contacto y tus sesiones.</li>
                <li><strong>Se conserva:</strong> el contenido que cargaste (discos, canciones, noticias, eventos, etc.) sigue en el portal, pero sin tu nombre.</li>
              </ul>
              @if ($soleOwnerFichas > 0)
                <div class="alert alert-warning small">Sos el único responsable de {{ $soleOwnerFichas }} ficha(s): van a quedar sin responsable hasta que alguien la reclame de nuevo.</div>
              @endif
              <div class="form-group">
                <label for="delete_current_password">Contraseña actual</label>
                <input type="password" name="current_password" id="delete_current_password" class="form-control" autocomplete="current-password" required>
              </div>
              <div class="form-group mb-0">
                <label for="delete_confirmation">Para confirmar, escribí <code>{{ $deleteConfirmation }}</code></label>
                <input type="text" name="confirmation" id="delete_confirmation" class="form-control" autocomplete="off" required>
              </div>
            </div>
            <div class="card-footer"><button type="submit" class="btn btn-danger">Eliminar mi cuenta para siempre</button></div>
          </form>
        @endif
      </div>
    </div>
  </div>
@stop
