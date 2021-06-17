<?php

namespace Tests\Feature;

use App\Model\Habit;
use App\Model\HabitCompleted;
use App\Model\HabitSetta;
use App\Model\Task;
use App\Model\Timer;
use App\Model\TaskCompleted;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use JWTAuth;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class StatisticTest extends TestCase
{
    use RefreshDatabase;
    use DatabaseMigrations;

    protected $user;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function setUp(): void
    {
        $today = Carbon::today();
        $currentDay = $today->subMonth();

        parent::setUp();

        $this->user = factory(User::class)->create([
            'created_at' => $currentDay,
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . JWTAuth::fromUser($this->user),
        ]);

        $this->artisan('db:seed');
    }

    public function test_statistic_geral_view()
    {
        $tomorrow = Carbon::tomorrow();

        factory(Task::class)->create([
            'title' => 'Tarefa 1',
            'schedule' => true,
            'date' => $tomorrow,
        ]);

        factory(Task::class)->create([
            'title' => 'Tarefa 2',
            'schedule' => true,
            'date' => $tomorrow,
        ]);

        factory(Task::class)->create([
            'title' => 'Tarefa 3',
            'schedule' => true,
            'date' => $tomorrow,
        ]);

        factory(TaskCompleted::class)->create([
            'user_id' => 1,
            'task_id' => 1,
            'completed_at' => $tomorrow,
        ]);

        factory(TaskCompleted::class)->create([
            'user_id' => 1,
            'task_id' => 2,
            'completed_at' => $tomorrow,
        ]);

        $response = $this->json('GET', '/api/statistic');

        $response->assertStatus(200);
        $response->assertJsonCount(11);
        $response->assertSeeText('"tasks_complete":2');
        //$response->assertSeeText('Tarefa 2');
        //$response->assertDontSeeText('Tarefa 1');
    }

    public function test_statistic_geral_view_task()
    {
        $today = Carbon::today();
        $currentDay = $today->subMonth();

        $diffDays = $currentDay->diffInDays(Carbon::today()); // total days

        // create 1 task for each day
        for ($i = 1; $i <= $diffDays; $i++) {
            $schedule = true;

            $task = factory(Task::class)->create([
                'user_id' => $this->user->id,
                'title' => "Tarefa #${i} > " . $currentDay->format('d-m-Y'),
                'date' => $currentDay,
                'schedule' => $schedule,
                'created_at' => $currentDay,
            ]);

            factory(TaskCompleted::class)->create([
                'user_id' => $this->user->id,
                'task_id' => $task->id,
                'completed_at' => $currentDay,
                'created_at' => $currentDay,
            ]);

            $currentDay = $currentDay->addDays(1);
        }

        $response = $this->json('GET', '/api/statistic');

        $response->assertStatus(200);
        $response->assertJsonCount(11);
        $response->assertSeeText('"tasks_total":' . $diffDays);
        $response->assertSeeText('"days_app":' . $diffDays);
        $response->assertSeeText('"tasks_average":"1');
    }

    public function test_statistic_geral_view_habit()
    {
        $today = Carbon::today();
        $currentDay = $today->subMonth();

        $diffDays = $currentDay->diffInDays(Carbon::today()); // total days

        // create 1 task for each day
        for ($i = 1; $i <= $diffDays; $i++) {
            $schedule = true;

            $habit = factory(Habit::class)->create([
                'user_id' => $this->user->id,
                'title' => "Habito #${i} > " . $currentDay->format('d-m-Y'),
                'tag_id' => 1,
                'created_at' => $currentDay,
            ]);

            factory(HabitCompleted::class)->create([
                'user_id' => $this->user->id,
                'habit_id' => $habit->id,
                'created_at' => $currentDay,
            ]);

            $currentDay = $currentDay->addDays(1);
        }

        $response = $this->json('GET', '/api/statistic');

        $firstDayMonth = Carbon::today()->firstOfMonth();

        $diffDaysMonth = $firstDayMonth->diffInDays(Carbon::today());

        $daysHabitLastMonth = $diffDays - $diffDaysMonth;

        $response->assertStatus(200);
        $response->assertJsonCount(11);
        $response->assertSeeText('"habits":' . $diffDays);
        $response->assertSeeText('"days_app":' . $diffDays);
        $response->assertSeeText('"habits_last_month":' . $daysHabitLastMonth);
    }

    public function test_statistic_geral_view_timer()
    {
        $today = Carbon::today();
        $currentDay = $today->subDay(7); //7 dias para validar

        // create 1 task for each day
        for ($i = 1; $i <= 7; $i++) {
            $timer = factory(Timer::class)->create([
                'tag_id' => 1,
                'user_id' => $this->user->id,
                'estimated_time' => "00:10:00",
                'estimated_used_time' => "00:10:00",
                'created_at' => $currentDay,
            ]);

            $currentDay = $currentDay->addDays(1);
        }

        $response = $this->json('GET', '/api/statistic');

        $response->assertStatus(200);
        $response->assertJsonCount(11);
        $response->assertSeeText('"hours_focus_last_days":"01:00:00');
        $response->assertSeeText('"hours_tools":"01:10:00');
    }

    public function test_statistic_geral_view_tag()
    {
        $today = Carbon::today();
        $currentDay = $today->subDay(7); //7 dias para validar

        // create 1 task for each day
        for ($i = 1; $i <= 7; $i++) {
            $timer = factory(Timer::class)->create([
                'tag_id' => 1,
                'user_id' => $this->user->id,
                'estimated_time' => "00:10:00",
                'estimated_used_time' => "00:10:00",
                'created_at' => $currentDay,
            ]);

            $habit = factory(Habit::class)->create([
                'user_id' => $this->user->id,
                'title' => "Habito #${i} > " . $currentDay->format('d-m-Y'),
                'tag_id' => 2,
                'created_at' => $currentDay,
            ]);

            $timer = factory(Timer::class)->create([
                'habit_id' => $habit->id,
                'user_id' => $this->user->id,
                'estimated_time' => "00:20:00",
                'estimated_used_time' => "00:20:00",
                'created_at' => $currentDay,
            ]);

            $task = factory(Task::class)->create([
                'user_id' => $this->user->id,
                'title' => "Task #${i} > " . $currentDay->format('d-m-Y'),
                'tag_id' => 3,
                'created_at' => $currentDay,
            ]);

            $timer = factory(Timer::class)->create([
                'task_id' => $task->id,
                'user_id' => $this->user->id,
                'estimated_time' => "00:30:00",
                'estimated_used_time' => "00:30:00",
                'created_at' => $currentDay,
            ]);

            $currentDay = $currentDay->addDays(1);
        }

        $response = $this->json('GET', '/api/statistic/tag');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertSeeText('"tag_origin":1');
        $response->assertSeeText('"total_hours_tag":"01:10:00');
        $response->assertSeeText('"tag_origin":2');
        $response->assertSeeText('"total_hours_tag":"02:20:00');
        $response->assertSeeText('"tag_origin":3');
        $response->assertSeeText('"total_hours_tag":"03:30:00');
    }
}
