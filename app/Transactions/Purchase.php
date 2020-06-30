<?php

namespace App\Depts;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $table = 'purchases';

    protected $fillable = ['name'];

    public function group()
    {
        return $this->belongsTo('App\Group'); 
    }

    public function buyers()
    {
        return $this->hasMany('App\Transactions\Buyers');
    }

    public function receivers()
    {
        return $this->hasMany('App\Transactions\Receiver');
    }


}
