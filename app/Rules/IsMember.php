<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Group;

class IsMember implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($group_id)
    {
        $this->group = Group::find($group_id);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->group->members->contains($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('errors.not_member');
    }
}
