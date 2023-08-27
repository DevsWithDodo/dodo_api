<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MemberResource;

class GroupResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'group_id' => $this->id,
            'group_name' => $this->name,
            'currency' => $this->currency,
            'admin_approval' => $this->admin_approval,
            'invitation' => $this->invitation,
            'boosted' => $this->boosted,
            'members' => MemberResource::collection($this->members),
            'custom_categories' => $this->custom_categories,
        ];
    }
}
