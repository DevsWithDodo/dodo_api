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
            'amount' => encrypt($this->faker->randomFloat($nbMaxDecimals = 2, $min = 10, $max = 200)),
            'original_amount' => encrypt($this->faker->randomFloat($nbMaxDecimals = 2, $min = 10, $max = 200)),
            'name' => encrypt($this->faker->text(20)),
            'created_at' => Carbon::now()->subDays(rand(0, 30))->subMinutes(rand(1, 1440)),
            'updated_at' => Carbon::now()->subDays(rand(0, 30))->subMinutes(rand(1, 1440))
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Purchase $purchase) {
            //create random number of receivers
            $receivers = [];
            for ($i = 0; $i < rand(1, 5); $i++) {
                $receivers[] = [
                    'user_id' => $purchase->group->members->random()->id,
                ];
            }
            $purchase->syncReceivers($receivers, $purchase->group->currency, $purchase->group->currency);
        });
    }
}
