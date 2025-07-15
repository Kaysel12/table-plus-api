<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use App\Models\Priority;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() === 0) {
            User::factory(10)->create();
        }
        
        if (Priority::count() === 0) {
            Priority::factory(5)->create();
        }

        Task::factory(10)->create();
    }
}