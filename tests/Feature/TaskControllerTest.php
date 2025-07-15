<?php
// tests/Feature/TaskControllerTest.php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;
    
    protected $user;
    protected $token;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }
    
    public function test_can_list_user_tasks()
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/tasks');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'due_date',
                            'status',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'meta'
                ]);
    }
    
    public function test_can_create_task()
    {
        $data = [
            'title' => 'New Task',
            'description' => 'Task description',
            'due_date' => now()->addDays(1)->toDateTimeString(),
            'reminder_minutes' => 30,
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/tasks', $data);
        
        $response->assertStatus(201)
                ->assertJson([
                    'data' => [
                        'title' => 'New Task',
                        'description' => 'Task description',
                    ]
                ]);
        
        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'user_id' => $this->user->id,
        ]);
    }
    
    public function test_can_show_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/tasks/{$task->id}");
        
        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $task->id,
                        'title' => $task->title,
                    ]
                ]);
    }
    
    public function test_cannot_show_other_user_task()
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/tasks/{$task->id}");
        
        $response->assertStatus(404);
    }
    
    public function test_can_update_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        
        $data = [
            'title' => 'Updated Task',
            'status' => 'completed',
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/tasks/{$task->id}", $data);
        
        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'title' => 'Updated Task',
                        'status' => 'completed',
                    ]
                ]);
        
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task',
            'status' => 'completed',
        ]);
    }
    
    public function test_can_delete_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/tasks/{$task->id}");
        
        $response->assertStatus(200);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
    
    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/tasks');
        $response->assertStatus(401);
    }
}