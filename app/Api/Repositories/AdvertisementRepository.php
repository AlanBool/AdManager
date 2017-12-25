<?php
/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2017/12/24
 * Time: 下午9:59
 */

namespace App\Api\Repositories;


use App\Api\Models\Advertisement;

/**
 * Class AdvertisementRepository
 * @package App\Admin\Repositories
 */
class AdvertisementRepository
{

    public function getAllData()
    {
        return Advertisement::all();
    }

}