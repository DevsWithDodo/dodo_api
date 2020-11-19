<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\Group
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Transactions\Payment[] $payments
 * @property-read int|null $payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Transactions\Purchase[] $purchases
 * @property-read int|null $purchases_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Request[] $requests
 * @property-read int|null $requests_count
 * @method static \Illuminate\Database\Eloquent\Builder|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group query()
 * @mixin \Eloquent
 */
	class Group extends \Eloquent {}
}

namespace App{
/**
 * App\Request
 *
 * @property-read \App\Group $group
 * @property-read \App\User $requester
 * @method static \Illuminate\Database\Eloquent\Builder|Request newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Request newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Request query()
 * @mixin \Eloquent
 */
	class Request extends \Eloquent {}
}

namespace App\Transactions{
/**
 * App\Transactions\Payment
 *
 * @property-read \App\Group $group
 * @property-read \App\User $payer
 * @property-read \App\User $taker
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment query()
 * @mixin \Eloquent
 */
	class Payment extends \Eloquent {}
}

namespace App\Transactions{
/**
 * App\Transactions\Purchase
 *
 * @property-read \App\User $buyer
 * @property-read \App\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Transactions\PurchaseReceiver[] $receivers
 * @property-read int|null $receivers_count
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Purchase query()
 * @mixin \Eloquent
 */
	class Purchase extends \Eloquent {}
}

namespace App\Transactions{
/**
 * App\Transactions\PurchaseReceiver
 *
 * @property-read \App\Transactions\Purchase $purchase
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReceiver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReceiver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReceiver query()
 * @mixin \Eloquent
 */
	class PurchaseReceiver extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $verified
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Group[] $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereVerified($value)
 * @mixin \Eloquent
 */
	class User extends \Eloquent {}
}

