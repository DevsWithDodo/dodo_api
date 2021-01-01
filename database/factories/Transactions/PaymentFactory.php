<?php

namespace Database\Factories\Transactions;

use App\Transactions\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'amount' => $this->faker->randomFloat($nbMacDecimals = 2, $min = 10, $max = 200),
            'note' => $this->faker->word,
            'created_at' => Carbon::now()->subMinutes(rand(1, 60)),
            'updated_at' => Carbon::now()->subMinutes(rand(1, 60))
        ];
    }
}
