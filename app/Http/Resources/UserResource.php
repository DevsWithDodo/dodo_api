<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'api_token' => $this->api_token,
            'last_active_group' => $this->last_active_group,
            'default_currency' => $this->default_currency,
            'ad_free' => $this->ad_free ? 1 : 0,
            'trial' => $this->trial ? 1 : 0,
            'gradients_enabled' => $this->gradients_enabled ? 1 : 0,
            'available_boosts' => $this->available_boosts ? 1 : 0,
            'personalised_ads' => $this->personalised_ads ? 1 : 0,
            'payment_details' => $this->payment_details,
            'status' => $this->status,
            'has_groups' => $this->groups()->count() > 0,
            'google_connected' => $this->google_connected ? 1 : 0,
            'apple_connected' => $this->apple_connected ? 1 : 0,
            'has_password' => $this->has_password ? 1 : 0,
        ];
    }
}
