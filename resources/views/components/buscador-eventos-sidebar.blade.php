<div>
  <!-- The only way to do great work is to love what you do. - Steve Jobs -->
  <div class="bg-white p-4 rounded-2xl shadow-sm mb-6">
    <h3 class="text-md font-semibold text-gray-800 mb-3 border-b pb-1 border-b-2 border-[#ff661f]">
      Buscar Eventos
    </h3>

    <form method="GET" action="{{ route('cartelera.index') }}" class="space-y-3">
      <!-- Artista -->
      <div>
        <label for="artista" class="form-label small text-muted">Artista</label>
        <select class="form-select form-select-sm" name="artista" id="artista">
          <option value="">Todos</option>
          @foreach ($artistas as $artista)
            <option value="{{ $artista->id }}" {{ request('artista') == $artista->id ? 'selected' : '' }}>
              {{ $artista->nombre }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Provincia -->
      <div>
        <label for="provincia" class="form-label small text-muted">Provincia</label>
        <select class="form-select form-select-sm" name="provincia" id="provincia">
          <option value="">Todas</option>
          @foreach ($provincias as $provincia)
            <option value="{{ $provincia->id }}" {{ request('provincia') == $provincia->id ? 'selected' : '' }}>
              {{ $provincia->nombre }}
            </option>
          @endforeach
        </select>
      </div>

      <!-- Mes -->
      <div>
        <label for="mes" class="form-label small text-muted">Mes</label>
        <select class="form-select form-select-sm" name="mes" id="mes">
          <option value="">Todos</option>
          @foreach ([
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre',
    ] as $num => $nombre)
            <option value="{{ $num }}" {{ request('mes') == $num ? 'selected' : '' }}>
              {{ $nombre }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="pt-2">
        <button type="submit" class="btn btn-sm btn-outline-primary w-100"
          style="border-color: #ff661f; color: #ff661f;">
          <i class="fas fa-search me-1"></i> Buscar
        </button>
      </div>
    </form>
  </div>

</div>
