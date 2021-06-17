<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Habit;
use App\Model\Period;
use App\Model\Tag;
use App\Model\Task;
use App\Model\HabitSetta;
use App\User;
use Faker\Generator as Faker;

$factory->define(Habit::class, function (Faker $faker) {
    return [
        'title' => $faker->text(20),
        'note' => $faker->text(500),
        'habit_setta_id' => HabitSetta::all()->random()->id,
        'user_id' => User::all()->random()->id,
        'tag_id' => Tag::all()->random()->id,
        'period_id' => Period::all()->random()->id,
        'parent_id' => null,
        'created_at' => $faker
            ->dateTimeBetween('-1 years', 'now')
            ->format('Y-m-d H:i:s'),
        'repeat' => $faker->randomElement([
            '[0]',
            '[1]',
            '[2]',
            '[3]',
            '[4]',
            '[5]',
            '[6]',
        ]),
    ];
});
