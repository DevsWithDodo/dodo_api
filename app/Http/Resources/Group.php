<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Member;

class Group extends JsonResource
{
    public function toArray($request)
    {
        $group = [
            'group_id' => $this->id,
            'group_name' => $this->name,
            'members' => Member::collection($this->members)
        ];

        return $group;
    }
}
