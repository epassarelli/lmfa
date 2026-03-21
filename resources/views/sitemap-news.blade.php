<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
    @foreach ($noticias as $noticia)
        <url>
            <loc>{{ route('noticias.show', $noticia->slug) }}</loc>
            <news:news>
                <news:publication>
                    <news:name>Mi Folklore Argentino</news:name>
                    <news:language>es</news:language>
                </news:publication>
                <news:publication_date>{{ $noticia->created_at->toIso8601String() }}</news:publication_date>
                <news:title>{{ $noticia->titulo }}</news:title>
            </news:news>
        </url>
    @endforeach
</urlset>
