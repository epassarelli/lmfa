<?php

namespace App\Http\Requests\Concerns;

use App\Support\RichTextHeadingSanitizer;

trait NormalizesRichTextFields
{
    protected function normalizeRichTextFields(array $fields): void
    {
        $normalized = [];

        foreach ($fields as $field) {
            if ($this->has($field)) {
                $normalized[$field] = RichTextHeadingSanitizer::normalize($this->input($field));
            }
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }
}
