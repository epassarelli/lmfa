<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MitoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'titulo' => $this->faker->sentence(),
            'slug' => $this->faker->word(),
            'mito' => $this->faker->paragraph(),

            'foto' => $this->faker->image(),
            'publicar' => $this->faker->date(),
            'user_id' => '1',
            'visitas' => '1',
            'estado' => '1',
            'mito' => $this->faker->paragraph(),

        ];
    }
}
