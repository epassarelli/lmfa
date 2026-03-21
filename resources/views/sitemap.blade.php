<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    @foreach ($urls as $url)
        <url>
            <loc>{{ $url['url'] }}</loc>
            <lastmod>{{ $url['lastmod'] ?? now()->toAtomString() }}</lastmod>
            <changefreq>{{ $url['changefreq'] ?? 'weekly' }}</changefreq>
            <priority>{{ $url['priority'] ?? '0.8' }}</priority>
            @if(isset($url['image']))
            <image:image>
                <image:loc>{{ $url['image'] }}</image:loc>
            </image:image>
            @endif
        </url>
    @endforeach
</urlset>
