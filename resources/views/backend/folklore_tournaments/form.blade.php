<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="name">Nombre</label>
      <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $tournament->name) }}" required>
      @error('name')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <label for="year">Anio</label>
      <input type="number" name="year" id="year" class="form-control" value="{{ old('year', $tournament->year) }}" required>
      @error('year')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <label for="status">Estado</label>
      <select name="status" id="status" class="form-control" required>
        @foreach ([
          \App\Models\FolkloreTournament::STATUS_DRAFT => 'Draft',
          \App\Models\FolkloreTournament::STATUS_ACTIVE => 'Active',
          \App\Models\FolkloreTournament::STATUS_FINISHED => 'Finished',
          \App\Models\FolkloreTournament::STATUS_ARCHIVED => 'Archived',
        ] as $value => $label)
          <option value="{{ $value }}" {{ old('status', $tournament->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
      @error('status')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="slug">Slug</label>
      <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $tournament->slug) }}" required>
      @error('slug')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <label for="starts_at">Inicio</label>
      <input type="datetime-local" name="starts_at" id="starts_at" class="form-control" value="{{ old('starts_at', $tournament->starts_at?->format('Y-m-d\TH:i')) }}">
      @error('starts_at')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <label for="ends_at">Fin</label>
      <input type="datetime-local" name="ends_at" id="ends_at" class="form-control" value="{{ old('ends_at', $tournament->ends_at?->format('Y-m-d\TH:i')) }}">
      @error('ends_at')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>
</div>

<div class="form-group">
  <label for="description">Descripcion</label>
  <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $tournament->description) }}</textarea>
  @error('description')
    <small class="text-danger">{{ $message }}</small>
  @enderror
</div>

<div class="form-group">
  <label for="rules">Reglas</label>
  <textarea name="rules" id="rules" rows="4" class="form-control">{{ old('rules', $tournament->rules) }}</textarea>
  @error('rules')
    <small class="text-danger">{{ $message }}</small>
  @enderror
</div>

<div class="card card-outline card-secondary">
  <div class="card-header">
    <h3 class="card-title">Participantes y zonas</h3>
  </div>
  <div class="card-body">
    <p class="text-muted">
      Cada cupo del torneo debe quedar vinculado a un interprete real de la base. Si cambias interpretes o zonas, el fixture de grupos se regenera y vuelve a programarse a razon de un partido por dia desde manana.
    </p>

    <div class="row font-weight-bold mb-2 d-none d-md-flex">
      <div class="col-md-6">Interprete</div>
      <div class="col-md-4">Zona</div>
      <div class="col-md-2">Actual</div>
    </div>

    @foreach ($participants as $participant)
      <div class="row align-items-end mb-3">
        <div class="col-md-6">
          <div class="form-group mb-0">
            <label for="participant_artists_{{ $participant->id }}">Cupo {{ $participant->seed_order ?? $loop->iteration }}</label>
            <select
              name="participant_artists[{{ $participant->id }}]"
              id="participant_artists_{{ $participant->id }}"
              class="form-control folklore-interprete-select"
              required
            >
              @foreach ($interpretes as $interprete)
                <option value="{{ $interprete->id }}" {{ (string) old("participant_artists.$participant->id", $participant->artist_id) === (string) $interprete->id ? 'selected' : '' }}>
                  #{{ $interprete->id }} - {{ $interprete->interprete }}
                </option>
              @endforeach
            </select>
            @error("participant_artists.$participant->id")
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group mb-0">
            <label for="participant_groups_{{ $participant->id }}">Zona</label>
            <select name="participant_groups[{{ $participant->id }}]" id="participant_groups_{{ $participant->id }}" class="form-control" required>
              @foreach ($groups as $group)
                <option value="{{ $group->id }}" {{ (string) old("participant_groups.$participant->id", $participant->group_id) === (string) $group->id ? 'selected' : '' }}>
                  {{ $group->name }}
                </option>
              @endforeach
            </select>
            @error("participant_groups.$participant->id")
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>
        </div>

        <div class="col-md-2">
          <div class="small text-muted">ID: {{ $participant->artist_id ?? '-' }}</div>
          <div class="small text-muted">{{ $participant->display_name }}</div>
        </div>
      </div>
    @endforeach
  </div>
</div>
