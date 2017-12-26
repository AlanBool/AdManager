<?php

namespace App\Api\Controllers;

use App\Api\Repositories\AdvertisementRepository;
use App\Api\Repositories\ChannelRepository;
use App\Api\Transformers\AdvertisementTransformer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Class AdvertisementController
 * @package App\Api\Controllers
 */
class AdvertisementController extends ApiController
{
    protected $advertisementRepository;
    protected $advertisementTransformer;
    protected $channelRepository;

    /**
     * AdvertisementController constructor.
     * @param $advertisementRepository
     * @param $advertisementTransformer
     * @param $channelRepository
     */
    public function __construct(AdvertisementRepository $advertisementRepository, AdvertisementTransformer $advertisementTransformer,ChannelRepository $channelRepository)
    {
        $this->advertisementRepository = $advertisementRepository;
        $this->advertisementTransformer = $advertisementTransformer;
        $this->channelRepository = $channelRepository;
    }

    public function adList(Request $request)
    {
        $token = trim($request->get('token'));

        if(empty($token)){
            return $this->responseNoToken();
        }

        //获取token所在的渠道id
        $channel = $this->channelRepository->byToken($token);
        if(empty($channel)){
            return $this->responseNoChannel();
        }

        //渠道是被删除的状态
        if($channel->isDelete()){
            return $this->responseChannelNoAuth();
        }

        $newChannel = $this->channelRepository->byIdWithAdvertisements($channel->id);

        $data = $this->advertisementTransformer->setChannel($channel->toArray())->transformCollection($newChannel->advertisements->toArray());
        if(count($data) > 0){
            return $this->setCode(200)->response([
                'message' => 'success',
                'code'  => $this->getCode(),
                'data' => $data,
            ]);
        }else{
            return $this->responseNoData();
        }
    }
}
