<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Transactions\Purchase;
use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(Purchase::class, function (Faker $faker) {
    return [
        'amount' => $faker->randomFloat($nbMacDecimals = 2, $min = 10, $max = 200),
        'name' => $faker->word,
        'created_at' => Carbon::now()->subMinutes(rand(1, 60)),
        'updated_at' => Carbon::now()->subMinutes(rand(1, 60))
    ];
});
