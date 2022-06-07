<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'mark' => $this->faker->word(),
            'model' => $this->faker->word(),
            'year' => $this->faker->year(),
        ];
    }
}
