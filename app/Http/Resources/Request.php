<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Reaction;
use App\Group;

class Request extends JsonResource
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
            'request_id' => $this->id,
            'name' => $this->name,
            'requester_id' => $this->requester_id,
            //'requester_username' => $this->requester->username,
            'requester_nickname' => Group::nicknameOf($this->group_id, $this->requester_id),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'reactions' => Reaction::collection($this->reactions)
        ];
    }
}
