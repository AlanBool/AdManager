<?php

namespace App\Http\Controllers;

use App\Http\DealChannelRequestData\Hotmobi;
use App\Http\Log\SourceLog;
use App\Http\Repositories\AdvertisementRepository;
use App\Http\Repositories\ChannelRepository;
use App\Http\Repositories\StatisticdataRepository;
use App\Http\Repositories\StreamdataRepository;
use App\Http\UpStream\PaiPaiDai;
use App\Http\UpStream\TalkingData;
use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;

class ClickV1Controller extends BaseController
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
        $url = $request->fullUrl();
        $ip = $request->getClientIp();
        $ua = $request->userAgent();
        $data = $request->all();
        SourceLog::writeSourceLog('source_click_url',['data'=>$data, 'server_ip'=>$ip, 'url' => $url]);
        $advertisement_uuid = $request->route()->parameter('ad_uuid'); // {user}
        $channel_uuid = $request->route()->parameter('ch_uuid'); // {role}

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

        $channelObject = "";
        switch ($cl->type){
            case 'hotmobi':
                    $channelObject = new Hotmobi($data);
                break;
            default:
                break;
        }
        if(!empty($channelObject)){
            $sys_click_id = Uuid::generate()->string;
            $params = $store_data = [
                'advertisement_uuid' => $advertisement_uuid,
                'channel_uuid' => $channel_uuid,
                'type' => 'click',
                'idfa' => isset($channelObject->data['idfa']) ? $channelObject->data['idfa'] : '',
                'ip' => isset($channelObject->data['ip']) ? $channelObject->data['ip'] : $ip,
                'ua' => isset($channelObject->data['useragent']) ? $channelObject->data['useragent'] : $ua,
                'click_id' => isset($channelObject->data['clickid']) ? $channelObject->data['clickid'] : '',
                'clicktime' => isset($channelObject->data['clicktime']) ? $channelObject->data['clicktime'] : '',
                'url' => $url,
                'sys_click_id' => $sys_click_id,
            ];
            $this->streamdata->store($store_data);
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
        }else{
            return $this->responseNotFound('cl type not found');
        }
    }

    public function transformClickCallBack($ad, $params)
    {
        $params['click_track_url'] = $ad->click_track_url;
        $obj = "";
        switch ($ad->track_type){
            case 'paipaidai':
                $obj = new PaiPaiDai($params);
                break;
            case 'talking_data':
                $obj = new TalkingData($params);
                break;
            default:
                break;
        }
        if(!empty($obj)){
            $obj->clickCallBack();
        }
    }

}
