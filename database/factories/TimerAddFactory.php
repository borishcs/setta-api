<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Timer;
use App\Model\TimerAdd;
use Faker\Generator as Faker;

$factory->define(TimerAdd::class, function (Faker $faker) {
    return [
        'timer_id' => Timer::all()->random()->id,
        'add' => $faker->time(),
        'type' => $faker->randomElement(['1', '2']),
    ];
});
