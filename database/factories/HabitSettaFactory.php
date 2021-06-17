<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;
use App\Model\Tag;
use App\Model\HabitSetta;

$factory->define(HabitSetta::class, function (Faker $faker) {
    return [
        'title' => $faker->text(20),
        'description' => $faker->text(500),
        'tag_id' => Tag::all()->random()->id,
        'image' => strtolower($faker->word()) . '.png',
    ];
});
