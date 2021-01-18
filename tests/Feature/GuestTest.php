<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use App\Transactions\Purchase;
use App\Group;
use App\User;

class GuestTest extends TestCase
{
    use RefreshDatabase;
    protected Group $group;

    public function setUp(): void
    {
        parent::setUp();

        //create group with users
        Artisan::call('migrate');
        $this->group = Group::factory()->create();
        $users = User::factory()->count(rand(3, 20))->create(['password' => null]);
        $admin = $users->first();
        $this->group->members()->attach($admin->id, ['nickname' => $admin->username, 'is_admin' => true]);
        foreach ($users->except($admin->id) as $guest) {
            $this->group->members()->attach($guest->id, ['nickname' => $guest->username]);
        }
        //create random purchases to modify balances
        for ($i = 0; $i < 10; $i++) {
            $buyer = $this->group->members->random();
            $purchase = Purchase::factory()->make();
            $user_ids = [];
            foreach ($users->except($buyer->id) as $user) {
                if (rand(0, 1) == 0)
                    $user_ids[] = ['user_id' => $user->id];
            }
            if (count($user_ids) == 0) $user_ids[]['user_id'] = $this->group->members->random()->id;

            $response = $this->actingAs($buyer, 'api')
                ->postJson(route('purchases.store'), [
                    'name' => $purchase->name,
                    'group' => $this->group->id,
                    'amount' => $purchase->amount,
                    'receivers' => $user_ids
                ]);
            $response->assertStatus(201);
        }
        $this->group->recalculateBalances();
        $balance = 0;
        foreach ($this->group->members as $member) {
            $balance = bcadd($balance, $member->member_data->balance);
        }
        $this->assertTrue(0 == $balance);
    }

    /**
     * Merge a guest
     * @test
     * @return void
     */
    public function mergeGuest()
    {
        $admin = $this->group->admins->first();
        $guest = $this->group->members->except($admin->id)->random();
        $member = $this->group->members->except([$admin->id, $guest->id])->random();
        $random_user = $this->group->members->except([$guest->id, $member->id])->random();
        $this->assertNotEmpty($admin);
        $this->assertNotEmpty($guest);
        $this->assertNotEmpty($member);
        $this->assertNotEquals($guest->id, $member->id);

        $guest_balance = $guest->member_data->balance;
        $member_balance = $member->member_data->balance;
        $random_user_balance = $random_user->member_data->balance;

        $guest->mergeDataInto($member->id);
        $this->group->recalculateBalances();

        $this->assertEquals(0, $guest->member_data->balance);
        $this->assertEquals(bcadd($guest_balance, $member_balance), $member->member_data->balance);
        $this->assertEquals($random_user_balance, $random_user->member_data->balance);

        $balance = 0;
        foreach ($this->group->members as $member) {
            $balance = bcadd($balance, $member->member_data->balance);
        }
        $this->assertEquals(0, $balance);
    }
}
