<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Model\Config;

$factory->define(Config::class, function (Faker $faker) {
    $title = $faker->text(20);
    $title = str_replace(' ', '_', $title);
    $title = str_replace('.', '', strtolower($title));

    return [
        'module' => str_replace('.', '', strtolower($faker->text(15))),
        'title' => $title,
        'description' => $faker->text(30),
        'default' => $faker->randomElement([true, false]),
    ];
});
