<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nuevo mensaje de contacto</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #1f2937;">
    <h1 style="font-size: 20px;">Nuevo mensaje de contacto</h1>
    <p><strong>Nombre:</strong> {{ $contact['nombre'] }}</p>
    <p><strong>Email:</strong> {{ $contact['email'] }}</p>
    <p><strong>Asunto:</strong> {{ $contact['asunto'] }}</p>
    <p><strong>Mensaje:</strong></p>
    <p style="white-space: pre-line;">{{ $contact['mensaje'] }}</p>
    <p><strong>Fecha:</strong> {{ \Illuminate\Support\Carbon::parse($contact['fecha_envio'])->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
    <hr>
    <p style="font-size: 13px; color: #6b7280;">
        Podés responder a este correo: la respuesta le llega a quien escribió.
        Ver y gestionar el mensaje en el backoffice: <a href="{{ $adminUrl }}">{{ $adminUrl }}</a>
    </p>
</body>
</html>
