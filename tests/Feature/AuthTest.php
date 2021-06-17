<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Generator as Faker;
use App\User;
use JWTAuth;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    use DatabaseMigrations;

    protected $user;

    public function test_register_user_and_exists()
    {
        $request = [
            "name" => "jOAO",
            "email" => "test@test.com",
            "password" => "123456",
        ];

        $response = $this->json('POST', '/api/register', $request);
        $response->assertStatus(200);

        $request = [
            "name" => "jOAO",
            "email" => "test@test.com",
            "password" => "888888",
        ];

        $response = $this->json('POST', '/api/register', $request);
        $response->assertStatus(401);
    }

    public function test_password_resset()
    {
        $this->userName = "jOAO";
        $this->userMail = "test2@test.com";
        $this->userPass = "123456";

        //register
        $response = $this->json('POST', '/api/register', [
            'name' => $this->userName,
            'email' => $this->userMail,
            'password' => $this->userPass,
        ]);
        $response->assertStatus(200);

        //step 1 - send mail
        $response2 = $this->json('POST', '/api/password/forgot', [
            'email' => $this->userMail,
            'code' => "",
        ]);
        $response2->assertJsonFragment(['success' => true]);

        //step 2 - send mail and code
        $array = json_decode($response2->getContent());

        $response3 = $this->json('POST', '/api/password/forgot', [
            'email' => $this->userMail,
            'code' => $array->code,
        ]);
        $response3->assertStatus(201);

        //step 3 - token and new password
        $token = $response3->getContent();
        $response4 = $this->json('POST', '/api/password/reset', [
            'token' => $token,
            'password' => '888888',
            'password_confirm' => '888888',
        ]);
        $response4->assertStatus(200);
    }
}
