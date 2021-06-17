<?php

namespace Tests\Feature;

use App\Model\Task;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use JWTAuth;
use Illuminate\Support\Carbon;

class MomentTest extends TestCase
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
     * Este teste insere 4 tarefas para hoje e 5 para amanha
     * o resultado esperado é que a API retorne tarefas de amanha
     * como sugestão para colocar para hoje, ja que temos 4 ou menos tarefas.
     */

    public function test_morning_show_tasks_four_true()
    {
        factory(Task::class, 4)->create([
            'schedule' => true,
            'due_date' => Carbon::today(),
            'user_id' => $this->user->id,
        ]);

        factory(Task::class, 5)->create([
            'schedule' => true,
            'title' => 'Task Tomorrow',
            'due_date' => Carbon::tomorrow(),
            'user_id' => $this->user->id,
        ]);

        $response = $this->json('GET', '/api/v1/moment/morning');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    public function test_morning_show_tasks_four_false()
    {
        factory(Task::class, 5)->create([
            'title' => 'Task Today',
            'schedule' => true,
            'due_date' => Carbon::today(),
            'user_id' => $this->user->id,
        ]);

        factory(Task::class, 5)->create([
            'schedule' => true,
            'title' => 'Teste Tomorrow',
            'due_date' => Carbon::tomorrow(),
            'user_id' => $this->user->id,
        ]);

        $response = $this->json('GET', '/api/v1/moment/morning');

        $response->assertStatus(200);
        $response->assertDontSeeText('"title":"Teste Tomorrow"');
    }

    /**
     * Este teste é a notificação da noite, que mostra tarefas que era do dia
     * e nao foram feitas.
     */
    public function test_today_show_tasks_four_true()
    {
        factory(Task::class, 4)->create([
            'title' => 'Task Today',
            'schedule' => true,
            'due_date' => Carbon::today(),
            'user_id' => $this->user->id,
        ]);

        $response = $this->json('GET', '/api/v1/moment/todayLost');

        $response->assertStatus(200);
        $response->assertJsonCount(4, 'data');
        $response->assertSeeText('"title":"Task Today"');
    }

    public function test_today_show_tasks_four_false()
    {
        $task = factory(Task::class)->create([
            'title' => 'Task Today',
            'schedule' => true,
            'due_date' => Carbon::today(),
            'user_id' => $this->user->id,
        ]);
        $response2 = $this->json(
            'POST',
            '/api/v1/tasks/' . $task->id . '/completed'
        );
        $response2->assertStatus(200);

        $task = factory(Task::class)->create([
            'title' => 'Task Today Fail',
            'schedule' => true,
            'due_date' => Carbon::today(),
            'user_id' => $this->user->id,
        ]);

        $response = $this->json('GET', '/api/v1/moment/todayLost');

        $response->assertStatus(200);
        //$response->assertJsonCount(4, 'data');assertDontSeeText
        $response->assertSeeText('"title":"Task Today Fail"');
        $response->assertDontSeeText('"title":"Task Today"');
    }
}
