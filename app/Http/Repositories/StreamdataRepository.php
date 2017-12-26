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

}