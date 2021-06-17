<?php

namespace Tests\Feature;

use App\Model\Habit;
use App\Model\Tag;
use App\Model\Task;
use App\Model\Timer;
use App\Model\TimerAdd;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use JWTAuth;

class TimerTest extends TestCase
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
     * Teste para criar timer a partir de Tarefa
     */
    public function test_timer_can_store_from_task()
    {
        $task = factory(Task::class)->create([
            'title' => 'Tarefa #1',
            'schedule' => true,
            'date' => Carbon::today(),
        ]);

        $request = [
            "task_id" => $task->id,
            "estimated_time" => "00:40:00",
            "estimated_used_time" => "00:40:00",
            "rest_time" => "00:05:00",
            "rest_used_time" => "00:03:00",
            "started_at" => "2020-06-18 12:30:00",
            "finished_at" => "2020-06-18 13:00:00",
        ];

        $this->json('POST', '/api/timer', $request);

        $response = $this->json('GET', '/api/timer');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertSeeText($task->title);
    }

    /**
     * Teste para criar timer a partir de Hábito
     */
    public function test_timer_can_store_from_habit()
    {
        $habit = factory(Habit::class)->create();

        $request = [
            "habit_id" => $habit->id,
            "estimated_time" => "00:40:00",
            "estimated_used_time" => "00:40:00",
            "rest_time" => "00:05:00",
            "rest_used_time" => "00:03:00",
            "started_at" => "2020-06-18 12:30:00",
            "finished_at" => "2020-06-18 13:00:00",
        ];

        $this->json('POST', '/api/timer', $request);

        $response = $this->json('GET', '/api/timer');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertSeeText(json_encode($habit->title));
    }

    /**
     * Teste para criar timer a partir de Tag
     */
    public function test_timer_can_store_from_tag()
    {
        $tag = factory(Tag::class)->create();

        $request = [
            "tag_id" => $tag->id,
            "estimated_time" => "00:40:00",
            "estimated_used_time" => "00:40:00",
            "rest_time" => "00:05:00",
            "rest_used_time" => "00:03:00",
            "started_at" => "2020-06-18 12:30:00",
            "finished_at" => "2020-06-18 13:00:00",
        ];

        $this->json('POST', '/api/timer', $request);

        $response = $this->json('GET', '/api/timer');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertSeeText($tag->title);
    }

    /**
     * Teste para criar tempo adicional no timer
     */
    public function test_timer_can_add_time()
    {
        $task = factory(Task::class)->create([
            'title' => 'Tarefa #1',
            'schedule' => true,
            'date' => Carbon::today(),
        ]);

        $request = [
            "task_id" => $task->id,
            "estimated_time" => "00:40:00",
            "estimated_used_time" => "00:40:00",
            "rest_time" => "00:15:00",
            "rest_used_time" => "00:10:00",
            "started_at" => "2020-06-18 12:30:00",
            "finished_at" => "2020-06-18 13:00:00",
        ];

        $request['adds'] = [
            [
                "add" => "00:05:00",
                "type" => 1,
            ],
            [
                "add" => "00:08:00",
                "type" => 2,
            ],
        ];

        $this->json('POST', '/api/timer', $request);

        $response = $this->json('GET', '/api/timer');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertSeeText('00:05:00');
        $response->assertSeeText('00:08:00');
        $response->assertSeeText($task->title);
    }

    /**
     * Teste para deletar timer
     */
    public function test_timer_can_delete()
    {
        $task = factory(Task::class)->create([
            'title' => 'Tarefa #1',
            'schedule' => true,
            'date' => Carbon::today(),
        ]);

        $timer = factory(Timer::class)->create([
            'task_id' => $task->id,
        ]);

        $response = $this->json('DELETE', '/api/timer/' . $timer->id);
        $response->assertStatus(200);
        $response->assertSeeText($timer->id);

        $response = $this->json('GET', '/api/timer/');
        $response->assertJsonCount(0, 'data');
    }

    /**
     * Teste para deleter tempo adicional timer
     */
    public function test_timer_can_delete_add()
    {
        $task = factory(Task::class)->create([
            'title' => 'Tarefa #1',
            'schedule' => true,
            'date' => Carbon::today(),
        ]);

        $timer = factory(Timer::class)->create([
            'task_id' => $task->id,
        ]);

        $timer_add = factory(TimerAdd::class)->create([
            'timer_id' => $timer->id,
        ]);

        $response = $this->json(
            'DELETE',
            '/api/timer/' . $timer->id . '/add/' . $timer_add->id
        );

        $response->assertStatus(200);
        $response->assertSeeText($timer_add->add);

        $response = $this->json('GET', '/api/timer/');
        $response->assertJsonCount(1, 'data');
        $response->assertDontSeeText($timer_add->add);
    }
}
