<?php

namespace App\Support;

class KnowledgeArticleBodySanitizer
{
    public static function normalize(?string $html): ?string
    {
        return RichTextHeadingSanitizer::normalize($html);
    }
}
