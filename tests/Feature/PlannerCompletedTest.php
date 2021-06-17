<?php

namespace Tests\Feature;

use App\Model\Habit;
use App\Model\HabitCompleted;
use App\Model\Task;
use App\Model\TaskCompleted;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use JWTAuth;

class PlannerCompletedTest extends TestCase
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

    /**
     * verificar se tarefa finalizada aparece em outra aba que não seja em Finalizadas
     */
    public function test_planner_completed__cant_see_completed_tasks_in_another_tab()
    {
        $today = Carbon::today();

        $task = factory(Task::class)->create([
            'title' => 'Tarefa #1',
            'date' => $today,
            'schedule' => true,
        ]);

        factory(TaskCompleted::class)->create([
            'task_id' => $task->id,
            'completed_at' => $today,
        ]);

        $this->assertDatabaseHas('tasks_completed', [
            'task_id' => $task->id,
        ]);

        $response = $this->json('GET', '/api/planner/completed');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertSeeText('Tarefa #1');

        $response = $this->json('GET', '/api/planner/today');
        $response->assertJsonCount(0);

        $response = $this->json('GET', '/api/planner/tomorrow');
        $response->assertJsonCount(0);

        $response = $this->json('GET', '/api/planner/this_week');
        $response->assertJsonCount(0);

        $response = $this->json('GET', '/api/planner/next_week');
        $response->assertJsonCount(0);

        $response = $this->json('GET', '/api/planner/this_month');
        $response->assertJsonCount(0);

        $response = $this->json('GET', '/api/planner/next_month');
        $response->assertJsonCount(0);
    }

    /**
     * verificar se hábito finalizado aparece em hoje ou amanhã caso esteja como finalizado
     */
    public function test_planner_completed__cant_see_completed_habits_in_another_tab()
    {
        $today = Carbon::today();

        $habit = factory(Habit::class)->create();

        factory(HabitCompleted::class)->create([
            'habit_id' => $habit->id,
            'completed_at' => $today,
        ]);

        $this->assertDatabaseHas('habits_completed', [
            'habit_id' => $habit->id,
        ]);

        $response = $this->json('GET', '/api/planner/completed');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertSeeText(json_encode($habit->title));

        $response = $this->json('GET', '/api/planner/today');
        $response->assertJsonCount(0);

        $response = $this->json('GET', '/api/planner/tomorrow');
        $response->assertJsonCount(0);

        $response = $this->json('GET', '/api/planner/this_week');
        $response->assertJsonCount(0);

        $response = $this->json('GET', '/api/planner/next_week');
        $response->assertJsonCount(0);

        $response = $this->json('GET', '/api/planner/this_month');
        $response->assertJsonCount(0);

        $response = $this->json('GET', '/api/planner/next_month');
        $response->assertJsonCount(0);
    }

    /**
     * verificar ordem das finalizadas
     */
    public function test_planner_completed__items_completed_order()
    {
        $today = Carbon::today();

        $first_task = factory(Task::class)->create([
            'title' => 'Tarefa finalizada #1',
            'date' => $today,
            'schedule' => true,
        ]);
        factory(TaskCompleted::class)->create([
            'task_id' => $first_task->id,
            'completed_at' => $today->addHours(1),
        ]);

        $first_habit = factory(Habit::class)->create();
        factory(HabitCompleted::class)->create([
            'habit_id' => $first_habit->id,
            'completed_at' => $today->addHours(2),
        ]);

        $second_task = factory(Task::class)->create([
            'title' => 'Tarefa finalizada #2',
            'date' => $today,
            'schedule' => true,
        ]);
        factory(TaskCompleted::class)->create([
            'task_id' => $second_task->id,
            'completed_at' => $today->addHours(3),
        ]);

        $second_habit = factory(Habit::class)->create();
        factory(HabitCompleted::class)->create([
            'habit_id' => $second_habit->id,
            'completed_at' => $today->addHours(4),
        ]);

        $response = $this->json('GET', '/api/planner/completed');
        $response->assertStatus(200);
        $response->assertJsonCount(4, $today->format('Y-m-d'));

        $correct_order = [
            json_encode($second_habit->title),
            $second_task->title,
            json_encode($first_habit->title),
            $first_task->title,
        ];
        $response->assertSeeTextInOrder($correct_order, true);
    }

    /**
     * verificar hábitos finalizados múltiplas vezes e ordem
     */
    public function test_planner_completed__habits_completed_multiple_times()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $twoDaysAgo = Carbon::today()->subDays(2);

        $habit = factory(Habit::class)->create();

        factory(HabitCompleted::class)->create([
            'habit_id' => $habit->id,
            'completed_at' => $twoDaysAgo,
        ]);

        factory(HabitCompleted::class)->create([
            'habit_id' => $habit->id,
            'completed_at' => $yesterday,
        ]);

        factory(HabitCompleted::class)->create([
            'habit_id' => $habit->id,
            'completed_at' => $today,
        ]);

        $response = $this->json('GET', '/api/planner/completed');
        $response->assertStatus(200);
        $response->assertJsonCount(3);

        $correct_order = [$today, $yesterday, $twoDaysAgo];
        $response->assertSeeTextInOrder($correct_order, true);
    }

    /**
     * permissões: verificar se usuário consegue visualizar task ou hábitos finalizados de outro usuário
     */
    public function test_planner_completed__user_cant_see_another_user_completeds()
    {
        $another_user = factory(User::class)->create();
        $another_task = factory(Task::class)->create([
            'user_id' => $another_user->id,
        ]);

        factory(TaskCompleted::class)->create([
            'task_id' => $another_task->id,
            'completed_at' => Carbon::today(),
        ]);

        $response = $this->json('GET', '/api/planner/completed');
        $response->assertStatus(200);
        $response->assertJsonCount(0);
        $response->assertDontSeeText($another_task->title);
    }
}
