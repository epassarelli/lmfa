<?php

namespace App\Http\Requests\Api\Concerns;

use App\Models\KnowledgeCategory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait ResolvesKnowledgeCategoryInput
{
    protected ?KnowledgeCategory $resolvedKnowledgeCategory = null;
    protected ?string $knowledgeCategoryLookupField = null;

    protected function prepareForValidation(): void
    {
        $slug = $this->normalizedCategorySlug();
        $name = $this->normalizedCategoryName();

        if ($slug !== null) {
            $this->merge(['knowledge_category_slug' => $slug]);
        }

        if ($name !== null) {
            $this->merge(['knowledge_category_name' => $name]);
        }

        $this->resolvedKnowledgeCategory = $this->resolveKnowledgeCategory($slug, $name);

        if ($this->resolvedKnowledgeCategory) {
            $this->merge([
                'knowledge_category_id' => $this->resolvedKnowledgeCategory->id,
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->hasKnowledgeCategoryLookup()) {
                if ($this->knowledgeCategoryIsRequired()) {
                    $validator->errors()->add(
                        'knowledge_category_id',
                        'Debe enviar knowledge_category_id, knowledge_category_slug o knowledge_category_name.'
                    );
                }

                return;
            }

            if (! $this->resolvedKnowledgeCategory) {
                $validator->errors()->add(
                    'knowledge_category_id',
                    'La categoria evergreen enviada no existe.'
                );

                return;
            }

            if (! $this->resolvedKnowledgeCategory->is_active) {
                $validator->errors()->add(
                    'knowledge_category_id',
                    'La categoria evergreen enviada esta inactiva.'
                );
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson() && $this->hasKnowledgeCategoryErrors($validator)) {
            throw new HttpResponseException(response()->json([
                'message' => 'No se pudo resolver la categoria evergreen enviada.',
                'code' => 'BLOQUEADO_CATEGORIA',
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }

    protected function knowledgeCategoryIsRequired(): bool
    {
        return true;
    }

    protected function hasKnowledgeCategoryLookup(): bool
    {
        return $this->filled('knowledge_category_id')
            || $this->filled('knowledge_category_slug')
            || $this->filled('knowledge_category_name');
    }

    protected function hasKnowledgeCategoryErrors(Validator $validator): bool
    {
        return $validator->errors()->has('knowledge_category_id')
            || $validator->errors()->has('knowledge_category_slug')
            || $validator->errors()->has('knowledge_category_name');
    }

    protected function resolveKnowledgeCategory(?string $slug, ?string $name): ?KnowledgeCategory
    {
        if ($this->filled('knowledge_category_id')) {
            $this->knowledgeCategoryLookupField = 'knowledge_category_id';

            return KnowledgeCategory::query()->find($this->integer('knowledge_category_id'));
        }

        if ($slug !== null) {
            $this->knowledgeCategoryLookupField = 'knowledge_category_slug';

            return KnowledgeCategory::query()
                ->whereRaw('LOWER(slug) = ?', [$slug])
                ->first();
        }

        if ($name !== null) {
            $this->knowledgeCategoryLookupField = 'knowledge_category_name';

            return KnowledgeCategory::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->first();
        }

        return null;
    }

    protected function normalizedCategorySlug(): ?string
    {
        $slug = $this->input('knowledge_category_slug');

        if (! is_string($slug) || trim($slug) === '') {
            return null;
        }

        return mb_strtolower(trim($slug));
    }

    protected function normalizedCategoryName(): ?string
    {
        $name = $this->input('knowledge_category_name');

        if (! is_string($name) || trim($name) === '') {
            return null;
        }

        return trim($name);
    }
}
