<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadioProgramSlot extends Model
{
    use HasFactory;

    protected $fillable = ['radio_program_id', 'weekday', 'starts_at', 'ends_at', 'timezone', 'is_active'];

    protected $casts = ['weekday' => 'integer', 'is_active' => 'boolean'];

    public function program()
    {
        return $this->belongsTo(RadioProgram::class, 'radio_program_id');
    }

    public function nextStartsAt(?CarbonImmutable $from = null): CarbonImmutable
    {
        $from ??= CarbonImmutable::now($this->timezone);
        $daysUntil = ($this->weekday - $from->dayOfWeek + 7) % 7;
        $candidate = $from->startOfDay()->addDays($daysUntil)->setTimeFromTimeString($this->starts_at);

        return $candidate->lte($from) ? $candidate->addWeek() : $candidate;
    }
}
