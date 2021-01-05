<?php


namespace Database\Factories\Transactions;

use App\Transactions\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PurchaseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Purchase::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'amount' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 10, $max = 200),
            'name' => $this->faker->text(20),
            'created_at' => Carbon::now()->subMinutes(rand(1, 60)),
            'updated_at' => Carbon::now()->subMinutes(rand(1, 60))
        ];
    }
}
