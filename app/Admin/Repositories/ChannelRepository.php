<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/25
 * Time: 15:26
 */

namespace App\Admin\Repositories;


use App\Admin\Models\Channel;

class ChannelRepository
{

    public function getAllDataPluckNameAndId()
    {
        return Channel::noDelete()->pluck('name', 'id');
    }

}