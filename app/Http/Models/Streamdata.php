<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Streamdata extends Model
{
    public $fillable = ['advertisement_uuid','channel_uuid','type','idfa','click_id','gaid','p','ip','ua','payout'];
}
