<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #ff661f; margin-bottom: 20px; padding-bottom: 10px; }
        .button { display: inline-block; background: #ff661f; color: #fff !important; text-decoration: none; font-weight: bold; padding: 12px 24px; border-radius: 6px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 0.85rem; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h2>Mi Folklore Argentino</h2></div>

        <p>Hola, recibimos un pedido para suscribir este correo a nuestro newsletter semanal con las novedades del folklore argentino.</p>
        <p>Para empezar a recibirlo, confirmá tu suscripción:</p>

        <p style="text-align:center; margin: 28px 0;">
            <a href="{{ $confirmUrl }}" class="button">Confirmar mi suscripción</a>
        </p>

        <p>Si el botón no funciona, copiá y pegá este enlace en tu navegador:<br>
            <a href="{{ $confirmUrl }}">{{ $confirmUrl }}</a></p>
        <p>El enlace vale {{ $days }} días.</p>

        <div class="footer">
            <p>Si no pediste esta suscripción, ignorá este mensaje: no vas a recibir nada y no hace falta que hagas algo.</p>
        </div>
    </div>
</body>
</html>
