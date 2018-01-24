<?php

/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2018/1/25
 * Time: 上午12:43
 */
namespace App\Http\UpStream;

use App\Http\Log\SourceLog;
use App\Http\Tools\HttpClient;

class TalkingData
{
    public $advertisement_uuid = "";
    public $channel_uuid = "";
    public $sys_click_id = "";
    public $idfa = "";
    public $ip = "";
    public $useragent = "";
    public $clicktime = "";
    public $click_track_url = "";

    public function __construct($params)
    {
        $this->advertisement_uuid = isset($params['advertisement_uuid']) ? $params['advertisement_uuid'] : "";
        $this->channel_uuid = isset($params['channel_uuid']) ? $params['channel_uuid'] : "";
        $this->sys_click_id = isset($params['sys_click_id']) ? $params['sys_click_id'] : "";
        $this->idfa = isset($params['idfa']) ? $params['idfa'] : "";
        $this->ip = isset($params['ip']) ? $params['ip'] : "";
        $this->useragent = isset($params['ua']) ? $params['ua'] : "";
        $this->clicktime = isset($params['clicktime']) ? $params['clicktime'] : "";
        $this->click_track_url = isset($params['click_track_url']) ? $params['click_track_url'] : "";
    }

    //点击汇报
    public function clickCallBack()
    {
        if(!empty($this->click_track_url)){
            $cbParams = [
                'uuid1' => $this->advertisement_uuid,
                'uuid2' => $this->channel_uuid,
                'uuid3' => $this->sys_click_id,
            ];
            $callBackUrl = env('CALLBACK_URL').'?'.http_build_query($cbParams);
            $rep_key = array('/{idfa}/','/{ip}/','/{useragent}/','/{clicktime}/','/{callback_url}/');
            $rep_value = array($this->idfa, $this->ip, urlencode($this->useragent), $this->clicktime, urlencode($callBackUrl));
            $track_url = preg_replace($rep_key, $rep_value, $this->click_track_url);
            $res = HttpClient::sentHttpRequest('GET', $track_url);
            $logData = [
                'url' => $track_url,
                'data' => [],
                'retheadercode' => $res->getStatusCode(),
                'body' => $res->getBody()->getContents(),
            ];
            SourceLog::writeSourceLog('click',$logData);
        }
    }


}