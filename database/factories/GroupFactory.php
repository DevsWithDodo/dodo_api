<?php

namespace Database\Factories;

use App\Group;
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
            'anyone_can_invite' => array_rand([true, false]),
            'invitation' => Str::random(20),
            'currency' => "HUF"
        ];
    }
}
