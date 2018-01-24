<?php
/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2018/1/25
 * Time: 上午12:18
 */

namespace App\Http\DealChannelRequestData;


class Hotmobi
{
    public $data = [];

    public function __construct($data)
    {
        $this->data['idfa'] = isset($data['idfa']) ? $data['idfa'] : '';
        $this->data['ip'] = isset($data['ip']) ? $data['ip'] : '';
        $this->data['useragent'] = isset($data['useragent']) ? $data['useragent'] : '';
        $this->data['clicktime'] = isset($data['clicktime']) ? $data['clicktime'] : '';
        $this->data['wxidentify'] = isset($data['wxidentify']) ? $data['wxidentify'] : '';
        $this->data['clickid'] = isset($data['clickid']) ? $data['clickid'] : '';
    }

}