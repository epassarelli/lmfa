<?php

namespace Database\Factories;

use App\Models\RadioProgram;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RadioProgramFactory extends Factory
{
    protected $model = RadioProgram::class;

    public function definition(): array
    {
        $title = 'Ronda folklórica '.$this->faker->unique()->city();

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(100, 999),
            'excerpt' => $this->faker->sentence(),
            'body' => '<p>'.$this->faker->paragraph().'</p>',
            'is_folklore' => true,
            'source_urls' => ['https://example.test/programa'],
            'verification_status' => 'pending',
            'editorial_status' => 'draft',
        ];
    }
}
