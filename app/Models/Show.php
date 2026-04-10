<?php

namespace App\Models;

/**
 * @deprecated Use Event instead.
 */
class Show extends Event
{
    protected $table = 'events';

    // Maintains old primary key mapping if needed, but 'id' is standard for both.
}
