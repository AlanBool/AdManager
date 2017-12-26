<?php
/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2017/12/24
 * Time: 下午9:59
 */

namespace App\Http\Repositories;


use App\Http\Models\Advertisement;

/**
 * Class AdvertisementRepository
 * @package App\Http\Repositories
 */
class AdvertisementRepository
{

    public function byUuid($uuid)
    {
        return Advertisement::where('uuid',$uuid)->first();
    }


}