<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Member;
use App\Http\Resources\Guest;

class Group extends JsonResource
{
    public function toArray($request)
    {
        $this->load('members');
        $group = [
            'group_id' => $this->id,
            'group_name' => $this->name,
            'currency' => $this->currency,
            'anyone_can_invite' => $this->anyone_can_invite,
            'invitation' => $this->invitation,
            'boosted' => $this->boosted,
            'members' => Member::collection($this->members)
        ];
        if (auth('api')->user()->can('edit', \App\Group::find($this->id))) {
            $group['guests'] = Guest::collection($this->guests);
        }
        return $group;
    }
}
