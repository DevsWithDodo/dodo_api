<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Request;
use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(Request::class, function (Faker $faker) {
    return [
        'name' => $faker->text(100),
        'created_at' => Carbon::now()->subMinutes(rand(1, 60)),
        'updated_at' => Carbon::now()->subMinutes(rand(1, 60))
    ];
});
