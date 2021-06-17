<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Tag;
use App\User;
use App\Model\Task;
use Faker\Generator as Faker;

$factory->define(Task::class, function (Faker $faker) {
    return [
        'title' => $faker->text(20),
        'user_id' => User::all()->random()->id,
        'tag_id' => Tag::all()->random()->id,
        'period' => 'sunrise',
        'schedule' => true,
    ];
});
