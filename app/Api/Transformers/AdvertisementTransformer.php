<?php
/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2017/12/26
 * Time: 上午12:56
 */

namespace App\Api\Transformers;


class AdvertisementTransformer extends Transformer
{
    public $channel;

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    public function transform($item)
    {
        return [
            'offer_id' => $item['uuid'],
            'title' => $item['name'],
            'loading_page' => $this->transformLoadingPage($item),
            'payout' => $item['payout'] * $this->channel['discount'],
            'payout_type' => $item['payout_type'],
//            'click_report' => array($item['click_track_url']),
        ];
    }

    private function transformLoadingPage($item)
    {
        $params = [
            'idfa' => '{idfa}',
            'gaid' => '{gaid}',
            'deviceid' => '{deviceid}',
            'mac' => '{mac}',
            'payout' => '{payout}',
            'p' => '{p}',
        ];

        $queryString= "";

        foreach ($params as $k => $v){
            $queryString = $queryString . $k . "=" . $v . "&";
        }
        if(!empty($queryString)){
            $queryString = substr($queryString,0, -1);
        }
        // click/offer_id/channel_id/to?
        return env('API_URL').'click/'.$item['uuid'].'/'.$this->channel['token'].'/to?'.$queryString;
    }
}