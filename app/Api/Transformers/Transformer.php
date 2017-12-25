<?php
/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2017/12/26
 * Time: 上午12:53
 */

namespace App\Api\Transformers;


abstract class Transformer
{
    public function transformCollection($items)
    {
        return array_map([$this,'transform'], $items);
    }

    public abstract function transform($item);
}