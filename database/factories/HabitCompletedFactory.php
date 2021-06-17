<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Habit;
use App\User;
use Faker\Generator as Faker;
use App\Model\HabitCompleted;

$factory->define(HabitCompleted::class, function (Faker $faker) {
    return [
        'user_id' => User::all()->random()->id,
        'habit_id' => Habit::all()->random()->id,
        'completed_at' => $faker->dateTime,
    ];
});
