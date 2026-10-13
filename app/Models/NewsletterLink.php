<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Un enlace de una edición aprobada, con su contador agregado de clics (no guarda quién hizo clic). */
class NewsletterLink extends Model
{
    protected $fillable = ['edition_id', 'section', 'title', 'url', 'clicks'];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(NewsletterEdition::class, 'edition_id');
    }
}
