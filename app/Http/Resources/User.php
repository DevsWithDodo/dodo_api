<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class User extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'api_token' => $this->api_token,
            'last_active_group' => $this->last_active_group,
            'balance' => DB::table('group_user')->where('user_id', $this->id)->sum('balance')
        ];
    }
}
