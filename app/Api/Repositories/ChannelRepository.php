<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/25
 * Time: 15:26
 */

namespace App\Api\Repositories;


use App\Api\Models\Channel;

class ChannelRepository
{

    public function byToken($token)
    {
        return Channel::where('token', $token)->first();
    }

    public function byIdWithAdvertisements($id)
    {
        return Channel::where('id',$id)->with(['advertisements' => function($query){
            $query->where('is_delete','F');
        }])->first();
    }

}