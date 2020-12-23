<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Member;
use App\Http\Resources\User;
use Illuminate\Support\Facades\Gate;

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
            'invitation' => $this->invitation
        ];
        if (auth('api')->user()->can('edit', \App\Group::find($this->id))) {
            $group['guests'] = User::collection($this->guests);
        }
        return $group;
    }
}
