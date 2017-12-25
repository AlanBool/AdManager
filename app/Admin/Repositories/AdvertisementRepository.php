<?php
/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2017/12/24
 * Time: 下午9:59
 */

namespace App\Admin\Repositories;


use App\Admin\Models\Advertisement;

/**
 * Class AdvertisementRepository
 * @package App\Admin\Repositories
 */
class AdvertisementRepository
{

    /**
     * 返回 Advertisement::class
     * @return string
     */
    public function getSelfModelClassName()
    {
        return Advertisement::class;
    }


    public function create(array $data)
    {
        return Advertisement::create($data);
    }

    public function byId($id)
    {
        return Advertisement::find($id);
    }

    public function batchDelete(array $ids,array $data)
    {
        return Advertisement::whereIn('id', $ids)->update($data);
    }



}