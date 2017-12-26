<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Statisticsdata extends Model
{
    public $fillable = ['advertisement_uuid','channel_uuid','click_count','conversion_count','total_cost'];
}
