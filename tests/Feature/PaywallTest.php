<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use JWTAuth;
use Tests\TestCase;

class PaywallTest extends TestCase
{
    use RefreshDatabase;
    use DatabaseMigrations;

    protected $user;
    protected $userMail;
    protected $userPass;
    protected $userName;

    public function setUp(): void
    {
        parent::setUp();

        $this->userMail = 'unittests@setta.co';
        $this->userPass = '123456';
        $this->userName = 'Unit Test';

        $this->user = factory(User::class)->create([
            'email' => $this->userMail,
            'password' => $this->userPass,
            'name' => $this->userName,
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . JWTAuth::fromUser($this->user),
        ]);

        $this->artisan('db:seed');
    }

    public function test_paywall__can_see_status()
    {
        $register_user = $this->json('POST', '/api/register', [
            'email' => $this->userMail,
            'password' => $this->userPass,
            'name' => $this->userName,
        ]);

        $response = $this->json('GET', '/api/paywall/status');

        $response->assertStatus(200);
    }

    public function test_paywall__can_see_customer()
    {
        $register_user = $this->json('POST', '/api/register', [
            'email' => $this->userMail,
            'password' => $this->userPass,
            'name' => $this->userName,
        ]);

        $response = $this->json('GET', '/api/paywall/customer');

        $response->assertStatus(200);
        $response->assertSeeText($this->user->email);
    }

    public function test_paywall__can_list_plans()
    {
        $register_user = $this->json('POST', '/api/register', [
            'email' => $this->userMail,
            'password' => $this->userPass,
            'name' => $this->userName,
        ]);

        $response = $this->json('GET', '/api/paywall/plans');

        $response->assertStatus(200);
        $response->assertSeeText('plan');
    }

    public function test_paywall__can_list_products()
    {
        $register_user = $this->json('POST', '/api/register', [
            'email' => $this->userMail,
            'password' => $this->userPass,
            'name' => $this->userName,
        ]);

        $response = $this->json('GET', '/api/paywall/products');

        $response->assertStatus(200);
        $response->assertSeeText('product');
    }

    public function test_paywall__can_list_coupons()
    {
        $register_user = $this->json('POST', '/api/register', [
            'email' => $this->userMail,
            'password' => $this->userPass,
            'name' => $this->userName,
        ]);

        $response = $this->json('GET', '/api/paywall/coupons');

        $response->assertStatus(200);
        $response->assertSeeText('coupon');
    }

    // test_is_premium
    public function test_planner__24hrs_free()
    {
        $response = $this->json('POST', '/api/register', [
            'email' => $this->userMail,
            'password' => $this->userPass,
            'name' => $this->userName,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['premium' => true]);
    }

    public function test_planner__24hrs_free_end()
    {
        $twoDaysAgo = Carbon::today()->subDays(2);

        $response = $this->json('POST', '/api/register', [
            'email' => $this->userMail,
            'password' => $this->userPass,
            'name' => $this->userName,
        ]);

        $updateUser = User::where('email', $this->userMail)->first();
        $updateUser->created_at = $twoDaysAgo->format('Y-m-d H:i:s');
        $updateUser->updated_at = $twoDaysAgo->format('Y-m-d H:i:s');
        $updateUser->save();

        $response = $this->json('POST', '/api/login', [
            'email' => $this->userMail,
            'password' => $this->userPass,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['premium' => false]);
    }

    public function test_planner__can_subscribe()
    {
        $registerUser = $this->json('POST', '/api/register', [
            'email' => $this->userMail,
            'password' => $this->userPass,
            'name' => $this->userName,
        ]);

        $response = $this->json('POST', '/api/paywall/subscribe', [
            'plan' => 'price_1H0WriJq7ibvKaXDhU4gfQQd',
        ]);

        $response->assertOk();
    }
}
