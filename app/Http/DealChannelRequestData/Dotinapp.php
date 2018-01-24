<?php
/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2018/1/25
 * Time: 上午1:42
 */

namespace App\Http\DealChannelRequestData;


class Dotinapp
{
    public $data = [];

    public function __construct($data)
    {
        $this->data['idfa'] = isset($data['idfa']) ? $data['idfa'] : '';
        $this->data['ip'] = isset($data['ip']) ? $data['ip'] : '';
        $this->data['useragent'] = isset($data['ua']) ? $data['ua'] : '';
        $this->data['clicktime'] = isset($data['timestamp']) ? $data['timestamp'] : '';
        $this->data['clickid'] = isset($data['clickid']) ? $data['clickid'] : '';
    }

}