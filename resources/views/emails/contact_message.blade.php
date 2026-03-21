<!DOCTYPE html>
<html>
<head>
    <title>Nuevo Mensaje de Contacto</title>
</head>
<body>
    <h1>Nuevo Mensaje de Contacto</h1>
    <p><strong>Nombre:</strong> {{ $contact['nombre'] }}</p>
    <p><strong>Email:</strong> {{ $contact['email'] }}</p>
    <p><strong>Asunto:</strong> {{ $contact['asunto'] }}</p>
    <p><strong>Mensaje:</strong></p>
    <p>{{ $contact['mensaje'] }}</p>
    <p><strong>Fecha:</strong> {{ $contact['fecha_envio'] }}</p>
</body>
</html>
