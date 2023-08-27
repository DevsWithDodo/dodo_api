<?php

namespace App;

use App\Enums\TrialStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'pin_verified_at',
        'pin_verification_count',
        'trial_status'
    ];

    protected $casts = [
        'pin_verified_at' => 'datetime',
        'trial_status' => TrialStatus::class
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
