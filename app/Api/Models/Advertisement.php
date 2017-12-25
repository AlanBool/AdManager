<?php

namespace App\Api\Models;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    //
    public $hidden = ['add_user_id','update_user_id','created_at','updated_at','is_delete'];

    public function channels()
    {
        return $this->belongsToMany(Channel::class)->withTimestamps();
    }
}
