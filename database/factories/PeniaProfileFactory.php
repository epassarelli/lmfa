<?php

namespace Database\Factories;

use App\Models\PeniaProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PeniaProfileFactory extends Factory
{
    protected $model = PeniaProfile::class;

    public function definition(): array
    {
        $title = 'Peña '.$this->faker->unique()->city();

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(100, 999),
            'excerpt' => $this->faker->sentence(),
            'body' => '<p>'.$this->faker->paragraph().'</p>',
            'province_id' => 1,
            'city' => $this->faker->city(),
            'venue_type' => 'penia',
            'source_urls' => ['https://example.test/fuente'],
            'verification_status' => 'pending',
            'editorial_status' => 'draft',
        ];
    }
}
