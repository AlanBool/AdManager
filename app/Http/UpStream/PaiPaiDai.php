<?php
/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2018/1/25
 * Time: 上午1:03
 */

namespace App\Http\UpStream;


use App\Http\Tools\HttpClient;

class PaiPaiDai
{
    public $advertisement_uuid = "";
    public $channel_uuid = "";
    public $sys_click_id = "";
    public $idfa = "";
    public $mac = "";
    public $deviceId = "";
    public $click_track_url = "";
    public $appid = "9488b36b0b634e3d8439393d6fb0804a";
    public $source = 381;
    public $url = 'http://gw.open.ppdai.com/marketing/AdvertiseService/SaveAdvertise';
    public $appPrivateKey = <<<EOF
-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQChIW1pTlWA93N8AVwV9VlGOAnck5yPYV3wQ2rwKwYWGAeOLaL+
xnkEITS66/cj+AEvOODXpoFvUgeZjWGMbMhd2G5LDpN2NR2LxX6PqSnXwGr4pdES
GECZfPeuD/co7UNPJ93u9FiGgXLcugHlyKwKHtZ7lPHr5pTuY6CAPtaGtQIDAQAB
AoGAYGhLunIwHpXv9wYpPsopvCXTYLLBPdiYCCWOWlyjq5x9CMiteZru1VW44w5E
NLUyoDp77Qum7iA6g9lfwFFmICrVGkEJ+gc7JZm0VtUtmu+ZYELYK0qiWsWkR6lO
Hqzuk/asvA/RYdW7CHoSZT1RC1D0IxsJGTBLDPttmhzuyRECQQDqRzn9MyTOEOoF
k+KKiERRnbCLT91HN5FSj6d/7MbGniGovUNbKrqoT/mdG0V+S6htFhdFAq23ubfr
zLJ7U3yHAkEAsBH8Nu1k9FIkoTg04PjZhCKmlY2fcx3wvbK0WBDOxGT7iA3gSzET
bwcPlB7oQ4uzjNRKaX/Poadks1n4QvHN4wJBAKHzSur1h+PLXXstl9UcDd49m+Ux
+E1a5GKmW6vbCi4S8kGrU/yZtR7U6kDosUl1E6EVPFDAYUY4ZCPlBRyrwdECQAHB
HB87/E6G5wCIO9amBBzR75D76UPPX4+0USGzgSvpyavQX5TAN25axqf2KuBJaw+T
Ke6lLF9y+Ijk85lPKXsCQBdlo8w9dwsOZSY4Hqj2YMVWzsh/ECM5VZV0N45NS9ol
fUgUyBzgrqRKDl++z5CR+x8wop9l6OLLxSXtwRjIrKg=
-----END RSA PRIVATE KEY-----
EOF;

    public function __construct($params)
    {
        $this->advertisement_uuid = isset($params['advertisement_uuid']) ? $params['advertisement_uuid'] : "";
        $this->channel_uuid = isset($params['channel_uuid']) ? $params['channel_uuid'] : "";
        $this->sys_click_id = isset($params['sys_click_id']) ? $params['sys_click_id'] : "";
        $this->idfa = isset($params['idfa']) ? $params['idfa'] : "";
        $this->mac = isset($params['mac']) ? $params['mac'] : "";
        $this->deviceId = isset($params['deviceId']) ? $params['deviceId'] : "";
        $this->click_track_url = isset($params['click_track_url']) ? $params['click_track_url'] : "";
    }

    //点击汇报
    public function clickCallBack(){
        $cbParams = [
            'uuid1' => $this->advertisement_uuid,
            'uuid2' => $this->channel_uuid,
            'uuid3' => $this->sys_click_id,
        ];
        $timestamp = gmdate ( "Y-m-d H:i:s", time ()); // UTC format
        $cbUrl = env('CALLBACK_URL').'?'.http_build_query($cbParams);
        $data = [
            'AppId' => $this->appid,
            'CallBackUrl' => urlencode($cbUrl),
            'DeviceId' => $this->deviceId,
            'Idfa' => $this->idfa,
            'Mac' => $this->mac,
            'Source' => $this->source,
        ];
        $jsonData = json_encode($data);
        $requestSignStr = $this->sortToSignPaiPaiDai($jsonData);
        $timestampSign = $this->signPaiPaiDai($this->appid.$timestamp);
        $sign = $this->signPaiPaiDai($requestSignStr);
        $headers = [
            'Content-Type' => 'application/json;charset=UTF-8',
            'X-PPD-APPID' => $this->appid,
            'X-PPD-TIMESTAMP' => $timestamp,
            'X-PPD-TIMESTAMP-SIGN' => $timestampSign,
            'X-PPD-SIGN' => $sign,
            'X-PPD-SIGNVERSION' => 1,
            'X-PPD-SERVICEVERSION' => 1,
        ];
        $options = ['body' => $jsonData,'headers' => $headers];
        $res = HttpClient::sentHttpRequest('POST', $this->url, $options);
        dd($headers,$res->getBody()->getContents());
    }

    public function sortToSignPaiPaiDai($request)
    {
        $obj = json_decode($request,true);
        $arr = array();
        foreach ($obj as $key=>$value){
            if(is_array($value)){
                continue;
            }else{
                $arr[$key] = $value;
            }
        }
        ksort($arr);
        $str = "";
        foreach ($arr as $key => $value){
            $str = $str.$key.$value;
        }
        $str = strtolower($str);
        return $str;
    }

    function signPaiPaiDai($str){
        if(openssl_sign($str, $sign, $this->appPrivateKey))
            $sign = base64_encode($sign);
        return $sign;
    }

}