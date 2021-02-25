<?php

namespace Database\Factories;

use App\Group;
use App\Http\Controllers\CurrencyController;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Group::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->text(20),
            'invitation' => Str::random(20),
            'currency' => array_rand(CurrencyController::currencyRates()['rates']),
            'admin_approval' => $this->faker->boolean(),
            'boosted' => $this->faker->boolean()
        ];
    }
}
