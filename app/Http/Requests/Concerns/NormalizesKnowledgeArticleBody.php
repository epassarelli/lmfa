<?php

namespace App\Http\Requests\Concerns;

trait NormalizesKnowledgeArticleBody
{
    use NormalizesRichTextFields;

    protected function prepareForValidation(): void
    {
        $this->normalizeRichTextFields(['body']);
    }
}
