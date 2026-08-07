<?php

namespace App\Support;

use App\Models\Comida;
use App\Support\CanonicalUrl;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RecipeContent
{
    private const FOREIGN_KEYWORDS = [
        'paella',
        'chiken broth',
        'chicken broth',
        'omelette',
        'souffle',
        'sabayon',
        'mousse',
        'anana a la crema de menta',
    ];

    private const ARGENTINA_HINTS = [
        'argentina',
        'argentino',
        'criollo',
        'criolla',
        'salte',
        'juje',
        'tucuman',
        'cuyo',
        'patagonia',
        'patagoni',
        'litoral',
        'cordob',
        'rioja',
        'santiag',
        'entrerr',
        'corrent',
        'mendoc',
        'catamar',
        'chaque',
        'locro',
        'tamal',
        'empanada',
        'humita',
        'pastelitos',
        'alfajor',
        'mate',
        'asado',
        'chimichurri',
        'carbonada',
        'mazamorra',
        'chip',
        'torta frita',
        'pastelito',
    ];

    public static function audit(Comida $recipe, array $context = []): array
    {
        $plainText = static::visibleText($recipe->receta);
        $normalizedText = static::normalizedText($plainText);
        $title = trim((string) $recipe->titulo);
        $slug = trim((string) $recipe->slug);

        $duplicateSlugs = (int) ($context['duplicate_slugs'][$slug] ?? 0);
        $duplicateTitles = (int) ($context['duplicate_titles'][$title] ?? 0);
        $similarTitles = $context['similar_titles'][$recipe->id] ?? [];
        $contentHash = static::contentHash($normalizedText);
        $contentDuplicates = $context['duplicate_contents'][$contentHash] ?? [];

        $hasIngredients = static::hasIngredients($plainText);
        $hasPreparation = static::hasPreparation($plainText);
        $hasImage = ($recipe->images->isNotEmpty() ?? false) || filled($recipe->foto);
        $encodingIssues = static::detectEncodingIssues($recipe->titulo.' '.$recipe->receta);
        $htmlIssues = static::detectHtmlIssues($recipe->receta);
        $wordCount = static::wordCount($normalizedText);
        $visibleChars = Str::length($normalizedText);
        $relevanceRisk = static::lacksArgentinaSignals($title, $normalizedText);
        $slugIssues = static::detectSlugIssues($slug, $duplicateSlugs);

        return [
            'id' => $recipe->id,
            'titulo' => $title,
            'slug' => $slug,
            'url_publica' => CanonicalUrl::normalize(route('comidas.show', $recipe->slug, false)),
            'estado' => static::stateLabel((int) $recipe->estado),
            'created_at' => optional($recipe->created_at)?->toAtomString(),
            'updated_at' => optional($recipe->updated_at)?->toAtomString(),
            'publicar' => optional($recipe->publicar)?->toAtomString(),
            'caracteres_visibles' => $visibleChars,
            'palabras' => $wordCount,
            'presencia_ingredientes' => $hasIngredients,
            'presencia_preparacion' => $hasPreparation,
            'presencia_imagen' => $hasImage,
            'titulos_duplicados' => $duplicateTitles > 1,
            'titulos_similares' => $similarTitles,
            'contenidos_duplicados' => count($contentDuplicates) > 1,
            'ids_contenido_duplicado' => array_values(array_diff($contentDuplicates, [$recipe->id])),
            'slug_vacio' => $slug === '',
            'slug_repetido' => $duplicateSlugs > 1,
            'slug_defectuoso' => $slugIssues !== [],
            'detalle_slug' => $slugIssues,
            'errores_html_evidentes' => $htmlIssues,
            'errores_codificacion' => $encodingIssues,
            'receta_extremadamente_breve' => $visibleChars < 280 || $wordCount < 45,
            'posible_falta_relacion_cocina_argentina' => $relevanceRisk,
            'estructura_actual' => static::structureSummary($plainText),
            'accion_editorial_sugerida' => static::suggestAction(
                $visibleChars,
                $hasIngredients,
                $hasPreparation,
                $duplicateTitles,
                $contentDuplicates,
                $relevanceRisk,
                $slugIssues
            ),
            'prioridad_sugerida' => static::suggestPriority(
                $visibleChars,
                $hasIngredients,
                $hasPreparation,
                $duplicateTitles,
                $contentDuplicates,
                $relevanceRisk
            ),
            'bajada_sugerida' => static::excerpt($normalizedText, 180),
        ];
    }

    public static function visibleText(?string $html): string
    {
        if ($html === null) {
            return '';
        }

        $prepared = preg_replace('/<\s*\/?(p|div|br|li|ul|ol|h[1-6]|section)\b[^>]*>/i', ' ', $html);
        $decoded = html_entity_decode((string) $prepared, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $withoutHtml = strip_tags($decoded);

        return static::normalizedText($withoutHtml);
    }

    public static function normalizedText(?string $text): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', (string) $text);

        return trim((string) $collapsed);
    }

    public static function excerpt(?string $text, int $maxLength = 160): string
    {
        $text = static::normalizedText($text);

        if ($text === '' || Str::length($text) <= $maxLength) {
            return $text;
        }

        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $excerpt = '';

        foreach ($words as $word) {
            $candidate = trim($excerpt === '' ? $word : $excerpt.' '.$word);

            if (Str::length($candidate) > $maxLength) {
                break;
            }

            $excerpt = $candidate;
        }

        return rtrim($excerpt, " ,;:-.\t\n\r\0\x0B").'.';
    }

    public static function contentHash(string $normalizedText): string
    {
        return sha1(Str::lower($normalizedText));
    }

    public static function hasIngredients(string $plainText): bool
    {
        $text = Str::lower(Str::ascii($plainText));

        if (Str::contains($text, ['ingredientes', 'relleno', 'masa', 'para la masa', 'para el relleno'])) {
            return true;
        }

        return preg_match('/\b\d+\s*(kg|gr|g|ml|l|litro|litros|taza|tazas|cucharada|cucharadas|cdta|cdita|huevo|huevos)\b/u', $plainText) === 1;
    }

    public static function hasPreparation(string $plainText): bool
    {
        $text = Str::lower(Str::ascii($plainText));

        if (Str::contains($text, ['preparacion', 'preparar', 'paso', 'mezclar', 'cocinar', 'hornear', 'servir'])) {
            return true;
        }

        return preg_match('/(^|\s)(1[°.)-]|2[°.)-]|3[°.)-]|paso\s+1)/iu', $plainText) === 1;
    }

    public static function detectEncodingIssues(string $value): array
    {
        $issues = [];

        foreach (['Ã', 'Â', '�'] as $token) {
            if (Str::contains($value, $token)) {
                $issues[] = "TOKEN_{$token}";
            }
        }

        return array_values(array_unique($issues));
    }

    public static function detectHtmlIssues(string $html): array
    {
        $issues = [];

        if ($html === '') {
            return ['HTML_VACIO'];
        }

        if (substr_count(Str::lower($html), '<ul') !== substr_count(Str::lower($html), '</ul>')) {
            $issues[] = 'UL_DESBALANCEADO';
        }

        if (substr_count(Str::lower($html), '<ol') !== substr_count(Str::lower($html), '</ol>')) {
            $issues[] = 'OL_DESBALANCEADO';
        }

        if (substr_count(Str::lower($html), '<p') !== substr_count(Str::lower($html), '</p>')) {
            $issues[] = 'P_DESBALANCEADO';
        }

        if (preg_match('/<[^>]*$/', $html) === 1) {
            $issues[] = 'TAG_FINAL_INCOMPLETA';
        }

        return $issues;
    }

    public static function lacksArgentinaSignals(string $title, string $plainText): bool
    {
        $haystack = Str::lower(Str::ascii($title.' '.$plainText));

        foreach (self::ARGENTINA_HINTS as $hint) {
            if (Str::contains($haystack, Str::lower(Str::ascii($hint)))) {
                return false;
            }
        }

        foreach (self::FOREIGN_KEYWORDS as $foreignKeyword) {
            if (Str::contains($haystack, Str::lower(Str::ascii($foreignKeyword)))) {
                return true;
            }
        }

        return true;
    }

    public static function structureSummary(string $plainText): array
    {
        return [
            'tiene_bajada' => Str::length($plainText) >= 80,
            'tiene_contexto' => Str::contains(Str::lower(Str::ascii($plainText)), ['origen', 'tradicion', 'tipica', 'regional', 'provincia']),
            'tiene_ficha' => preg_match('/\b(porciones|minutos|dificultad)\b/iu', $plainText) === 1,
            'tiene_ingredientes' => static::hasIngredients($plainText),
            'tiene_preparacion' => static::hasPreparation($plainText),
            'tiene_consejos' => Str::contains(Str::lower(Str::ascii($plainText)), ['consejo', 'tip', 'recomend']),
            'tiene_variantes' => Str::contains(Str::lower(Str::ascii($plainText)), ['variante', 'adaptacion', 'opcion']),
            'tiene_servicio' => Str::contains(Str::lower(Str::ascii($plainText)), ['servir', 'acompan']),
            'tiene_conservacion' => Str::contains(Str::lower(Str::ascii($plainText)), ['guardar', 'freezer', 'heladera', 'recalentar']),
        ];
    }

    public static function renderStructuredHtml(array $data): string
    {
        $sections = [];

        if (! blank($data['bajada'] ?? null)) {
            $sections[] = '<p class="receta-bajada">'.e(trim($data['bajada'])).'</p>';
        }

        foreach ([
            'contexto' => ['class' => 'receta-contexto', 'title' => 'Sobre esta receta', 'type' => 'paragraphs'],
            'ficha' => ['class' => 'receta-ficha', 'title' => 'Informacion de la receta', 'type' => 'facts'],
            'ingredientes' => ['class' => 'receta-ingredientes', 'title' => 'Ingredientes', 'type' => 'groups'],
            'preparacion' => ['class' => 'receta-preparacion', 'title' => 'Preparacion paso a paso', 'type' => 'ordered'],
            'consejos' => ['class' => 'receta-consejos', 'title' => 'Consejos para que salga bien', 'type' => 'unordered'],
            'variantes' => ['class' => 'receta-variantes', 'title' => 'Variantes y adaptaciones', 'type' => 'paragraphs'],
            'servicio' => ['class' => 'receta-servicio', 'title' => 'Como servirla', 'type' => 'paragraphs'],
            'conservacion' => ['class' => 'receta-conservacion', 'title' => 'Conservacion y recalentado', 'type' => 'paragraphs'],
        ] as $key => $config) {
            $html = static::renderSection($data[$key] ?? null, $config['class'], $config['title'], $config['type']);

            if ($html !== null) {
                $sections[] = $html;
            }
        }

        return implode("\n\n", $sections);
    }

    public static function uniquePilotCandidates(Collection $recipes): Collection
    {
        return $recipes->unique(fn ($recipe) => Str::lower(Str::ascii($recipe->titulo)))->values();
    }

    protected static function renderSection(mixed $value, string $class, string $title, string $type): ?string
    {
        if (blank($value)) {
            return null;
        }

        $inner = match ($type) {
            'paragraphs' => static::renderParagraphs((array) $value),
            'facts' => static::renderFacts((array) $value),
            'groups' => static::renderGroups((array) $value),
            'ordered' => static::renderList((array) $value, true),
            'unordered' => static::renderList((array) $value, false),
            default => null,
        };

        if ($inner === null || trim($inner) === '') {
            return null;
        }

        return <<<HTML
<section class="{$class}">
    <h2>{$title}</h2>
    {$inner}
</section>
HTML;
    }

    protected static function renderParagraphs(array $paragraphs): ?string
    {
        $paragraphs = collect($paragraphs)
            ->filter(fn ($paragraph) => filled(trim((string) $paragraph)))
            ->map(fn ($paragraph) => '<p>'.e(trim((string) $paragraph)).'</p>')
            ->all();

        return $paragraphs !== [] ? implode("\n    ", $paragraphs) : null;
    }

    protected static function renderFacts(array $facts): ?string
    {
        $items = collect($facts)
            ->filter(fn ($value) => filled(trim((string) $value)))
            ->map(function ($value, $label) {
                return '<li><strong>'.e((string) $label).':</strong> '.e(trim((string) $value)).'</li>';
            })
            ->all();

        if ($items === []) {
            return null;
        }

        return "<ul>\n        ".implode("\n        ", $items)."\n    </ul>";
    }

    protected static function renderGroups(array $groups): ?string
    {
        $chunks = [];

        foreach ($groups as $group) {
            $groupTitle = trim((string) ($group['title'] ?? ''));
            $items = collect($group['items'] ?? [])
                ->filter(fn ($item) => filled(trim((string) $item)))
                ->map(fn ($item) => '<li>'.e(trim((string) $item)).'</li>')
                ->all();

            if ($items === []) {
                continue;
            }

            $chunk = '';
            if ($groupTitle !== '') {
                $chunk .= '<h3>'.e($groupTitle)."</h3>\n    ";
            }

            $chunk .= "<ul>\n        ".implode("\n        ", $items)."\n    </ul>";
            $chunks[] = $chunk;
        }

        return $chunks !== [] ? implode("\n\n    ", $chunks) : null;
    }

    protected static function renderList(array $items, bool $ordered): ?string
    {
        $items = collect($items)
            ->filter(fn ($item) => filled(trim((string) $item)))
            ->map(fn ($item) => '<li>'.e(trim((string) $item)).'</li>')
            ->all();

        if ($items === []) {
            return null;
        }

        $tag = $ordered ? 'ol' : 'ul';

        return "<{$tag}>\n        ".implode("\n        ", $items)."\n    </{$tag}>";
    }

    protected static function detectSlugIssues(string $slug, int $duplicateSlugs): array
    {
        $issues = [];

        if ($slug === '') {
            $issues[] = 'SLUG_VACIO';
        }

        if ($duplicateSlugs > 1) {
            $issues[] = 'SLUG_REPETIDO';
        }

        if ($slug !== '' && preg_match('/^[a-z0-9\-]+$/', $slug) !== 1) {
            $issues[] = 'SLUG_FORMATO_NO_CANONICO';
        }

        if (Str::contains($slug, '--')) {
            $issues[] = 'SLUG_GUIONES_DUPLICADOS';
        }

        return $issues;
    }

    protected static function suggestAction(
        int $visibleChars,
        bool $hasIngredients,
        bool $hasPreparation,
        int $duplicateTitles,
        array $contentDuplicates,
        bool $relevanceRisk,
        array $slugIssues
    ): string {
        if ($relevanceRisk && $visibleChars < 900) {
            return 'REVISAR_RELEVANCIA';
        }

        if ($duplicateTitles > 1 || count($contentDuplicates) > 1) {
            return 'CONSOLIDAR';
        }

        if ($slugIssues !== []) {
            return 'CORREGIR_TITULO';
        }

        if (! $hasIngredients || ! $hasPreparation || $visibleChars < 280) {
            return 'RECONSTRUIR';
        }

        if ($visibleChars < 1200) {
            return 'CONSERVAR_AMPLIAR';
        }

        return 'CONSERVAR_AMPLIAR';
    }

    protected static function suggestPriority(
        int $visibleChars,
        bool $hasIngredients,
        bool $hasPreparation,
        int $duplicateTitles,
        array $contentDuplicates,
        bool $relevanceRisk
    ): string {
        if ($relevanceRisk) {
            return 'MEDIA';
        }

        if (! $hasIngredients || ! $hasPreparation || $visibleChars < 280) {
            return 'ALTA';
        }

        if ($duplicateTitles > 1 || count($contentDuplicates) > 1 || $visibleChars < 800) {
            return 'MEDIA';
        }

        return 'BAJA';
    }

    protected static function stateLabel(int $state): string
    {
        return $state === 1 ? 'PUBLICADO' : 'BORRADOR';
    }

    protected static function wordCount(string $text): int
    {
        preg_match_all('/[\p{L}\p{N}\']+/u', $text, $matches);

        return count($matches[0]);
    }
}
