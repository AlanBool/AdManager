<?php
/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2017/12/26
 * Time: 下午11:05
 */

namespace App\Http\Repositories;


use App\Http\Models\Statisticsdata;
use Carbon\Carbon;

class StatisticdataRepository
{
    public function byAdUuidAndClUuid($advertisement_uuid, $channel_uuid)
    {
        return Statisticsdata::where([
            'advertisement_uuid' => $advertisement_uuid,
            'channel_uuid' => $channel_uuid,
        ])->whereDate('created_at', Carbon::now()->toDateString())->first();
    }

    public function store($data)
    {
        return Statisticsdata::create($data);
    }

}