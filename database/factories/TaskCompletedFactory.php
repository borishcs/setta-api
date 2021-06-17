<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Task;
use App\User;
use Faker\Generator as Faker;
use App\Model\TaskCompleted;

$factory->define(TaskCompleted::class, function (Faker $faker) {
    return [
        'user_id' => User::all()->random()->id,
        'task_id' => Task::all()->random()->id,
        'completed_at' => $faker->dateTime,
        'descount' => $faker->time(),
    ];
});
