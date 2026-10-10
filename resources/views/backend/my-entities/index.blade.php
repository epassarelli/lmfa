@extends('adminlte::page')

@section('metaTitle', 'Mis fichas')

@section('content_header')
  <h1>Mis fichas</h1>
@stop

@php
  $statusBadges = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary'];
  $statusLabels = \App\Models\EntityClaimRequest::statusLabels();
@endphp

@section('content')
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="row">
    <div class="col-lg-6">
      <div class="card card-outline card-primary shadow-sm">
        <div class="card-header"><h3 class="card-title">Fichas que administro</h3></div>
        <div class="card-body">
          @if (count($managed))
            <table class="table table-sm mb-0">
              <tbody>
                @foreach ($managed as $item)
                  <tr>
                    <td><span class="badge badge-light">{{ $item['label'] }}</span> {{ $item['name'] }}</td>
                    <td class="text-right">
                      <a href="{{ $item['edit_url'] }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @else
            <p class="text-muted mb-0">Todavía no administrás ninguna ficha. Podés pedir administrar la tuya con el formulario.</p>
          @endif
        </div>
      </div>

      <div class="card card-outline card-info shadow-sm">
        <div class="card-header"><h3 class="card-title">Mis solicitudes</h3></div>
        <div class="card-body">
          @forelse ($claims as $claim)
            <div class="border-bottom pb-2 mb-2">
              <div class="d-flex justify-content-between">
                <div>
                  <span class="badge badge-light">{{ $claim->entityConfig()['label'] ?? 'Ficha' }}</span>
                  <strong>{{ $claim->entityName() ?? 'Ficha #'.$claim->entity_id }}</strong>
                  <div class="small text-muted">{{ $claim->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="text-right">
                  <span class="badge badge-{{ $statusBadges[$claim->status] ?? 'secondary' }}">{{ $statusLabels[$claim->status] ?? $claim->status }}</span>
                  @if ($claim->isPending())
                    <form action="{{ route('backend.my-entities.claims.cancel', $claim) }}" method="POST" class="mt-1" onsubmit="return confirm('¿Cancelar esta solicitud?')">
                      @csrf
                      <button class="btn btn-outline-danger btn-xs">Cancelar</button>
                    </form>
                  @endif
                </div>
              </div>
              @if ($claim->review_notes)
                <div class="small mt-1"><strong>Respuesta:</strong> {{ $claim->review_notes }}</div>
              @endif
            </div>
          @empty
            <p class="text-muted mb-0">No enviaste solicitudes.</p>
          @endforelse
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card card-outline card-success shadow-sm">
        <div class="card-header"><h3 class="card-title">Solicitar administrar una ficha</h3></div>
        <form action="{{ route('backend.my-entities.claims.store') }}" method="POST">
          @csrf
          <div class="card-body">
            <p class="text-muted small">Buscá tu artista o festival. Revisamos cada solicitud antes de darte acceso para editar la ficha.</p>
            <div class="form-group">
              <label for="entity_type">Tipo de ficha</label>
              <select name="entity_type" id="entity_type" class="form-control">
                @foreach (config('entity_management.entities') as $key => $cfg)
                  <option value="{{ $key }}" @selected(old('entity_type', $prefill['type'] ?? 'interprete') === $key)>{{ $cfg['label'] }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label for="entity_id">Ficha</label>
              <select name="entity_id" id="entity_id" class="form-control" required>
                @if ($prefill)
                  <option value="{{ $prefill['id'] }}" selected>{{ $prefill['text'] }}</option>
                @endif
              </select>
            </div>
            <div class="form-group">
              <label for="relationship">Tu relación con la ficha</label>
              <select name="relationship" id="relationship" class="form-control" required>
                @foreach (config('entity_management.claims.relationships') as $value => $label)
                  <option value="{{ $value }}" @selected(old('relationship') === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label for="message">Contanos quién sos y cómo podemos verificarlo</label>
              <textarea name="message" id="message" rows="4" class="form-control" minlength="10" maxlength="1000" required
                placeholder="Ej.: Soy la cantante del grupo. Pueden confirmarlo escribiendo al mail de contacto o a nuestro Instagram.">{{ old('message') }}</textarea>
            </div>
            <div class="form-group">
              <label for="contact_phone">Teléfono o WhatsApp (opcional)</label>
              <input type="text" name="contact_phone" id="contact_phone" class="form-control" maxlength="50" value="{{ old('contact_phone') }}">
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane mr-1"></i> Enviar solicitud</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@stop

@section('js')
  @include('backend.partials.scripts._select2')
  <script>
    window.addEventListener('load', function () {
      $('#entity_id').select2({
        theme: 'classic',
        width: '100%',
        placeholder: 'Escribí al menos 2 letras del nombre',
        minimumInputLength: 2,
        language: {
          inputTooShort: function () { return 'Escribí al menos 2 letras'; },
          noResults: function () { return 'Sin resultados'; },
          searching: function () { return 'Buscando...'; }
        },
        ajax: {
          url: @json(route('backend.my-entities.search')),
          dataType: 'json',
          delay: 250,
          data: function (params) { return { q: params.term, type: $('#entity_type').val() }; }
        }
      });

      $('#entity_type').on('change', function () { $('#entity_id').val(null).trigger('change'); });
    });
  </script>
@stop
