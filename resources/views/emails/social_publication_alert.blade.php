<!doctype html>
<html lang="es"><body style="font-family:Arial,sans-serif;color:#222">
<h2 style="font-size:18px">{{ $title }}</h2>
<ul>@foreach($lines as $line)<li>{{ $line }}</li>@endforeach</ul>
<p><a href="{{ $panelUrl }}">Abrir Publicaciones en redes</a></p>
<p style="color:#777;font-size:12px">Aviso automático de Mi Folklore Argentino. Hora: {{ now()->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}.</p>
</body></html>
