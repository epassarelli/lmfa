@php
  $field = fn (string $name, $default = null) => old($name, $organization->{$name} ?? $default);
@endphp

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
  <div class="col-md-4">
    <div class="form-group">
      <label for="type">Tipo *</label>
      <select name="type" id="type" class="form-control" required>
        @foreach (config('entity_management.organization_types') as $value => $label)
          <option value="{{ $value }}" @selected($field('type') === $value)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="col-md-5">
    <div class="form-group">
      <label for="name">Nombre *</label>
      <input type="text" name="name" id="name" class="form-control" value="{{ $field('name') }}" required maxlength="255">
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <label for="status">Estado *</label>
      <select name="status" id="status" class="form-control" required>
        <option value="active" @selected($field('status', 'active') === 'active')>Activa</option>
        <option value="inactive" @selected($field('status') === 'inactive')>Inactiva</option>
      </select>
      <small class="text-muted">Inactiva revoca los permisos de sus miembros sobre las fichas.</small>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="slug">Slug</label>
      <input type="text" name="slug" id="slug" class="form-control" value="{{ $field('slug') }}" maxlength="255" placeholder="Se genera desde el nombre si queda vacío">
    </div>
  </div>
  <div class="col-md-6">
    <div class="form-group">
      <label for="legal_name">Razón social</label>
      <input type="text" name="legal_name" id="legal_name" class="form-control" value="{{ $field('legal_name') }}" maxlength="255">
    </div>
  </div>
</div>

<h5 class="mt-2">Contacto <small class="text-muted">(interno; no se muestra en el sitio público)</small></h5>
<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" name="email" id="email" class="form-control" value="{{ $field('email') }}" maxlength="255">
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="phone">Teléfono / WhatsApp</label>
      <input type="text" name="phone" id="phone" class="form-control" value="{{ $field('phone') }}" maxlength="50">
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="website">Sitio web</label>
      <input type="url" name="website" id="website" class="form-control" value="{{ $field('website') }}" maxlength="255" placeholder="https://">
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label for="province_id">Provincia</label>
      <select name="province_id" id="province_id" class="form-control select2" data-placeholder="Sin provincia">
        <option value="">Sin provincia</option>
        @foreach ($provincias as $provincia)
          <option value="{{ $provincia->id }}" @selected((int) $field('province_id', 0) === (int) $provincia->id)>{{ $provincia->nombre }}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="city">Ciudad</label>
      <input type="text" name="city" id="city" class="form-control" value="{{ $field('city') }}" maxlength="255">
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="address">Dirección</label>
      <input type="text" name="address" id="address" class="form-control" value="{{ $field('address') }}" maxlength="255">
    </div>
  </div>
</div>

<h5 class="mt-2">Consentimiento y verificación</h5>
<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label for="source">Origen del contacto</label>
      <select name="source" id="source" class="form-control">
        <option value="">Sin dato</option>
        @foreach (config('entity_management.sources') as $source)
          <option value="{{ $source }}" @selected($field('source') === $source)>{{ $source }}</option>
        @endforeach
      </select>
    </div>
    <div class="custom-control custom-checkbox mb-2">
      <input type="hidden" name="contact_consent" value="0">
      <input type="checkbox" class="custom-control-input" id="contact_consent" name="contact_consent" value="1"
        @checked(old('contact_consent', $organization->contact_consent_at ? '1' : '0') == '1')>
      <label class="custom-control-label" for="contact_consent">Aceptó recibir comunicaciones</label>
      @if ($organization->contact_consent_at)
        <small class="d-block text-muted">Desde {{ $organization->contact_consent_at->format('d/m/Y') }}</small>
      @endif
    </div>
    <div class="custom-control custom-checkbox">
      <input type="hidden" name="unsubscribed" value="0">
      <input type="checkbox" class="custom-control-input" id="unsubscribed" name="unsubscribed" value="1"
        @checked(old('unsubscribed', $organization->unsubscribed_at ? '1' : '0') == '1')>
      <label class="custom-control-label" for="unsubscribed">Se dio de baja</label>
      @if ($organization->unsubscribed_at)
        <small class="d-block text-muted">Desde {{ $organization->unsubscribed_at->format('d/m/Y') }}</small>
      @endif
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="contact_consent_purpose">Finalidad del consentimiento</label>
      <input type="text" name="contact_consent_purpose" id="contact_consent_purpose" class="form-control" value="{{ $field('contact_consent_purpose') }}" maxlength="100" placeholder="Ej.: novedades y convocatorias">
    </div>
  </div>
  <div class="col-md-4">
    <div class="custom-control custom-checkbox mb-2">
      <input type="hidden" name="is_verified" value="0">
      <input type="checkbox" class="custom-control-input" id="is_verified" name="is_verified" value="1"
        @checked(old('is_verified', $organization->is_verified ? '1' : '0') == '1')>
      <label class="custom-control-label" for="is_verified">Verificada</label>
      @if ($organization->verified_at)
        <small class="d-block text-muted">El {{ $organization->verified_at->format('d/m/Y') }}</small>
      @endif
    </div>
    <div class="form-group">
      <label for="verification_method">Método de verificación</label>
      <select name="verification_method" id="verification_method" class="form-control">
        <option value="">Sin dato</option>
        @foreach (config('entity_management.verification_methods') as $method)
          <option value="{{ $method }}" @selected($field('verification_method') === $method)>{{ $method }}</option>
        @endforeach
      </select>
    </div>
  </div>
</div>
