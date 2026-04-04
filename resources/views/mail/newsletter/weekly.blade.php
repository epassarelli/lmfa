<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #ff661f; margin-bottom: 20px; padding-bottom: 10px; }
        .section-title { font-size: 1.25rem; font-weight: bold; margin-top: 20px; color: #ff661f; }
        .item-title { font-weight: bold; color: #000; text-decoration: none; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 0.85rem; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Novedades de la Semana</h2>
            <p>{{ $newsData['period'] }}</p>
        </div>

        <p>Hola, aquí tienes un resumen de lo más destacado de los últimos días en nuestro portal de folklore:</p>

        @if($newsData['noticias']->count())
            <div class="section-title">Últimas Noticias</div>
            <ul>
                @foreach($newsData['noticias'] as $noticia)
                    <li>
                        <a href="{{ route('noticias.show', $noticia->slug) }}" class="item-title">{{ $noticia->titulo ?? 'Noticia' }}</a>
                    </li>
                @endforeach
            </ul>
        @endif

        @if($newsData['discos']->count())
            <div class="section-title">Discos Agregados</div>
            <ul>
                @foreach($newsData['discos'] as $disco)
                    @php $interpreteSlug = $disco->interprete ? $disco->interprete->slug : 'varios'; @endphp
                    <li>
                        <a href="{{ route('artista.disco', ['interprete' => $interpreteSlug, 'slug' => $disco->slug]) }}" class="item-title">{{ $disco->album ?? 'Disco' }}</a>
                    </li>
                @endforeach
            </ul>
        @endif

        @if($newsData['canciones']->count())
            <div class="section-title">Letras Nuevas</div>
            <ul>
                @foreach($newsData['canciones'] as $cancion)
                    @php $interpreteSlug = $cancion->interprete ? $cancion->interprete->slug : 'varios'; @endphp
                    <li>
                        <a href="{{ route('artista.cancion', ['interprete' => $interpreteSlug, 'cancion' => $cancion->slug]) }}" class="item-title">{{ $cancion->cancion ?? 'Canción' }}</a>
                    </li>
                @endforeach
            </ul>
        @endif

        @if($newsData['interpretes']->count())
            <div class="section-title">Nuevos Artistas Ingresados</div>
            <ul>
                @foreach($newsData['interpretes'] as $interprete)
                    <li>
                        <a href="{{ route('artista.show', $interprete->slug) }}" class="item-title">{{ $interprete->interprete ?? 'Artista' }}</a>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="footer">
            <p>Has recibido este correo porque te suscribiste a nuestro newsletter.</p>
            <p>
                <a href="{{ route('newsletter.unsubscribe', $subscriber->token) }}">Deseo darme de baja</a>
            </p>
        </div>
    </div>
</body>
</html>
