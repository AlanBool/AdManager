<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/25
 * Time: 17:40
 */

namespace App\Http\DownStream;


use App\Http\Log\SourceLog;
use App\Http\Tools\HttpClient;

class DotinappDownStream
{
    public static function conversionCallBack($params){
        $pre_url = "http://svr.dotinapp.com/iis?clkid=";
        $clickid = isset($params['clickid']) ? $params['clickid'] : '';
        $url = $pre_url.$clickid;
        $res = HttpClient::sentHttpRequest('GET', $url);
        $logData = [
            'url' => $url,
            'data' => [],
            'retheadercode' => $res->getStatusCode(),
            'body' => $res->getBody()->getContents(),
        ];
        SourceLog::writeSourceLog('conversion',$logData);
    }
}