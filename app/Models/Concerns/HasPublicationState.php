<?php

namespace App\Models\Concerns;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

trait HasPublicationState
{
    public function scopePublishedVisible(Builder $query): Builder
    {
        return $query
            ->where('editorial_status', 'published')
            ->where(function (Builder $builder) {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('editorial_status', 'draft');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->where('editorial_status', 'approved')
                ->orWhere('editorial_status', 'published')
                ->orWhereNotNull('approved_at');
        });
    }

    public function publicationDateTime(): ?CarbonInterface
    {
        return $this->published_at;
    }

    public function shouldPublishAt(): ?CarbonInterface
    {
        return $this->publicationDateTime();
    }

    public function isPublished(): bool
    {
        return $this->editorial_status === 'published'
            && (! $this->published_at || $this->published_at->lte(now()));
    }

    public function isPendingReview(): bool
    {
        return $this->editorial_status === 'draft';
    }

    public function isApproved(): bool
    {
        return in_array($this->editorial_status, ['approved', 'published'], true)
            || $this->approved_at !== null;
    }
}
