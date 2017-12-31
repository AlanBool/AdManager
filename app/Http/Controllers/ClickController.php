<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AdvertisementRepository;
use App\Http\Repositories\ChannelRepository;
use App\Http\Repositories\StatisticdataRepository;
use App\Http\Repositories\StreamdataRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ClickController extends BaseController
{
    public $advertisement;
    public $channel;
    public $statisticdata;
    public $streamdata;

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
    }


    public function to(Request $request)
    {
        $advertisement_uuid = $request->route()->parameter('ad_uuid'); // {user}
        $channel_uuid = $request->route()->parameter('ch_uuid'); // {role}
        $idfa = $request->get('idfa');
        $gaid = $request->get('gaid');
        $payout = $request->get('payout');
        $p = $request->get('p');
        $ip = $request->getClientIp();
        $ua = $request->headers->get('User-Agent');
        $ad = $this->advertisement->byUuid($advertisement_uuid);
        $deviceid = $request->get('deviceid');
        $mac = $request->get('mac');
        $clickId = date('YmdHis').mt_rand(10000,99999);
        $params = [
            'advertisement_uuid' => $advertisement_uuid,
            'channel_uuid' => $channel_uuid,
            'type' => 'click',
            'idfa' => $idfa,
            'gaid' => $gaid,
            'p' => $p,
            'ip' => $ip,
            'ua' => $ua,
            'payout' => $payout,
            'click_id' => $clickId,
            'deviceid' => $deviceid,
            'mac' => $mac,
        ];
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
            'gaid' => $gaid,
            'p' => $p,
            'ip' => $ip,
            'ua' => $ua,
            'payout' => $payout,
            'click_id' => $clickId,
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
        //处理上游广告主的点击回传
        $url = $this->transformClick($ad, $cl, $clickId);
        $this->transformClickCallBack($ad, $cl, $params);
        if(!empty($url)){
            return redirect($url);
        }else{
            return $this->responseError('url is Invalid');
        }
    }

    public function transformClick($ad, $channel, $clickId)
    {
        $source = $ad->source;
        $url = '';
        switch ($source){
            default:
                $url = $ad->loading_page.'?clickId='.$clickId.'&subid='.$channel->name;
        }
        return $url;
    }

    public function transformClickCallBack($ad, $channel, $params)
    {
        $track_type = $ad->track_type;
        switch ($track_type){
            case 'paipaidai':
                    $this->paiPaiDaiClickCallBack($params);
                break;
            default:
                break;
        }
    }

    public function paiPaiDaiClickCallBack($params)
    {
        $url = 'http://gw.open.ppdai.com/marketing/AdvertiseService/SaveAdvertise';
        $data = [
            'AppId' => 'AppId',
            'CallBackUrl' => '',
            'DeviceId' => $params['deviceid'],
            'Idfa' => $params['idfa'],
            'Mac' => $params['mac'],
            'Source' => 1,
        ];
        $client = new Client([
            'http_errors' => false,
            'timeout' => 1,
        ]);
//        $res = $client->request('post', $url, ['json' => $data, 'http_errors' => false]);
        $res = $client->request('POST', $url, ['json' => $data]);
//        dd($res->getBody()->getContents());
    }
}
