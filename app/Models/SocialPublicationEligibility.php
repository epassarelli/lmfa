<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPublicationEligibility extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['enabled' => 'boolean', 'portal_only_override' => 'boolean', 'enabled_at' => 'datetime', 'disabled_at' => 'datetime'];
}
