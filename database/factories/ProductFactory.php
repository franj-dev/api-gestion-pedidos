<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(), // Nombre del producto aleatorio
            'price' => $this->faker->randomFloat(2, 5, 100), // Precio con decimales (5.00-100.00) aleatorio
            'stock' => $this->faker->numberBetween(10, 50), // Stock inicial (10-50) unidades aleatorio
        ];
    }
}