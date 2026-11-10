<?php

namespace App\Services\Publication;

use Illuminate\Support\Facades\Storage;

class SocialPublicationRenderer
{
    public function render(array $payload): array
    {
        abort_unless(extension_loaded('gd'), 503, 'GD no está disponible.');
        $regular = config('social_publication.font_regular');
        $bold = config('social_publication.font_bold');
        abort_unless(is_readable($regular) && is_readable($bold), 503, 'Falta configurar la fuente editorial aprobada.');
        $catalog = json_decode(file_get_contents(resource_path('social-templates/catalog.v4.json')), true, 512, JSON_THROW_ON_ERROR);
        $template = collect($catalog['templates'])->firstWhere('id', $payload['template_id']);
        abort_unless($template && in_array($payload['template_id'], ['mfa-noticias-v4', 'mfa-agenda-v4'], true)
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
            } else { $details = ['NOTICIA DEL PORTAL', 'FOLKLORE ARGENTINO']; }
            foreach ($details as $i => $detail) { $label($detail, 71, 919+40*$i, 26, $white, true); }
            $note = $wrap($payload['excerpt'] ?? '', 28, 810);
            // The full excerpt stays in the caption; omit the optional field rather than truncate it.
            if (count($note) <= 3) { foreach ($note as $i => $line) { $label($line, 71, 1023+38*$i, 28, $white); } }
            for ($i=0; $i<4; $i++) { $y=928+$i*40; imagefilledpolygon($image, [973,$y,985,$y+12,973,$y+24,961,$y+12], $accent); }
            $box(73, 1166, 934, 2, $accent);
            $label($template['cta'], 71, 1195, 23, $white, true);
            $label('mifolkloreargentino.com', 71, 1238, 26, $accent);
            ob_start(); imagepng($image, null, 6); $png = ob_get_clean();
            $hash = hash('sha256', $png);
            $path = 'social-publications/assets/'.$hash.'.png';
            abort_unless(Storage::disk('local')->put($path, $png), 503, 'No se pudo conservar la placa.');
            return ['asset_reference' => 'sha256:'.$hash, 'asset_sha256' => $hash, 'asset_base64' => base64_encode($png), 'mime_type' => 'image/png', 'width' => 1080, 'height' => 1350];
        } finally { imagedestroy($image); }
    }
}
