<?php

namespace Tests\Feature;

use App\Model\Habit;
use App\Model\HabitCompleted;
use App\Model\HabitSetta;
use App\Model\Task;
use App\Model\Timer;
use App\Model\TaskCompleted;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use JWTAuth;

class PlannerTest extends TestCase
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

    /**
     * Somente mostrar tarefas com schedule true na aba Hoje
     */
    public function test_planner_today_not_show_task_with_schedule_false()
    {
        $today = Carbon::today();

        factory(Task::class)->create([
            'title' => 'Tarefa 1',
            'schedule' => true,
            'due_date' => $today,
        ]);

        factory(Task::class)->create([
            'title' => 'Tarefa 2',
            'schedule' => false,
            'due_date' => $today,
        ]);

        $response = $this->json('GET', '/api/planner/today');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertSeeText('Tarefa 1');
        $response->assertDontSeeText('Tarefa 2');
    }

    /**
     * Somente mostrar tarefas com schedule true na aba Amanhã
     */
    public function test_planner_tomorrow_dont_show_task_with_schedule_false()
    {
        $tomorrow = Carbon::tomorrow();

        factory(Task::class)->create([
            'title' => 'Tarefa 1',
            'schedule' => true,
            'due_date' => $tomorrow,
        ]);

        factory(Task::class)->create([
            'title' => 'Tarefa 2',
            'schedule' => false,
            'due_date' => $tomorrow,
        ]);

        $response = $this->json('GET', '/api/planner/tomorrow');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertSeeText('Tarefa 1');
        $response->assertDontSeeText('Tarefa 2');
    }

    /**
     * Não mostra subtasks na listagem principal (como pai)
     */
    public function test_planner_dont_show_subtask()
    {
        $today = Carbon::today();

        $parent_task = factory(Task::class)->create([
            'title' => 'Tarefa pai',
            'due_date' => $today,
            'schedule' => true,
        ]);

        $child_task = factory(Task::class)->create([
            'title' => 'Tarefa filho',
            'due_date' => $today,
            'schedule' => true,
            'parent_id' => $parent_task->id,
        ]);

        // today
        $response = $this->json('GET', '/api/planner/today');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertSeeText('Tarefa pai');

        // tomorrow
        $tomorrow = Carbon::tomorrow();
        $parent_task->due_date = $tomorrow;
        $parent_task->save();
        $child_task->due_date = $tomorrow;
        $parent_task->save();

        $response = $this->json('GET', '/api/planner/tomorrow');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertSeeText('Tarefa pai');
    }

    /**
     * Cadastrar tarefas para todos os dias do mês atual, anterior e próximo e contar quantas tarefas deveriam mostrar em cada aba
     */
    public function test_planner_tasks_count()
    {
        $firstDayOfPrevMonth = Carbon::today()
            ->firstOfMonth()
            ->subMonth();
        $lastDayOfNextMonth = Carbon::today()
            ->firstOfMonth()
            ->addMonth()
            ->lastOfMonth();

        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $diffDays = $firstDayOfPrevMonth->diffInDays($lastDayOfNextMonth) + 1; // total days

        // create 1 task for each day
        for ($i = 1; $i <= $diffDays; $i++) {
            $currentDay = Carbon::today()
                ->firstOfMonth()
                ->addDays($i - 1);

            $schedule = false;

            if ($i == $today->format('j') || $i == $tomorrow->format('j')) {
                $schedule = true;
            }

            factory(Task::class)->create([
                'title' => "Tarefa #${i} > " . $currentDay->format('d-m-Y'),
                'due_date' => $currentDay,
                'schedule' => $schedule,
            ]);
        }

        // today
        $response = $this->json('GET', '/api/planner/today');
        $response->assertJsonCount(1);

        // tomorrow
        $response = $this->json('GET', '/api/planner/tomorrow');
        $response->assertJsonCount(1);

        // this_week
        $response = $this->json('GET', '/api/planner/this_week');
        $response->assertJsonCount(7); // 7 dias na semana

        // next_week
        $response = $this->json('GET', '/api/planner/next_week');
        $response->assertJsonCount(7);

        // this_month
        $quantityDaysOfCurrentMonth =
            Carbon::today()
                ->firstOfMonth()
                ->diffInDays(Carbon::today()->lastOfMonth()) + 1;

        $response = $this->json('GET', '/api/planner/this_month');
        $response->assertJsonCount($quantityDaysOfCurrentMonth); // quantidade de dias do mês atual

        // next_month
        $quantityDaysOfNextMonth =
            Carbon::today()
                ->firstOfMonth()
                ->addMonth()
                ->diffInDays(
                    Carbon::today()
                        ->firstOfMonth()
                        ->addMonth()
                        ->lastOfMonth()
                ) + 1;

        $response = $this->json('GET', '/api/planner/next_month');
        $response->assertJsonCount($quantityDaysOfNextMonth);
    }

    /**
     * Quando subtask é finalizada a tarefa pai ainda aparece na aba Hoje
     */
    public function test_planner_complete_child_task_shows_parent()
    {
        $today = Carbon::today();

        $task_parent = factory(Task::class)->create([
            'title' => "Tarefa Pai",
            'due_date' => $today,
            'schedule' => true,
            'parent_id' => null,
        ]);

        $task_child = factory(Task::class)->create([
            'title' => "Tarefa Filho",
            'due_date' => $today,
            'schedule' => true,
            'parent_id' => $task_parent->id,
            'completed_at' => $today,
        ]);

        $response = $this->json('GET', '/api/planner/today');
        $response->assertJsonCount(1);
        $response->assertStatus(200);
        $response->assertSeeText('Tarefa Pai');
    }
}
