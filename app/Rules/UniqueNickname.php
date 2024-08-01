<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Group;

class UniqueNickname implements Rule
{
    /**
     * Create a new rule instance.
     * @param int $group_id
     * @param int|null $except_member_id
     * @return void
     */
    public function __construct($group_id, $except_member_id)
    {
        $this->group = Group::find($group_id);
        $this->except_member_id = $except_member_id;
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
        return $this->group->members
                ->where('member_data.nickname', $value)
                ->except([$this->except_member_id])
                ->count() == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('errors.taken_nickname');
    }
}
