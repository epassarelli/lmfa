<?php

namespace App\Services\Publication;

use Illuminate\Support\Facades\Storage;

class SocialPublicationRenderer
{
    public function render(array $payload, string $format = 'png'): array
    {
        abort_unless(extension_loaded('gd'), 503, 'GD no está disponible.');
        $regular = config('social_publication.font_regular');
        $bold = config('social_publication.font_bold');
        abort_unless(is_readable($regular) && is_readable($bold), 503, 'Falta configurar la fuente editorial aprobada.');
        $catalog = json_decode(file_get_contents(resource_path('social-templates/catalog.v4.json')), true, 512, JSON_THROW_ON_ERROR);
        $template = collect($catalog['templates'])->firstWhere('id', $payload['template_id']);
        abort_unless($template && in_array($payload['template_id'], ['mfa-noticias-v4', 'mfa-agenda-v4', 'mfa-discos-v4'], true)
            && $template['type'] === $payload['content_kind'] && $payload['template_catalog_version'] === '4.0.0', 422, 'Plantilla no aprobada.');
        $image = imagecreatetruecolor(1080, 1350);
        imageantialias($image, true);
        $color = function (string $hex) use ($image): int {
            return imagecolorallocate($image, hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2)));
        };
        $background = $color('#14171C'); $white = $color('#FFFFFF'); $accent = $color($template['accent']);
        $box = fn ($x, $y, $width, $height, $ink) => imagefilledrectangle($image, $x, $y, $x+$width, $y+$height, $ink);
        $width = function (string $text, int $size, bool $strong = false) use ($regular, $bold): int {
            $bounds = imagettfbbox($size * .75, 0, $strong ? $bold : $regular, $text);
            return max($bounds[2], $bounds[4]) - min($bounds[0], $bounds[6]);
        };
        $label = function (string $text, int $x, int $top, int $size, int $ink, bool $strong = false) use ($image, $regular, $bold, $width): void {
            abort_if($x+$width($text, $size, $strong) > 1010 || $top+$size > 1310, 422, 'Texto fuera del área editorial.');
            imagettftext($image, $size * .75, 0, $x, $top+$size, $ink, $strong ? $bold : $regular, $text);
        };
        $wrap = function (string $text, int $size, int $maxWidth, bool $strong = false) use ($width): array {
            $lines = []; $line = '';
            foreach (preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) as $word) {
                abort_if($width($word, $size, $strong) > $maxWidth, 422, 'Palabra demasiado larga para la placa.');
                $candidate = $line === '' ? $word : $line.' '.$word;
                if ($width($candidate, $size, $strong) > $maxWidth) { $lines[] = $line; $line = $word; }
                else { $line = $candidate; }
            }
            if ($line !== '') { $lines[] = $line; }
            return $lines;
        };
        try {
            $box(0, 0, 1080, 1350, $background);
            $cursor = 72;
            foreach ([['Mi Folk','#93C5FD'],['lor','#FFFFFF'],['e','#FACC15'],['Arg','#FFFFFF'],['entino','#93C5FD']] as [$text, $hex]) {
                $label($text, $cursor, 57, 46, $color($hex), true);
                $cursor += $width($text, 46, true);
            }
            $box(73, 168, 934, 3, $accent);
            $tabWidth = $width($template['section'], 36, true)+42;
            $box(73, 225, $tabWidth, 64, $accent);
            // Orange and gold approved for this pilot both contrast with the dark ink.
            $label($template['section'], 90, 233, 36, $background, true);
            $label($template['category'], 73, 323, 22, $white);
            $title = trim($payload['title'] ?? '');
            abort_if($title === '', 422, 'Título vacío.');
            $cover = $payload['content_kind'] === 'albums' ? $this->cover($payload) : null;
            if ($cover) {
                $this->albumWithCover($image, $cover, $payload, $title, $accent, $white, $box, $label, $wrap);
            } else {
            $lines = []; $size = 80;
            for (; $size >= 52; $size -= 2) {
                try { $lines = $wrap($title, $size, 925, true); } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { continue; }
                if (count($lines) * (int) ceil($size * 1.23) <= 414) { break; }
            }
            abort_if($size < 52, 422, 'El título requiere revisión; no se truncó.');
            foreach ($lines as $i => $line) { $label($line, 68, 438+(int) ceil($size*1.23)*$i, $size, $i === count($lines)-1 ? $accent : $white, true); }
            $box(74, 887, 76, 5, $accent);
            if ($payload['content_kind'] === 'events') {
                abort_unless(!empty($payload['event_start_at']), 422, 'Evento sin fecha.');
                $start = \Carbon\CarbonImmutable::parse($payload['event_start_at'])->setTimezone($payload['event_timezone'] ?? 'America/Argentina/Buenos_Aires');
                $end = empty($payload['event_end_at']) ? null : \Carbon\CarbonImmutable::parse($payload['event_end_at'])->setTimezone($start->timezone);
                $date = $end && !$end->isSameDay($start) ? $start->format('d/m/Y').' AL '.$end->format('d/m/Y') : $start->format('d/m/Y');
                $details = [$date, mb_strtoupper($payload['event_city'] ?? '')];
            } elseif ($payload['content_kind'] === 'albums') {
                $details = array_values(array_filter([mb_strtoupper($payload['album_artist'] ?? ''), $payload['album_year'] ?? null]));
            } else { $details = ['NOTICIA DEL PORTAL', 'FOLKLORE ARGENTINO']; }
            foreach ($details as $i => $detail) { $label($detail, 71, 919+40*$i, 26, $white, true); }
            $note = $wrap($payload['excerpt'] ?? '', 28, 810);
            // The full excerpt stays in the caption; omit the optional field rather than truncate it.
            if (count($note) <= 3) { foreach ($note as $i => $line) { $label($line, 71, 1023+38*$i, 28, $white); } }
            for ($i=0; $i<4; $i++) { $y=928+$i*40; imagefilledpolygon($image, [973,$y,985,$y+12,973,$y+24,961,$y+12], $accent); }
            }
            $box(73, 1166, 934, 2, $accent);
            $label($template['cta'], 71, 1195, 23, $white, true);
            $label('mifolkloreargentino.com', 71, 1238, 26, $accent);
            abort_unless(in_array($format, ['png', 'jpeg'], true), 422);
            ob_start();
            if ($format === 'jpeg') { imagejpeg($image, null, 94); } else { imagepng($image, null, 6); }
            $png = ob_get_clean();
            $extension = $format === 'jpeg' ? 'jpg' : 'png';
            $hash = hash('sha256', $png);
            $path = 'social-publications/assets/'.$hash.'.'.$extension;
            abort_unless(Storage::disk('local')->put($path, $png), 503, 'No se pudo conservar la placa.');
            return ($format === 'jpeg' ? ['asset_url' => route('social-publications.asset', ['hash' => $hash])] : []) + ['asset_reference' => 'sha256:'.$hash, 'asset_sha256' => $hash, 'asset_base64' => base64_encode($png), 'mime_type' => $format === 'jpeg' ? 'image/jpeg' : 'image/png', 'width' => 1080, 'height' => 1350];
        } finally { imagedestroy($image); }
    }

    /** Tipos de disco conocidos para la columna derecha de la placa; el resto se omite. */
    private const ALBUM_TYPES = ['album' => 'ÁLBUM', 'single' => 'SIMPLE', 'ep' => 'EP', 'compilation' => 'COMPILADO', 'compilado' => 'COMPILADO', 'simple' => 'SIMPLE', 'álbum' => 'ÁLBUM'];

    /**
     * Portada del disco como imagen GD, solo si el archivo sigue existiendo y coincide con la huella del snapshot.
     * Sin portada legible la placa sale sin ella (spec social-autopublicacion-discos, 5.3): no se bloquea la cola.
     */
    private function cover(array $payload): ?\GdImage
    {
        $relative = $payload['cover_path'] ?? null;
        if (!$relative || !Storage::disk('public')->exists($relative)) { return null; }
        $path = Storage::disk('public')->path($relative);
        if (!empty($payload['cover_sha256']) && !hash_equals($payload['cover_sha256'], (string) hash_file('sha256', $path))) { return null; }
        $image = @imagecreatefromstring((string) file_get_contents($path));
        return $image instanceof \GdImage ? $image : null;
    }

    /** Variante A aprobada (2026-11-20): portada grande a la izquierda, año y tipo a la derecha, título e intérprete debajo. */
    private function albumWithCover(\GdImage $image, \GdImage $cover, array $payload, string $title, int $accent, int $white,
        \Closure $box, \Closure $label, \Closure $wrap): void
    {
        try {
            // Título: de 66 a 40 px, hasta dos líneas a ancho completo; sin truncar.
            $lines = []; $size = 66;
            for (; $size >= 40; $size -= 2) {
                try { $lines = $wrap($title, $size, 934, true); } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { continue; }
                if (count($lines) <= 2) { break; }
            }
            abort_if($size < 40, 422, 'El título requiere revisión; no se truncó.');
            $artist = mb_strtoupper(trim($payload['album_artist'] ?? ''));
            $artistSize = 28;
            while ($artistSize > 20 && $artist !== '' && count($wrap($artist, $artistSize, 934, true)) > 1) { $artistSize -= 2; }
            abort_if($artist !== '' && count($wrap($artist, $artistSize, 934, true)) > 1, 422, 'El intérprete requiere revisión; no se truncó.');

            // Se arma de abajo hacia arriba: la línea del cierre está en 1166.
            $lineHeight = (int) ceil($size * 1.18);
            $artistTop = 1166 - 32 - $artistSize;
            $titleTop = $artistTop - 14 - $lineHeight * count($lines);
            $coverTop = 380;
            $side = min(600, $titleTop - 36 - $coverTop);
            abort_if($side < 360, 422, 'No hay lugar para la portada.');

            // Recorte centrado a cuadrado y escalado.
            $w = imagesx($cover); $h = imagesy($cover); $crop = min($w, $h);
            imagecopyresampled($image, $cover, 73, $coverTop, (int) (($w - $crop) / 2), (int) (($h - $crop) / 2), $side, $side, $crop, $crop);
            $box(73, $coverTop + $side + 14, $side, 5, $accent);

            // Columna derecha: año y tipo de disco, si son conocidos.
            $column = 73 + $side + 47;
            $cursor = $coverTop;
            if (!empty($payload['album_year'])) {
                $label('AÑO', $column, $cursor, 24, $white, true);
                $label($payload['album_year'], $column - 4, $cursor + 32, 84, $accent, true);
                $cursor += 150;
            }
            if ($type = self::ALBUM_TYPES[$payload['album_type'] ?? ''] ?? null) {
                $label($type, $column, $cursor, 24, $white, true);
            }
            for ($i = 0; $i < 4; $i++) { $y = $coverTop + 260 + $i * 40; imagefilledpolygon($image, [973,$y,985,$y+12,973,$y+24,961,$y+12], $accent); }

            foreach ($lines as $i => $line) { $label($line, 70, $titleTop + $lineHeight * $i, $size, $white, true); }
            if ($artist !== '') { $label($artist, 73, $artistTop, $artistSize, $accent, true); }
        } finally {
            imagedestroy($cover);
        }
    }
}
