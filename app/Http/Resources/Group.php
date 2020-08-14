<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Member;
use App\Http\Resources\Invitation;

class Group extends JsonResource
{
    public function toArray($request)
    {
        setlocale(LC_COLLATE, 'hu_HU.ISO8859-2');
        $group = [
            'group_id' => $this->id,
            'group_name' => $this->name,
            'currency' => $this->currency,
            'anyone_can_invite' => $this->anyone_can_invite,
            'members' => Member::collection($this->members->sortBy('member_data.nickname', SORT_LOCALE_STRING)),
            'invitations' => Invitation::collection($this->invitations)
        ];

        return $group;
    }
}
