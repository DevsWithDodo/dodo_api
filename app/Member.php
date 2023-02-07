<?php
 
namespace App;
 
use Illuminate\Database\Eloquent\Relations\Pivot;
 
class Member extends Pivot
{
    protected $table = 'group_user';

    protected $fillable = ['user_id', 'group_id', 'nickname', 'is_admin', 'balance', 'approved'];

    public function getNicknameAttribute($value)
    {
        return $value == null ? null : decrypt($value);
    }

    public function getBalanceAttribute($value)
    {
        return $value == null ? null : decrypt($value);
    }

}