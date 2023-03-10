<?php

namespace Database\Factories\Transactions;

use App\Transactions\PurchaseReceiver;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseReceiverFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PurchaseReceiver::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'amount' => encrypt($this->faker->randomFloat($nbMaxDecimals = 2, $min = 10, $max = 200)),
            'original_amount' => encrypt($this->faker->randomFloat($nbMaxDecimals = 2, $min = 10, $max = 200)),
        ];
    }
}
