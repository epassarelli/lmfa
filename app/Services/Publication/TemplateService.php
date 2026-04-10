<?php

namespace App\Services\Publication;

use App\Models\PublicationTarget;
use App\Models\PublicationTemplate;

class TemplateService
{
    /**
     * Resolve and render the best matching template for a given target and content.
     * Falls back to plain title + excerpt if no template is configured.
     *
     * @param  PublicationTarget  $target
     * @param  object             $content   Event or News model instance
     * @return string
     */
    public function render(PublicationTarget $target, object $content): string
    {
        $request = $target->request;
        $contentType = $request?->content_type ?? get_class($content);

        // 1. Try exact match: content_type + provider + variant
        $template = PublicationTemplate::active()
            ->where('content_type', $contentType)
            ->where('provider', $target->provider)
            ->where('variant_name', $target->template_variant)
            ->first();

        // 2. Fallback: any active template for this provider + content_type
        if (!$template) {
            $template = PublicationTemplate::active()
                ->where('content_type', $contentType)
                ->where('provider', $target->provider)
                ->first();
        }

        // 3. Fallback: any active template for this provider (provider-level default)
        if (!$template) {
            $template = PublicationTemplate::active()
                ->where('provider', $target->provider)
                ->where('content_type', null)
                ->first();
        }

        if (!$template) {
            return $this->defaultCopy($content);
        }

        return $template->render($this->buildTokens($content));
    }

    /**
     * Build the token map for template rendering.
     */
    public function buildTokens(object $content): array
    {
        $url = null;
        if (method_exists($content, 'getTable')) {
            $slug = $content->slug ?? null;
            if ($slug) {
                try {
                    $url = $content->getTable() === 'events'
                        ? route('cartelera.show', $slug)
                        : route('noticias.show', $slug);
                } catch (\Exception) {}
            }
        }

        return [
            'title'    => $content->title ?? '',
            'subtitle' => $content->subtitle ?? '',
            'excerpt'  => $content->excerpt ?? ($content->subtitle ?? ''),
            'url'      => $url ?? '',
            'date'     => isset($content->start_at) ? $content->start_at->format('d/m/Y') : '',
            'city'     => $content->city ?? '',
            'venue'    => (method_exists($content, 'relationLoaded') && $content->relationLoaded('venue')) ? ($content->venue?->name ?? '') : '',
        ];
    }

    /**
     * Default copy when no template is found.
     */
    private function defaultCopy(object $content): string
    {
        $title   = $content->title ?? '';
        $excerpt = $content->excerpt ?? ($content->subtitle ?? '');

        return trim("{$title}\n\n{$excerpt}");
    }
}
