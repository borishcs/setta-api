<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Period;
use Faker\Generator as Faker;

$factory->define(Period::class, function (Faker $faker) {
    return [
        'title' => $faker->text(20),
        'icon' => $faker->text(10),
        'start' => $faker->dateTime,
        'end' => $faker->dateTime,
    ];
});
