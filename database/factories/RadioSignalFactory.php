<?php

namespace Database\Factories;

use App\Models\RadioSignal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RadioSignalFactory extends Factory
{
    protected $model = RadioSignal::class;

    public function definition(): array
    {
        $title = 'Radio '.$this->faker->unique()->city();

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(100, 999),
            'excerpt' => $this->faker->sentence(),
            'body' => '<p>'.$this->faker->paragraph().'</p>',
            'editorial_focus' => 'folklore',
            'transmission_modes' => ['streaming'],
            'city' => $this->faker->city(),
            'coverage_scope' => 'local',
            'source_urls' => ['https://example.test/radio'],
            'verification_status' => 'pending',
            'editorial_status' => 'draft',
        ];
    }
}
