<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Habit;
use App\Model\Tag;
use App\Model\Task;
use App\Model\Timer;
use App\User;
use Faker\Generator as Faker;

$factory->define(Timer::class, function (Faker $faker) {
    $task_id = null;
    $habit_id = null;
    $tag_id = null;

    if (count(Habit::all())) {
        $habit_id = Habit::all()->random()->id;
    }

    if (count(Task::all())) {
        $task_id = Task::all()->random()->id;
    }

    if (count(Tag::all())) {
        $tag_id = Tag::all()->random()->id;
    }

    return [
        'user_id' => User::all()->random()->id,
        'task_id' => $task_id,
        'habit_id' => $habit_id,
        'tag_id' => $tag_id,
        'estimated_time' => $faker->time(),
        'estimated_used_time' => $faker->time(),
        'rest_time' => $faker->time(),
        'rest_used_time' => $faker->time(),
        'started_at' => $faker->dateTime,
        'finished_at' => $faker->dateTime,
        'created_at' => $faker
            ->dateTimeBetween('-2 years', 'now')
            ->format('Y-m-d H:i:s'),
    ];
});
