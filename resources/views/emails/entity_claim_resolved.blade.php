<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $approved ? 'Solicitud aprobada' : 'Solicitud no aprobada' }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #1f2937;">
    <h1 style="font-size: 20px;">Hola {{ $userName }},</h1>

    @if ($approved)
        <p>
            @if ($isCreation)
                Aprobamos tu pedido de alta de <strong>{{ $entityName }}</strong>.
            @else
                Aprobamos tu solicitud para administrar <strong>{{ $entityName }}</strong>.
            @endif
            @if ($role)
                Tu rol es: <strong>{{ $role }}</strong>.
            @endif
        </p>
        @if ($notes)
            <p><strong>Nota del equipo:</strong> {{ $notes }}</p>
        @endif
        <p>Ya podés gestionarla desde tu panel: <a href="{{ $url }}">Mis fichas</a>. Todo lo que cargues se publica después de una revisión.</p>
    @else
        <p>
            @if ($isCreation)
                Revisamos tu pedido de alta de <strong>{{ $entityName }}</strong> y por ahora no pudimos aprobarlo.
            @else
                Revisamos tu solicitud para administrar <strong>{{ $entityName }}</strong> y por ahora no pudimos aprobarla.
            @endif
        </p>
        @if ($notes)
            <p><strong>Motivo:</strong> {{ $notes }}</p>
        @endif
        <p>Podés volver a intentarlo o escribirnos desde <a href="{{ $url }}">Mis fichas</a> y el formulario de contacto del sitio.</p>
    @endif

    <p>Saludos,<br>Mi Folklore Argentino</p>
    <hr>
    <p style="font-size: 12px; color: #6b7280;">Recibís este correo porque hiciste una solicitud sobre una ficha en mifolkloreargentino.com.</p>
</body>
</html>
