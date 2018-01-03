<?php
/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2017/12/26
 * Time: 下午11:06
 */

namespace App\Http\Repositories;


use App\Http\Models\Streamdata;

class StreamdataRepository
{

    public function store($data)
    {
        return Streamdata::create($data);
    }

    public function getConversionCountBySysClickId($sys_click_id)
    {
        return Streamdata::where([
            'type' => 'conversion',
            'sys_click_id' => $sys_click_id
        ])->count();
    }
    
    /**
     * 获取点击信息
     */
    public function getClickDataBySysClickId($sys_click_id)
    {
        return Streamdata::where([
            'type' => 'click',
            'sys_click_id' => $sys_click_id
        ])->first();
    }

}