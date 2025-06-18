<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function testUsuarioRegistro()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'testJunio@example.com',
            'name' => 'Test User',
            'password' => 'password123'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token']);

        $this->assertDatabaseHas('users', [
            'email' => 'testJunio@example.com'
        ]);
    }

    public function testUsuarioLogin()
    {
        User::factory()->create([
            'email' => 'testJunio@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'testJunio@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token']);
    }
}
