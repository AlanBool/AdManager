<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AdvertisementRepository;
use App\Http\Repositories\ChannelRepository;
use App\Http\Repositories\StatisticdataRepository;
use App\Http\Repositories\StreamdataRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ActiveController extends BaseController
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

    public function index(Request $request)
    {
        //处理回传逻辑
        $advertisement_uuid = $request->get('uuid1');//广告ID
        $channel_uuid = $request->get('uuid2');//渠道id
        $sys_click_id = $request->get('uuid3');//系统生成的click_id
        $url = $request->fullUrl();

        $this->writeLog('source_conversion_url',['url' => $url,'ip'=>$request->get('ip')]);

        $conversion_count = $this->streamdata->getConversionCountBySysClickId($sys_click_id);
        if($conversion_count <= 0){
            $statistics_data = $this->statisticdata->byAdUuidAndClUuid($advertisement_uuid, $channel_uuid);
            if(empty($statistics_data)){
                $data = [
                    'advertisement_uuid' => $advertisement_uuid,
                    'channel_uuid' => $channel_uuid,
                    'conversion_count' => 1,
                ];
                $this->statisticdata->store($data);
            }else{
                $statistics_data->increment('conversion_count');
            }
            $data = [
                'advertisement_uuid' => $advertisement_uuid,
                'channel_uuid' => $channel_uuid,
                'type' => 'conversion',
                'url' => $url,
                'sys_click_id' => $sys_click_id,
            ];
            $this->streamdata->store($data);
        }

        //查找点击信息
        $click_Data = $this->streamdata->getClickDataBySysClickId($sys_click_id);
        if($click_Data){
            //有点击信息
            $cl = $this->channel->byUuid($channel_uuid);
            if($cl && !$cl->isDelete()){
                $url = $click_Data->url;
                $params = [];
                $url_data_list = parse_url($url);
                if(isset($url_data_list['query'])){
                    parse_str($url_data_list['query'],$params);
                }
                $this->transformConversionCallBack($cl->type, $params);
            }
        }
        return $this->response('success');
    }

    public function transformConversionCallBack($type, $params)
    {
        switch ($type){
            case 'hotmobi':
                $this->hotmobiConversionCallBack($params);
                break;
            default:
                break;
        }
    }

    public function hotmobiConversionCallBack($params)
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
        $res = $this->client->request('GET', $url);
        $logData = [
            'url' => $url,
            'data' => [],
            'retheadercode' => $res->getStatusCode(),
            'body' => $res->getBody(),
        ];
        $this->writeLog('conversion',$logData);
    }
}
