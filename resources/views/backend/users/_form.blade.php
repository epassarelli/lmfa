<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="name">Nombre</label>
      <input
        type="text"
        id="name"
        name="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $user->name ?? '') }}"
        required>
      @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="email">Email</label>
      <input
        type="email"
        id="email"
        name="email"
        class="form-control @error('email') is-invalid @enderror"
        value="{{ old('email', $user->email ?? '') }}"
        required>
      @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="password">{{ isset($user) ? 'Contraseña nueva' : 'Contraseña' }}</label>
      <input
        type="password"
        id="password"
        name="password"
        class="form-control @error('password') is-invalid @enderror"
        {{ isset($user) ? '' : 'required' }}>
      @if (isset($user))
        <small class="form-text text-muted">Dejalo en blanco para conservar la contraseña actual.</small>
      @endif
      @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="password_confirmation">Confirmar contraseña</label>
      <input
        type="password"
        id="password_confirmation"
        name="password_confirmation"
        class="form-control @error('password_confirmation') is-invalid @enderror"
        {{ isset($user) ? '' : 'required' }}>
      @error('password_confirmation')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<div class="form-group mb-0">
  <label for="roles">Roles</label>
  <select
    id="roles"
    name="roles[]"
    class="form-control @error('roles') is-invalid @enderror"
    multiple
    required>
    @foreach ($roles as $role)
      <option value="{{ $role->name }}" {{ in_array($role->name, old('roles', $userRoles ?? []), true) ? 'selected' : '' }}>
        {{ $role->name }}
      </option>
    @endforeach
  </select>
  <small class="form-text text-muted">Usá Ctrl o Cmd para seleccionar más de un rol.</small>
  @error('roles')
    <div class="invalid-feedback d-block">{{ $message }}</div>
  @enderror
</div>
