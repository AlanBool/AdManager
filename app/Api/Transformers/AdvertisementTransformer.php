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
    public function transform($item)
    {
        return [
            'title' => $item['name'],
        ];
    }
}