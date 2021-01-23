<?php

namespace Database\Factories;

use App\Request;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Request::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->text(100),
            'created_at' => Carbon::now()->subMinutes(rand(1, 60)),
            'updated_at' => Carbon::now()->subMinutes(rand(1, 60))
        ];
    }
}
