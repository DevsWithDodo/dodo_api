<?php

namespace Database\Factories;

use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'username' => $this->faker->unique()->userName,
            'password' => Hash::make('1234'),
            'password_reminder' => $this->faker->word,
            'default_currency' => "HUF"
        ];
    }
}
