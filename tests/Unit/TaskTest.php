<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_crea_tarea()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Tarea de prueba',
            'description' => 'Descripción',
            'status' => 'pendiente',
            'expiration_date' => now()->addDay()->toDateString(),
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'Tarea de prueba'
            ]);
    }

    public function test_usuario_ve_tareas()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Tarea propia'
        ]);

        Task::factory()->create(); // Otra tarea de otro user

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Tarea propia'])
            ->assertJsonMissing(['title' => 'Otra tarea']);
    }
}
