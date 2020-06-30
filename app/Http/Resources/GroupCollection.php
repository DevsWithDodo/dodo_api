<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\Group;

class GroupCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $groups = [];
        foreach ($this->collection as $group) {
            $groups[] = [
                'id' => $group->id,
                'name' => $group->name
            ];
        }
        return $groups;
    }
}
