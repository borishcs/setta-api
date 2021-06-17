<?php

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class TestPaywallNotification extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $today = Carbon::now();
        $currentTime = $today->subHour(20);

        factory(User::class)->create([
            'created_at' => $currentTime,
        ]);

        $currentTime = $today->subHour(27);

        factory(User::class)->create([
            'created_at' => $currentTime,
        ]);
    }
}
