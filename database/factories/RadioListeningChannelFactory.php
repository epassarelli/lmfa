<?php

namespace Database\Factories;

use App\Models\RadioListeningChannel;
use App\Models\RadioSignal;
use Illuminate\Database\Eloquent\Factories\Factory;

class RadioListeningChannelFactory extends Factory
{
    protected $model = RadioListeningChannel::class;

    public function definition(): array
    {
        return [
            'radio_signal_id' => RadioSignal::factory(),
            'label' => 'Escuchar en vivo',
            'channel_type' => 'stream',
            'platform' => 'stream_directo',
            'url' => 'https://example.test/stream',
            'is_primary' => true,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
