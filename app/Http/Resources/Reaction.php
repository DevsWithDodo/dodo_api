<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Reaction extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $group = $this->purchase?->group ?? $this->request?->group ?? $this->payment?->group;
        return [
            'id' => $this->id,
            'reaction' => $this->reaction,
            'user_nickname' => $group->members->find($this->user_id)->member_data->nickname
        ];
    }
}
