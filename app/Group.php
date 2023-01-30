<?php

namespace App;

use App\Transactions\Reactions\Reaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';

    protected $fillable = ['name', 'currency', 'admin_approval', 'invitation', 'boosted', 'custom_categories'];

    protected $casts = [
        'custom_categories' => 'array'
    ];

    public function getMemberLimitAttribute()
    {
        return $this->boosted ? 30 : 8;
    }

    public function getCategoriesAttribute(): array
    {
        return array_merge(
            //default categories
            [
                'food',
                'groceries',
                'transport',
                'entertainment',
                'shopping',
                'health',
                'bills',
                'other'
            ],
            $this->boosted ? array_keys($this->custom_categories ?? []) : []
        );
    }

    public function delete()
    {
        $this->members()->detach($this->members);
        $this->purchases()->delete();
        $this->payments()->delete();
        $this->requests()->delete();

        return parent::delete();
    }

    public function members(): BelongsToMany
    {
        return $this
            ->belongsToMany('App\User', 'group_user')
            ->as('member_data')
            ->withPivot('nickname', 'is_admin', 'balance', 'approved')
            ->where('approved', true)
            ->withTimestamps();
    }

    public function unapprovedMembers(): BelongsToMany
    {
        return $this
            ->belongsToMany('App\User', 'group_user')
            ->as('member_data')
            ->withPivot('nickname', 'is_admin', 'balance', 'approved')
            ->where('approved', false)
            ->withTimestamps();
    }

    /**
     * Returns the member, fails if not found.
     * @param int the member's id
     */
    public function member($user_id)
    {
        return $this->members()->findOrFail($user_id);
    }

    public function isMember($user_id): bool
    {
        return $this->members()->find($user_id) != null;
    }


    /**
     *Returns the nickname of the member in the group.
     *Should be used always as this works with cache.
     *@param int the group's id
     *@param int the user's id
     *@return string the nickname or '$$deleted_user$$ if not found.
     */
    public static function nicknameOf(int $group_id, int $user_id): string
    {
       // App::setLocale(auth('api')->user()->language);
        return Cache::remember('group_' . $group_id . "_nicknames", now()->addSeconds(5), function () use ($group_id) {
            $nicknames = [];
            $group = Group::with('members')->findOrFail($group_id);
            foreach ($group->members as $member) {
                $nicknames[$member->id] = $member->member_data->nickname;
            }
            return $nicknames;
        })[$user_id] ?? __('general.deleted_member');
    }

    /**
     * Add the desired amount to the member's balance.
     * @param int $group_id
     * @param int $user_id the member's id
     * @param float $amount the amount to be added
     * @return void
     */
    public static function addToMemberBalance($group_id, $user_id, $amount)
    {
        $old_balance = DB::table('group_user')->where('group_id', $group_id)->where('user_id', $user_id)->get('balance')->first()->balance;
        DB::table('group_user')->where('group_id', $group_id)->where('user_id', $user_id)->update(['balance' => bcadd($old_balance, $amount)]);
        if (config('app.debug'))
            Log::info('updated member balance', ['user id' => $user_id, 'amount' => $amount, 'old balance' => $old_balance]);
    }

    public function guests(): BelongsToMany
    {
        return $this->members()->where('password', null);
    }

    public function admins(): BelongsToMany
    {
        return $this->members()->where('is_admin', true);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany('App\Transactions\Purchase');
    }

    public function purchaseReceivers(): HasMany
    {
        return $this->hasMany('App\Transactions\PurchaseReceiver');
    }

    public function payments(): HasMany
    {
        return $this->hasMany('App\Transactions\Payment');
    }

    public function requests(): HasMany
    {
        return $this->hasMany('App\Request');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    /**
     * Recalculates the balances based on the existing transactions.
     * Will not change the existing transactions.
     * @return void
     */
    public function recalculateBalances()
    {
        $this->load('members');
        foreach ($this->members as $member) {
            $balance = 0;
            $payments_payed = $this->payments()->where('payer_id', $member->id)->get();
            $payments_taken = $this->payments()->where('taker_id', $member->id)->get();
            $purchases_buyed = $this->purchases()->where('buyer_id', $member->id)->get();
            $purchases_received = DB::table('purchase_receivers')
                ->join('purchases', 'purchase_receivers.purchase_id', '=', 'purchases.id')
                ->where([
                    ['purchase_receivers.receiver_id', $member->id],
                    ['purchases.group_id', $this->id]
                ])->select('purchase_receivers.amount')->get();
            foreach ($payments_payed as $payment_payed) {
                $balance = bcadd($balance, $payment_payed->amount);
            }
            foreach ($payments_taken as $payment_taken) {
                $balance = bcsub($balance, $payment_taken->amount);
            }
            foreach ($purchases_buyed as $purchase_buyed) {
                $balance = bcadd($balance, $purchase_buyed->amount);
            }
            foreach ($purchases_received as $purchase_received) {
                $balance = bcsub($balance, $purchase_received->amount);
            }
            $member->member_data->update(['balance' => $balance]);
        }
    }

    public static function activeGroupQuery($lastActive = 30){
        return Group::where(function($query) use ($lastActive){
            $query->whereHas('purchases', function($query) use ($lastActive){
                $query->where('updated_at', '>', now()->subDays($lastActive));
            })
            ->orWhereHas('payments', function($query) use ($lastActive){
                $query->where('updated_at', '>', now()->subDays($lastActive));
            })
            ->orWhereHas('requests', function($query) use ($lastActive){
                $query->where('updated_at', '>', now()->subDays($lastActive));
            })
            ->orWhereHas('reactions', function($query) use ($lastActive){
                $query->where('updated_at', '>', now()->subDays($lastActive));
            });
        });
    }
}
