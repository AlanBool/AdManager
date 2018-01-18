<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AdvertisementRepository;
use App\Http\Repositories\ChannelRepository;
use App\Http\Repositories\StatisticdataRepository;
use App\Http\Repositories\StreamdataRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;

class ClickController extends BaseController
{
    public $advertisement;
    public $channel;
    public $statisticdata;
    public $streamdata;
    public $client;

    /**
     * ClickController constructor.
     * @param $advertisement
     * @param $channel
     * @param $statisticdata
     * @param $streamdata
     */
    public function __construct(AdvertisementRepository $advertisement, ChannelRepository $channel, StatisticdataRepository $statisticdata, StreamdataRepository $streamdata)
    {
        $this->advertisement = $advertisement;
        $this->channel = $channel;
        $this->statisticdata = $statisticdata;
        $this->streamdata = $streamdata;
        $this->client = new Client([
            'http_errors' => false,
            'timeout' => 2,
        ]);
    }


    public function to(Request $request)
    {
        $advertisement_uuid = $request->route()->parameter('ad_uuid'); // {user}
        $channel_uuid = $request->route()->parameter('ch_uuid'); // {role}
        $idfa = $request->get('idfa');
        $ip = $request->get('ip');
        $useragent = $request->get('useragent');
        $clicktime = $request->get('clicktime');
        $wxidentify = $request->get('wxidentify');
        $clickid = $request->get('clickid');
        $url = $request->fullUrl();
        $sys_click_id = Uuid::generate()->string;
        $this->writeLog('source_click_url',['url' => $url,'ip'=>$ip]);
        $params = [
            'advertisement_uuid' => $advertisement_uuid,
            'channel_uuid' => $channel_uuid,
            'idfa' => $idfa,
            'ip' => $ip,
            'useragent' => $useragent,
            'click_id' => $clickid,
            'clicktime' => $clicktime,
            'sys_click_id' => $sys_click_id,
            'wxidentify' => $wxidentify,
        ];

        $ad = $this->advertisement->byUuid($advertisement_uuid);
        //广告不存在
        if(empty($ad)){
            return $this->responseNotFound('Ad not found');
        }
        //广告已删除
        if($ad->isDelete())
        {
            return $this->responseError('Ad is Invalid');
        }
        $cl = $this->channel->byUuid($channel_uuid);
        if(empty($cl)){
            return $this->responseNotFound('cl not found');
        }
        if($cl->isDelete())
        {
            return $this->responseChannelNoAuth('cl is Invalid');
        }

        $data = [
            'advertisement_uuid' => $advertisement_uuid,
            'channel_uuid' => $channel_uuid,
            'type' => 'click',
            'idfa' => $idfa,
            'ip' => $ip,
            'ua' => $useragent,
            'click_id' => $clickid,
            'clicktime' => $clicktime,
            'url' => $url,
            'sys_click_id' => $sys_click_id,
        ];
        $this->streamdata->store($data);
        $statistics_data = $this->statisticdata->byAdUuidAndClUuid($advertisement_uuid, $channel_uuid);
        if(empty($statistics_data)){
            $data = [
                'advertisement_uuid' => $advertisement_uuid,
                'channel_uuid' => $channel_uuid,
                'click_count' => 1,
            ];
            $this->statisticdata->store($data);
        }else{
            $statistics_data->increment('click_count');
        }
        //处理汇报给上游
        $this->transformClickCallBack($ad, $params);
        return $this->response('success');
    }

    public function transformClickCallBack($ad, $params)
    {
        switch ($ad->track_type){
            case 'paipaidai':
                $this->paiPaiDaiClickCallBack($params);
                break;
            case 'talking_data':
                $this->talkingDataClickCallBack($params,$ad);
                break;
            default:
                break;
        }
    }

    /**
     * 点击上报给PAIPAI贷
     * @param $params
     */
    public function paiPaiDaiClickCallBack($params)
    {
        $url = 'http://gw.open.ppdai.com/marketing/AdvertiseService/SaveAdvertise';
        $appid = "9488b36b0b634e3d8439393d6fb0804a";
        $cbParams = [
            'clickId' => $params['click_id'],
            'uuid1' => $params['advertisement_uuid'],
            'uuid2' => $params['channel_uuid'],
        ];
        $timestamp = gmdate ( "Y-m-d H:i:s", time ()); // UTC format
        $cbUrl = env('CALLBACK_URL').'?'.http_build_query($cbParams);
        $data = [
            'AppId' => '9488b36b0b634e3d8439393d6fb0804a',
            'CallBackUrl' => urlencode($cbUrl),
            'DeviceId' => isset($params['deviceid'])?$params['deviceid']:"22",
            'Idfa' => isset($params['idfa'])?$params['idfa']:"",
            'Mac' => isset($params['mac'])?$params['mac']:"",
            'Source' => 381,
        ];
        $jsonData = json_encode($data);
        $requestSignStr = $this->sortToSignPaiPaiDai($jsonData);
        $timestampSign = $this->signPaiPaiDai($appid.$timestamp);
        $sign = $this->signPaiPaiDai($requestSignStr);
        $headers = [
            'Content-Type' => 'application/json;charset=UTF-8',
            'X-PPD-APPID' => $appid,
            'X-PPD-TIMESTAMP' => $timestamp,
            'X-PPD-TIMESTAMP-SIGN' => $timestampSign,
            'X-PPD-SIGN' => $sign,
            'X-PPD-SIGNVERSION' => 1,
            'X-PPD-SERVICEVERSION' => 1,
        ];
        $res = $this->client->request('POST', $url, ['body' => $jsonData,'headers' => $headers]);
//        echo $timestamp."\n";
//        echo $timestampSign ."\n";
//        echo $sign ."\n";
//        echo $requestSignStr."\n";
//        echo $jsonData."\n";
        dd($headers,$res->getBody()->getContents());
    }

    public function talkingDataClickCallBack($params, $ad)
    {
        $cbParams = [
            'uuid1' => $params['advertisement_uuid'],
            'uuid2' => $params['channel_uuid'],
            'uuid3' => $params['sys_click_id'],
        ];
        $callBackUrl = env('CALLBACK_URL').'?'.http_build_query($cbParams);
        $rep_key = array('/{idfa}/','/{ip}/','/{useragent}/','/{clicktime}/','/{callback_url}/');
        $rep_value = array($params['idfa'],$params['ip'],urlencode($params['useragent']),$params['clicktime'],urlencode($callBackUrl));
        $track_url = preg_replace($rep_key, $rep_value, $ad->click_track_url);
//        $url = "https://lnk0.com/RB14Mh?idfa=". $params['idfa'] ."&ip=". $params['ip'] ."&useragent=". $params['useragent'] ."&clicktime=". $params['clicktime'] ."&callback_url=". $callBackUrl;
        $res = $this->client->request('GET', $track_url);
        $logData = [
            'url' => $track_url,
            'data' => [],
            'retheadercode' => $res->getStatusCode(),
            'body' => $res->getBody()->getContents(),
        ];
        $this->writeLog('click',$logData);
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
        $appPrivateKey =<<<EOF
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
        if(openssl_sign($str,$sign,$appPrivateKey))
            $sign = base64_encode($sign);
        return $sign;
    }

}
