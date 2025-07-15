<?php

namespace Database\Factories;

use App\Models\Priority;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriorityFactory extends Factory
{
    protected $model = Priority::class;

    public function definition()
    {
        return [
            'title' => $this->faker->randomElement(['Alta', 'Media', 'Baja']),
        ];
    }
}
