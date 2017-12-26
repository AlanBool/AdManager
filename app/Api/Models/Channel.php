<?php

namespace App\Api\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    //
    public $hidden = ['parent_id','add_user_id','created_at','updated_at'];

    public function advertisements()
    {
        return $this->belongsToMany(Advertisement::class)->withTimestamps();
    }

    public function isDelete()
    {
        return $this->is_delete === 'T';
    }
}
