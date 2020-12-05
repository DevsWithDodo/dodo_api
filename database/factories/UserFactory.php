<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use App\Http\Controllers\CurrencyController;

use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;

$factory->define(User::class, function (Faker $faker) {
    return [
        'username' => $faker->userName,
        'password' => Hash::make('1234'),
        'password_reminder' => $faker->word,
        'default_currency' => "HUF"
    ];
});
