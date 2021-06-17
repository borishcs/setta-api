<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class NotificationTest extends TestCase
{
    use RefreshDatabase;
    use DatabaseMigrations;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    public function test_bomdia_notification_stress()
    {
        factory(User::class, 200)->create([
            'token' => '88a96def-845e-4dd8-be1d-31cb272dfb0d',
        ]);

        $response = $this->json('GET', '/api/notification/000000');

        $response->assertStatus(200);
    }

    public function test_notification_institutional_envio()
    {
        factory(User::class, 200)->create([
            'token' => '88a96def-845e-4dd8-be1d-31cb272dfb0d',
        ]);

        $response = $this->json('GET', '/api/notification/000000');

        $response->assertStatus(200);
    }
}
