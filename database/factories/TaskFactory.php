<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\Priority;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed']),
            'user_id' => User::factory(),
            'priority_id' => Priority::factory(),
        ];
    }
}
