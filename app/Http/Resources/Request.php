<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'requester_username' => $this->group->members->find($this->requester_id)->username,
            'requester_nickname' => $this->group->members->find($this->requester_id)->member_data->nickname,
            'fulfiller_id' => $this->fulfiller_id,
            'fulfiller_nickname' => $this->fulfilled ? ($this->group->members->find($this->fulfiller_id)->member_data->nickname) : null,
            'fulfilled' => $this->fulfilled,
            'fulfilled_at' => $this->fulfilled_at,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
