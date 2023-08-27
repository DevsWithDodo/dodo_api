<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Group;

class ReactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'reaction' => $this->reaction,
            'user_id' => $this->user_id,
            'user_nickname' => Group::nicknameOf($this->group_id, $this->user_id)
        ];
    }
}
