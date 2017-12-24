<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    //
    public $fillable = ['name','track_type','loading_page','click_track_url','uuid','add_user_id','update_user_id'];

    public function channels()
    {
        return $this->belongsToMany(Channel::class)->withTimestamps();
    }
}
