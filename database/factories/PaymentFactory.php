<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Transactions\Payment;
use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(Payment::class, function (Faker $faker) {
    return [
        'amount' => $faker->randomFloat($nbMacDecimals = 2, $min = 10, $max = 200),
        'note' => $faker->word,
        'created_at' => Carbon::now()->subMinutes(rand(1, 60)),
        'updated_at' => Carbon::now()->subMinutes(rand(1, 60))
    ];
});
