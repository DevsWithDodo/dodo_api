<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Member;
use App\Http\Resources\Invitation;

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
            'invitations' => Invitation::collection($this->invitations)
        ];

        return $group;
    }
}
