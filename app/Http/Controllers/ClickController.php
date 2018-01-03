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
        $this->transformClickCallBack($ad->track_type, $params);
        return $this->response('success');
    }

    public function transformClickCallBack($track_type, $params)
    {
        switch ($track_type){
            case 'paipaidai':
                $this->paiPaiDaiClickCallBack($params);
                break;
            case 'talking_data':
                $this->talkingDataClickCallBack($params);
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
        $cbParams = [
            'clickId' => $params['click_id'],
            'uuid1' => $params['advertisement_uuid'],
            'uuid2' => $params['channel_uuid'],
        ];
        $data = [
            'AppId' => 'AppId',
            'CallBackUrl' => env('CALLBACK_URL').'?'.http_build_query($cbParams),
            'DeviceId' => $params['deviceid'],
            'Idfa' => $params['idfa'],
            'Mac' => $params['mac'],
            'Source' => 1,
        ];
        $res = $this->client->request('POST', $url, ['json' => $data]);
//        dd($res->getBody()->getContents());
    }

    public function talkingDataClickCallBack($params)
    {
        $cbParams = [
            'uuid1' => $params['advertisement_uuid'],
            'uuid2' => $params['channel_uuid'],
            'uuid3' => $params['sys_click_id'],
        ];
        $callBackUrl = env('CALLBACK_URL').'?'.http_build_query($cbParams);
        $url = "https://lnk0.com/RB14Mh?idfa=". $params['idfa'] ."&ip=". $params['ip'] ."&useragent=". $params['useragent'] ."&clicktime=". $params['clicktime'] ."&callback_url=". $callBackUrl;
        $this->client->request('GET', $url);
    }

}
