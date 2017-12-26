<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/25
 * Time: 15:26
 */

namespace App\Http\Repositories;


use App\Http\Models\Channel;

class ChannelRepository
{

    public function byUuid($uuid)
    {
        return Channel::where('token',$uuid)->first();
    }

}