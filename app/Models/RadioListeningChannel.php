<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadioListeningChannel extends Model
{
    use HasFactory;

    protected $fillable = ['radio_signal_id', 'label', 'channel_type', 'platform', 'frequency_band', 'frequency', 'url', 'is_primary', 'is_active', 'sort_order'];

    protected $casts = ['is_primary' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];

    public function signal()
    {
        return $this->belongsTo(RadioSignal::class, 'radio_signal_id');
    }
}
