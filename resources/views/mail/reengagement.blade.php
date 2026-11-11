<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #ff661f; margin-bottom: 20px; padding-bottom: 10px; }
        .button { display: inline-block; background: #ff661f; color: #fff !important; text-decoration: none; font-weight: bold; padding: 12px 24px; border-radius: 6px; }
        .button-secondary { display: inline-block; background: #fff; color: #ff661f !important; border: 2px solid #ff661f; text-decoration: none; font-weight: bold; padding: 10px 22px; border-radius: 6px; }
        .box { background: #fff7f2; border-left: 4px solid #ff661f; padding: 12px 16px; margin: 24px 0; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 0.85rem; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h2>Mi Folklore Argentino</h2></div>

        <p>Hola{{ filled($name) ? ' '.$name : '' }},</p>

        @if ($never)
            <p>Tu cuenta en <strong>Mi Folklore Argentino</strong> te está esperando. Te registraste en el portal pero todavía no la usaste, y queremos contarte todo lo que podés hacer con ella.</p>
        @else
            <p>En <strong>Mi Folklore Argentino te extrañamos</strong>. Hace tiempo que no pasás por el portal y queremos contarte qué podés hacer desde tu cuenta.</p>
        @endif

        <p><strong>Ser parte de la comunidad tiene ventajas:</strong></p>
        <ul>
            <li><strong>Gestión de contenidos:</strong> desde tu panel podés cargar y mantener información del folklore argentino (artistas, festivales, eventos y más), con revisión editorial del equipo.</li>
            <li><strong>Newsletter:</strong> recibís cada semana las novedades y la agenda de eventos del folklore, directo en tu correo.</li>
            <li><strong>Comunidad:</strong> sumás tu voz a un archivo vivo que cuida y difunde nuestra música, nuestras fiestas y nuestras tradiciones.</li>
        </ul>

        <p style="text-align:center; margin: 28px 0;">
            <a href="{{ $loginUrl }}" class="button">Ingresar a mi cuenta</a>
        </p>

        <div class="box">
            <p style="margin-top:0"><strong>¿No recordás tu contraseña?</strong></p>
            <p>Si antes entrabas con Facebook o con Google, ese acceso no está disponible por el momento. Podés crear una contraseña nueva con tu correo y volver a ingresar:</p>
            <p style="text-align:center; margin-bottom:0">
                <a href="{{ $resetUrl }}" class="button-secondary">Recuperar mi contraseña</a>
            </p>
        </div>

        <div class="footer">
            <p>Te escribimos porque tenés una cuenta en Mi Folklore Argentino. Si no querés recibir más este tipo de mensajes,
                <a href="{{ $unsubscribeUrl }}">podés darte de baja acá</a>. Esto no cambia tu suscripción al newsletter.</p>
        </div>
    </div>
</body>
</html>
