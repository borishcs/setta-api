<?php

namespace Tests\Feature;

use App\Model\Task;
use App\Model\TaskCompleted;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use JWTAuth;

class PlannerInboxTest extends TestCase
{
    use RefreshDatabase;
    use DatabaseMigrations;

    protected $user;
    private $weekStartAt = Carbon::SUNDAY; // domingo

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        $this->withHeaders([
            'Authorization' => 'Bearer ' . JWTAuth::fromUser($this->user),
        ]);

        $this->artisan('db:seed');
    }

    public function test_planner_inbox_task_overdue_schedule_true()
    {
        $yesterday = Carbon::yesterday();

        factory(Task::class)->create([
            'title' => 'Tarefa Atrasada agendada',
            'schedule' => true,
            'date' => $yesterday,
        ]);

        $response = $this->json('GET', '/api/planner/inbox');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertSeeText('Tarefa Atrasada agendada');
    }

    public function test_planner_inbox_task_overdue_schedule_false()
    {
        $overdue = Carbon::today()
            ->startOfWeek($this->weekStartAt)
            ->subDays(2);

        factory(Task::class)->create([
            'title' => 'Tarefa Atrasada nao agendada',
            'schedule' => false,
            'date' => $overdue,
        ]);

        $response = $this->json('GET', '/api/planner/inbox');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertSeeText('Tarefa Atrasada nao agendada');
    }

    public function test_planner_inbox_task_without_date()
    {
        factory(Task::class)->create([
            'title' => 'Tarefa dem data',
            'schedule' => false,
            'date' => null,
        ]);

        $response = $this->json('GET', '/api/planner/inbox');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertSeeText('Tarefa dem data');
    }

    public function test_planner_inbox_tasks_count()
    {
        $yesterday = Carbon::yesterday();
        $overdue = Carbon::today()
            ->startOfWeek($this->weekStartAt)
            ->subDays(2);

        factory(Task::class, 2)->create([
            'title' => 'Tarefa Atrasada agendada',
            'schedule' => true,
            'date' => $yesterday,
        ]);

        factory(Task::class, 2)->create([
            'title' => 'Tarefa Atrasada nao agendada',
            'schedule' => false,
            'date' => $overdue,
        ]);

        factory(Task::class, 2)->create([
            'title' => 'Tarefa dem data',
            'schedule' => false,
            'date' => null,
        ]);

        $response = $this->json('GET', '/api/planner/inbox');

        $response->assertStatus(200);
        $response->assertJsonCount(6, 'data');
    }

    public function test_planner_inbox_tasks_completed()
    {
        $yesterday = Carbon::yesterday();

        factory(Task::class)->create([
            'title' => 'Tarefa Atrasada agendada',
            'schedule' => true,
            'date' => $yesterday,
        ]);

        $task_completed = factory(Task::class)->create([
            'title' => 'Tarefa Atrasada agendada finalizada',
            'schedule' => true,
            'date' => $yesterday,
        ]);

        factory(TaskCompleted::class)->create([
            'task_id' => $task_completed->id,
            'completed_at' => $yesterday,
        ]);

        $response = $this->json('GET', '/api/planner/inbox');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_planner_inbox_dont_show_next_tasks()
    {
        factory(Task::class)->create([
            'title' => 'Tarefa Futura #1',
            'schedule' => true,
            'date' => Carbon::tomorrow(),
        ]);

        factory(Task::class)->create([
            'title' => 'Tarefa Futura #2',
            'schedule' => true,
            'date' => Carbon::today()->addDays(2),
        ]);

        factory(Task::class)->create([
            'title' => 'Tarefa Futura #2',
            'schedule' => false,
            'date' => Carbon::today()->addDays(3),
        ]);

        $response = $this->json('GET', '/api/planner/inbox');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    public function test_planner_inbox_many_inserts()
    {
        factory(Task::class, 200)->create([
            'title' => 'Tarefa Atrasada',
            'schedule' => true,
            'date' => Carbon::yesterday(),
        ]);

        $response = $this->json('GET', '/api/planner/inbox');

        $response->assertStatus(200);
        $response->assertJsonCount(20, 'data');
        $response->assertJsonFragment(['total' => 200]);

        $response = $this->json('GET', '/api/planner/inbox?page=2');
        $response->assertJsonCount(20, 'data');
    }
}
