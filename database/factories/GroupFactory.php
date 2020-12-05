<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Group;
use App\Http\Controllers\CurrencyController;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Group::class, function (Faker $faker) {
    return [
        'name' => $faker->text(20),
        'anyone_can_invite' => array_rand([true, false]),
        'invitation' => Str::random(20),
        'currency' => "HUF"
    ];
});
