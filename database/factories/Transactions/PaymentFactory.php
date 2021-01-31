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
            'amount' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 10, $max = 200),
            'note' => $this->faker->word,
            'created_at' => Carbon::now()->subDays(rand(0, 30))->subMinutes(rand(1, 1440)),
            'updated_at' => Carbon::now()->subDays(rand(0, 30))->subMinutes(rand(1, 1440))
        ];
    }
}
