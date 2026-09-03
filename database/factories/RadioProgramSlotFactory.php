<?php

namespace Database\Factories;

use App\Models\RadioProgram;
use App\Models\RadioProgramSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class RadioProgramSlotFactory extends Factory
{
    protected $model = RadioProgramSlot::class;

    public function definition(): array
    {
        return [
            'radio_program_id' => RadioProgram::factory(),
            'weekday' => 1,
            'starts_at' => '20:00:00',
            'ends_at' => '22:00:00',
            'timezone' => 'America/Argentina/Buenos_Aires',
            'is_active' => true,
        ];
    }
}
