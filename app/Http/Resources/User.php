<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'api_token' => $this->api_token,
            'last_active_group' => $this->last_active_group,
            'default_currency' => $this->default_currency,
            'total_balance' => round(floatval($this->totalBalance()), 2),
            'ad_free' => $this->ad_free,
            'gradients_enabled' => $this->gradients_enabled,
            'available_boosts' => $this->available_boosts,
        ];
    }
}
