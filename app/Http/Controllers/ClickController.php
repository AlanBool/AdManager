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
        $obj = json_decode($request);
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
        $appPrivateKey ="
-----BEGIN RSA PRIVATE KEY-----
MIICXgIBAAKBgQCmL1sJ4/hZmqou8nFqjtK175SJFeBPPly8a5ThjgWsGAVZmyJW
3PwM7KmwyeDy1BD8f2cHGsewMipEbBKegpSkqQg+ZaCsQJLKW64jRJXVFCIJBhhu
cbK8gX8VHPK4B84EfEhuuZ/Gcb0pU2XCZx3igQmlM/I4aBihKo5btelMwwIDAQAB
AoGAMY+j7fIwCcEHihLB4k6P5rR5rtx4Vgm6LHNFJnNtm6JaThvnBNLI1K3r+Y5r
aN/35OW1+zdwYErFsjws3VsCKxFVQXOUdu7vSta7swFl9LXyO5TIr0eReX3EVuaB
Rz7GS0hXm9sDiLZjJWHf7JEn+voZyax+2hAtLfMKbS5qeRECQQDou5ZYbFlJF8Zs
aoZn4wpxJZfAtkqAEqzXNS2tu7Q237ebFEuBEMmWVdBl8pG9koSxs0w6p+8TWEFd
2jQMJhspAkEAtsyWVQjpITvi1fBMx5/Pp4uBOWHTQcnAU9t6tcwtjwciIfYkavRX
7aaqgGELKWZS2IkAGayyhUNg6PozNFtSCwJBALNBeSWWHpcr1ss+qVNvDmXj3KS0
Q2GuAK6p6Qr9nmr9mX+6/ATnFz3RzvgXA6YOKmJshXRQUNaHjaFqJdiNqTECQQC1
T0kQwMzTDN4ZysWs/oLtsL4Ul0X9q8mao0gcB49snOuq+cP3XbHU4wmcWiTDBF3J
rmEuFg/fhAwcKQYeuTEvAkEA4wCxtbUvAgETre9g9vWOkhZo+oqU3AZJF2/gQgis
2ZO7L3tjvXTBfyi5unQ+uw15vti1+Oz6sfPD3n6hsRtovw==
-----END RSA PRIVATE KEY-----
";
        if(openssl_sign($str,$sign,$appPrivateKey))
            $sign = base64_encode($sign);
        return $sign;
    }

}
