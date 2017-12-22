<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    //
    public $fillable = ['name','parent_id'];

    public function advertisements()
    {
        return $this->belongsToMany(Advertisement::class)->withTimestamps();
    }
}
