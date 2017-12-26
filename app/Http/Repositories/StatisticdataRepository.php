<?php
/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2017/12/26
 * Time: 下午11:05
 */

namespace App\Http\Repositories;


use App\Http\Models\Statisticsdata;

class StatisticdataRepository
{
    public function byAdUuidAndClUuid($advertisement_uuid, $channel_uuid)
    {
        return Statisticsdata::where(['advertisement_uuid' => $advertisement_uuid, 'channel_uuid' => $channel_uuid])->first();
    }

    public function store($data)
    {
        return Statisticsdata::create($data);
    }

}