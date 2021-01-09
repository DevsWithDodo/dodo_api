<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Member;
use App\Http\Resources\User;

class Group extends JsonResource
{
    public function toArray($request)
    {
        $group = [
            'group_id' => $this->id,
            'group_name' => $this->name,
            'currency' => $this->currency,
            'anyone_can_invite' => $this->anyone_can_invite,
            'members' => Member::collection($this->members),
            'invitation' => $this->invitation
        ];
        if (auth('api')->user()->can('edit', \App\Group::find($this->id))) {
            $group['guests'] = User::collection($this->guests);
        }
        return $group;
    }
}
