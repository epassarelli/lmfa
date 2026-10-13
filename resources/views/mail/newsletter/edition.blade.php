<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $edition->subject }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #ff661f; margin-bottom: 20px; padding-bottom: 10px; }
        .section-title { font-size: 1.25rem; font-weight: bold; margin-top: 24px; color: #ff661f; }
        .item-title { font-weight: bold; color: #000; text-decoration: none; }
        .meta { color: #666; font-size: 0.9rem; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 0.85rem; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Novedades de la Semana</h2>
            <p>{{ $edition->period() }}</p>
        </div>

        @if (filled($edition->intro))
            <p>{{ $edition->intro }}</p>
        @endif

        @foreach (\App\Models\NewsletterEdition::SECTIONS as $type => $title)
            @if (! empty($sections[$type]))
                <div class="section-title">{{ $title }}</div>
                <ul>
                    @foreach ($sections[$type] as $item)
                        <li>
                            <a href="{{ $item['url'] }}" class="item-title">{{ $item['title'] }}</a>
                            @if (! empty($item['meta'])) <span class="meta"> · {{ $item['meta'] }}</span> @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        @endforeach

        <div class="footer">
            <p>Recibís este correo porque confirmaste tu suscripción al newsletter de Mi Folklore Argentino.</p>
            <p><a href="{{ $unsubscribeUrl }}">Darme de baja</a></p>
        </div>
    </div>
</body>
</html>
