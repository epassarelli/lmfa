<?php

namespace App\Policies;

use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KnowledgeArticlePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('administrador') || $user->can('read noticia');
    }

    public function view(User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $user->hasRole('administrador')
            || $user->can('read noticia')
            || $knowledgeArticle->author_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('administrador') || $user->can('create noticia');
    }

    public function update(User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $user->hasRole('administrador')
            || $user->can('update noticia')
            || $knowledgeArticle->author_id === $user->id;
    }

    public function delete(User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $user->hasRole('administrador');
    }
}
