<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use App\User;
use App\Model\Task;
use App\Model\Tag;
use JWTAuth;

class TaskTest extends TestCase
{
    use RefreshDatabase;
    use DatabaseMigrations;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        $this->withHeaders([
            'Authorization' => 'Bearer ' . JWTAuth::fromUser($this->user),
        ]);

        $this->artisan('db:seed');
    }

    public function test_user_can_store_task()
    {
        $data = [
            'title' => 'Tarefa Teste',
            'tag_id' => Tag::all()->random()->id,
            'when' => 'today',
            'period' => 'sunrise',
        ];

        $response = $this->json('POST', '/api/v1/tasks', $data);

        $response->assertStatus(201);
        $response->assertSeeText('Tarefa Teste');
        $response->assertSeeText('task');
    }

    public function test_user_can_update_task()
    {
        $task = factory(Task::class)->create();

        $data = [
            'title' => 'changed',
        ];

        $response = $this->json('PUT', '/api/v1/tasks/' . $task->id, $data);

        $response->assertStatus(200);
    }

    public function test_user_cant_see_another_user_task()
    {
        $another_user = factory(User::class)->create();
        $another_task = factory(Task::class)->create();
        $another_task->user_id = $another_user->id;
        $another_task->save();

        $response = $this->json('GET', '/api/v1/tasks/' . $another_task->id);

        $response->assertStatus(403);
        $response->assertJson([]);
    }

    public function test_user_cant_update_another_user_task()
    {
        $another_user = factory(User::class)->create();
        $another_task = factory(Task::class)->create();
        $another_task->user_id = $another_user->id;
        $another_task->save();

        $response = $this->json('PUT', '/api/v1/tasks/' . $another_task->id);

        $response->assertStatus(422);
    }

    public function test_user_cant_delete_another_user_task()
    {
        $another_user = factory(User::class)->create();
        $another_task = factory(Task::class)->create();
        $another_task->user_id = $another_user->id;
        $another_task->save();

        $response = $this->json('DELETE', '/api/v1/tasks/' . $another_task->id);

        $response->assertStatus(500);
    }
}
