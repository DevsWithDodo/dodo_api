<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Group extends JsonResource
{
    public function toArray($request)
    {
        $group = [
            'id' => $this->id,
            'name' => $this->name,
        ];
        foreach ($this->members as $member) {
            $group['members'][] = [
                'id' => $member->id,
                'name' => $member->name,
                'balance' => $member->member_data->balance,
                'nickname' => $member->member_data->nickname,
                'is_admin' => $member->member_data->is_admin
            ];
        }

        return $group;
    }
}
