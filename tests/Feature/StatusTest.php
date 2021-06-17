<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusTest extends TestCase
{
    public function test_status_true()
    {
        $response = $this->get('/api');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true
            ]);
    }
}
