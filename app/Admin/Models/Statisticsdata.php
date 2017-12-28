<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Statisticsdata extends Model
{
    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class,'advertisement_uuid','uuid');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class,'channel_uuid','token');
    }

}
