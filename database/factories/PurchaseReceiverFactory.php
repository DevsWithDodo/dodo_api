<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Transactions\PurchaseReceiver;
use Faker\Generator as Faker;

$factory->define(PurchaseReceiver::class, function (Faker $faker) {
    return [
        'amount' => $faker->randomFloat($nbMacDecimals = 2, $min = 10, $max = 200)
    ];
});
