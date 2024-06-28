<p>Se ha agregado un/a Album en el portal:</p>
<p><strong>Álbum:</strong> {{ $details['album'] }}</p>
<p><strong>Foto:</strong></p>
<img src="{{ asset('storage/' . $details['foto']) }}" alt="Foto de {{ $details['album'] }}">
<p><strong>Año:</strong> {{ $details['anio'] }}</p>
<p><strong>Intérprete:</strong> {{ $details['interprete'] }}</p>
<p><strong>Usuario:</strong> {{ $details['user'] }}</p>
