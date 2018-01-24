<?php
/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2018/1/25
 * Time: 上午1:18
 */

namespace App\Http\DownStream;


use App\Http\Log\SourceLog;
use App\Http\Tools\HttpClient;

class HotmobiDownStream
{
    public static function conversionCallBack($params)
    {
        $rep_key = [
            '/{wxidentify}/',
            '/{clickid}/',
            '/{clicktime}/',
            '/{ip}/',
            '/{idfa}/'
        ];
        $wxidentify = isset($params['wxidentify']) ? $params['wxidentify'] : "";
        $clickid = isset($params['clickid']) ? $params['clickid'] : "";
        $clicktime = isset($params['clicktime']) ? $params['clicktime'] : "";
        $ip = isset($params['ip']) ? $params['ip'] : "";
        $idfa = isset($params['idfa']) ? $params['idfa'] : "";
        $rep_val = [
            $wxidentify,
            $clickid,
            $clicktime,
            $ip,
            $idfa,
        ];
        $pre_url = "http://cpa.adunite.com/api/activate.api?wxidentify={wxidentify}&clickid={clickid}&clicktime={clicktime}&ip={ip}&idfa={idfa}";
        $url = preg_replace($rep_key, $rep_val, $pre_url);
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