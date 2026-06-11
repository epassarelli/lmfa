<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="participant_1_id">Participante 1</label>
      <select name="participant_1_id" id="participant_1_id" class="form-control" required>
        @foreach($participantOptions as $participantOption)
          <option value="{{ $participantOption->id }}" {{ (string) old('participant_1_id', $match->participant_1_id) === (string) $participantOption->id ? 'selected' : '' }}>
            {{ $participantOption->display_name }}
          </option>
        @endforeach
      </select>
      @error('participant_1_id')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="participant_2_id">Participante 2</label>
      <select name="participant_2_id" id="participant_2_id" class="form-control" required>
        @foreach($participantOptions as $participantOption)
          <option value="{{ $participantOption->id }}" {{ (string) old('participant_2_id', $match->participant_2_id) === (string) $participantOption->id ? 'selected' : '' }}>
            {{ $participantOption->display_name }}
          </option>
        @endforeach
      </select>
      @error('participant_2_id')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label>{{ $match->participant1?->display_name }}</label>
      <input type="number" min="0" name="participant_1_votes" class="form-control"
        value="{{ old('participant_1_votes', $match->participant_1_votes) }}" required>
      @error('participant_1_votes')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label>{{ $match->participant2?->display_name }}</label>
      <input type="number" min="0" name="participant_2_votes" class="form-control"
        value="{{ old('participant_2_votes', $match->participant_2_votes) }}" required>
      @error('participant_2_votes')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label for="status">Estado</label>
      <select name="status" id="status" class="form-control" required>
        @php $selectedStatus = old('status', $match->status); @endphp
        @foreach ([
          \App\Models\FolkloreTournamentMatch::STATUS_SCHEDULED => 'Programado',
          \App\Models\FolkloreTournamentMatch::STATUS_VOTING_OPEN => 'Votacion abierta',
          \App\Models\FolkloreTournamentMatch::STATUS_CLOSED => 'Cerrado',
          \App\Models\FolkloreTournamentMatch::STATUS_FINISHED => 'Finalizado',
          \App\Models\FolkloreTournamentMatch::STATUS_CANCELLED => 'Cancelado',
        ] as $value => $label)
          <option value="{{ $value }}" {{ $selectedStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
      @error('status')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="winner_participant_id">Ganador manual</label>
      <select name="winner_participant_id" id="winner_participant_id" class="form-control">
        <option value="">— Sin definir —</option>
        @foreach($winnerOptions as $winnerOption)
          <option value="{{ $winnerOption->id }}" {{ (string) old('winner_participant_id', $match->winner_participant_id) === (string) $winnerOption->id ? 'selected' : '' }}>
            {{ $winnerOption->display_name }}
          </option>
        @endforeach
      </select>
      @error('winner_participant_id')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="instagram_url">URL de Instagram</label>
      <input type="url" name="instagram_url" id="instagram_url" class="form-control"
        value="{{ old('instagram_url', $match->instagram_url) }}" placeholder="https://instagram.com/...">
      @error('instagram_url')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label for="scheduled_at">Fecha programada</label>
      <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control"
        value="{{ old('scheduled_at', $match->scheduled_at?->format('Y-m-d\TH:i')) }}">
      @error('scheduled_at')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="voting_opens_at">Apertura de votacion</label>
      <input type="datetime-local" name="voting_opens_at" id="voting_opens_at" class="form-control"
        value="{{ old('voting_opens_at', $match->voting_opens_at?->format('Y-m-d\TH:i')) }}">
      @error('voting_opens_at')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="voting_closes_at">Cierre de votacion</label>
      <input type="datetime-local" name="voting_closes_at" id="voting_closes_at" class="form-control"
        value="{{ old('voting_closes_at', $match->voting_closes_at?->format('Y-m-d\TH:i')) }}">
      @error('voting_closes_at')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>
</div>

<div class="form-group">
  <label for="notes">Notas</label>
  <textarea name="notes" id="notes" rows="4" class="form-control" placeholder="Observaciones internas o aclaraciones editoriales...">{{ old('notes', $match->notes) }}</textarea>
  @error('notes')
    <small class="text-danger">{{ $message }}</small>
  @enderror
</div>
