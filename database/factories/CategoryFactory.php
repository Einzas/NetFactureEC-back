<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(), // nombre aleatorio
            'description' => $this->faker->sentence(), // descripción aleatoria
            'status' => $this->faker->boolean(90), // 90% chance que sea true
        ];
    }
}
