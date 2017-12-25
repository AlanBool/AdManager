<?php

namespace App\Api\Controllers;

use App\Api\Repositories\AdvertisementRepository;
use App\Api\Transformers\AdvertisementTransformer;
use App\Http\Controllers\Controller;

/**
 * Class AdvertisementController
 * @package App\Api\Controllers
 */
class AdvertisementController extends ApiController
{
    protected $advertisementRepository;
    protected $advertisementTransformer;
    /**
     * AdvertisementController constructor.
     * @param $advertisementRepository
     * @param $advertisementTransformer
     */
    public function __construct(AdvertisementRepository $advertisementRepository, AdvertisementTransformer $advertisementTransformer)
    {
        $this->advertisementRepository = $advertisementRepository;
        $this->advertisementTransformer = $advertisementTransformer;
    }

    public function list()
    {
        //返回404
//        return $this->responseNotFound('ad not found');
        $ads = $this->advertisementRepository->getAllData();
        return $this->setCode(200)->response([
            'message' => 'success',
            'code'  => $this->getCode(),
            'data' => $this->advertisementTransformer->transformCollection($ads->toArray()),
        ]);
    }
}
