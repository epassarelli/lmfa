<?php

namespace App\Policies;

use App\Models\User;
use App\Models\News;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasRole('administrador') || $user->can('read noticia') || $user->managedInterpreteIds() !== [];
    }

    public function view(User $user, News $news)
    {
        return $user->hasRole('administrador') || $user->can('read noticia') || $news->created_by == $user->id || $user->managesAllInterpretes($news->linkedInterpreteIds());
    }

    public function create(User $user)
    {
        return $user->hasRole('administrador') || $user->can('create noticia') || $user->managedInterpreteIds() !== [];
    }

    public function update(User $user, News $news)
    {
        return $user->hasRole('administrador') || $user->can('update noticia') || $news->created_by == $user->id || $user->managesAllInterpretes($news->linkedInterpreteIds());
    }

    public function delete(User $user, News $news)
    {
        return $user->hasRole('administrador');
    }
}
