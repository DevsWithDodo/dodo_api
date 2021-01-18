<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Transactions\Purchase;
use Illuminate\Support\Facades\Artisan;
use App\Group;
use App\User;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * buyer included in purchase's receivers
     */
    public function buyerIncluded()
    {
        for ($i = 0; $i < 5; $i++) {
            Artisan::call('migrate');
            $group = Group::factory()->create();
            $users = User::factory()->count(rand(3, 20))->create();
            $user_ids = [];
            foreach ($users as $user) {
                $group->members()->attach($user->id, ['nickname' => $user->username]);
                $user_ids[] = ['user_id' => $user->id];
            }
            $buyer = $group->members->first();
            $purchase = Purchase::factory()->make();

            $response = $this->actingAs($buyer, 'api')
                ->postJson(route('purchases.store'), [
                    'name' => $purchase->name,
                    'group' => $group->id,
                    'amount' => $purchase->amount,
                    'receivers' => $user_ids
                ]);
            $response->assertStatus(201);

            $balance = 0;
            foreach ($group->members as $member) {
                $balance = bcadd($balance, $member->member_data->balance);
            }
            $this->assertTrue(
                abs(($purchase->amount - ($purchase->amount / $users->count()))
                    - $group->member($buyer->id)->member_data->balance) < 0.01
            );
            foreach ($group->members->except($buyer->id) as $user) {
                $this->assertTrue(
                    abs((0 - ($purchase->amount / $users->count()))
                        - $group->member($user->id)->member_data->balance) < 0.01
                );
            }
            $this->assertTrue(0 == $balance);
        }
    }
    /**
     * @test
     * buyer not included in purchase's receivers
     */
    public function buyerNotIncluded()
    {
        for ($i = 0; $i < 5; $i++) {
            Artisan::call('migrate');
            $group = Group::factory()->create();
            $users = User::factory()->count(rand(3, 20))->create();
            foreach ($users as $user) {
                $group->members()->attach($user->id, ['nickname' => $user->username]);
            }
            $buyer = $group->members->first();
            $user_ids = [];
            foreach ($users->except($buyer->id) as $user) {
                $user_ids[] = ['user_id' => $user->id];
            }
            $purchase = Purchase::factory()->make();

            $response = $this->actingAs($buyer, 'api')
                ->postJson(route('purchases.store'), [
                    'name' => $purchase->name,
                    'group' => $group->id,
                    'amount' => $purchase->amount,
                    'receivers' => $user_ids
                ]);
            $response->assertStatus(201);

            $balance = 0;
            foreach ($group->members as $member) {
                $balance = bcadd($balance,  $member->member_data->balance);
            }
            $this->assertTrue(
                abs($purchase->amount
                    - $group->member($buyer->id)->member_data->balance) < 0.01
            );
            foreach ($group->members->except($buyer->id) as $user) {
                $this->assertTrue(
                    abs((0 - ($purchase->amount / ($users->count() - 1)))
                        - $group->member($user->id)->member_data->balance) < 0.01
                );
            }
            $this->assertTrue(0 == $balance);
        }
    }

    /**
     * @test
     * Update purchase, receivers are random.
     * Only asserts group balance.
     */
    public function Update()
    {
        for ($i = 0; $i < 5; $i++) {
            Artisan::call('migrate');
            $group = Group::factory()->create();
            $users = User::factory()->count(rand(3, 20))->create();
            foreach ($users as $user) {
                $group->members()->attach($user->id, ['nickname' => $user->username]);
            }
            $buyer = $group->members->first();
            $user_ids = $user_ids_2 = [];
            foreach ($users->except($buyer->id) as $user) {
                if (rand(0, 1) == 0)
                    $user_ids[] = ['user_id' => $user->id];
                if (rand(0, 1) == 0)
                    $user_ids_2[] = ['user_id' => $user->id];
            }
            if (count($user_ids) == 0) $user_ids[]['user_id'] = $group->members->random()->id;
            if (count($user_ids_2) == 0) $user_ids_2[]['user_id'] = $group->members->random()->id;

            $purchase = Purchase::factory()->make();
            $purchase2 = Purchase::factory()->make();

            $response = $this->actingAs($buyer, 'api')
                ->postJson(route('purchases.store'), [
                    'name' => $purchase->name,
                    'group' => $group->id,
                    'amount' => $purchase->amount,
                    'receivers' => $user_ids
                ]);
            $response->assertStatus(201);
            $id = $response->json()["transaction_id"];
            $response = $this->actingAs($buyer, 'api')
                ->putJson(route('purchases.update', $id),  [
                    'name' => $purchase2->name,
                    'amount' => $purchase2->amount,
                    'receivers' => $user_ids_2
                ]);
            $response->assertStatus(200);

            $balance = 0;
            foreach ($group->members as $member) {
                $balance = bcadd($balance,  $member->member_data->balance);
            }
            $this->assertTrue(0 == $balance);
        }
    }
}
