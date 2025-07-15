<?php
// tests/Feature/AuthControllerTest.php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_register()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];
        
        $response = $this->postJson('/api/register', $data);
        
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user'
                ]);
        
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }
    
    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);
        
        $data = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];
        
        $response = $this->postJson('/api/login', $data);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user'
                ]);
    }
    
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);
        
        $data = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ];
        
        $response = $this->postJson('/api/login', $data);
        
        $response->assertStatus(401)
                ->assertJson(['error' => 'Invalid credentials']);
    }
}