<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\User;
use App\Model\Task;
use App\Model\Habit;
use App\Model\Tag;
use JWTAuth;

class HabitTest extends TestCase
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

    public function test_user_can_store_habit_builder()
    {
        $data = [
            'title' => 'Tarefa Teste',
            'tag_id' => Tag::all()->random()->id,
            'period' => 'sunrise',
            'repeat' => [1, 2, 3],
        ];

        $response = $this->json('POST', '/api/v1/habits', $data);

        $response->assertStatus(201);
        $response->assertSeeText('Tarefa Teste');
    }

    public function test_user_can_store_habit_from_task()
    {
        $another_task = factory(Task::class)->create();

        $data = [
            'task_id' => $another_task->id,
            'title' => 'Tarefa Teste',
            'tag_id' => Tag::all()->random()->id,
            'period' => 'sunrise',
            'repeat' => [1, 2, 3],
        ];

        $response = $this->json('POST', '/api/v1/habits', $data);

        $response->assertStatus(201);
        $response->assertSeeText('Tarefa Teste');
    }

    public function test_user_can_store_habit_from_task_final_date()
    {
        $DaysAgo = Carbon::today()->addDays(15);
        $today = $DaysAgo->format('Y-m-d H:i:s');

        $data = [
            'title' => 'Tarefa Teste',
            'tag_id' => Tag::all()->random()->id,
            'period' => 'sunrise',
            'final_date' => $today,
            'repeat' => [0, 1, 2, 3, 4, 5, 6],
        ];

        $response = $this->json('POST', '/api/v1/habits', $data);

        $response->assertStatus(201);
        $response->assertSeeText('Tarefa Teste');

        $response = $this->json('GET', '/api/v1/planner/today');
        $response->assertJsonCount(1);

        $response = $this->json('GET', '/api/v1/planner/tomorrow');
        $response->assertJsonCount(1);
    }

    public function test_user_can_store_two_habit_from_task_final_date()
    {
        $DaysAgo = Carbon::today()->addDays(15);
        $today = $DaysAgo->format('Y-m-d H:i:s');

        $data = [
            'title' => 'Tarefa Teste #1',
            'tag_id' => Tag::all()->random()->id,
            'period' => 'sunrise',
            'final_date' => $today,
            'repeat' => [0, 1, 2, 3, 4, 5, 6],
        ];

        $response = $this->json('POST', '/api/v1/habits', $data);

        $data2 = [
            'title' => 'Tarefa Teste#2',
            'tag_id' => Tag::all()->random()->id,
            'period' => 'sunrise',
            'final_date' => $today,
            'repeat' => [0, 1, 2, 3, 4, 5, 6],
        ];

        $response = $this->json('POST', '/api/v1/habits', $data2);

        $response->assertStatus(201);
        $response->assertSeeText('Tarefa Teste');

        $response = $this->json('GET', '/api/v1/planner/today');
        $response->assertJsonCount(2);

        $response = $this->json('GET', '/api/v1/planner/tomorrow');
        $response->assertJsonCount(2);
    }
}
