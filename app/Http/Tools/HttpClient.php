<?php
/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2018/1/25
 * Time: 上午12:55
 */

namespace App\Http\Tools;


use GuzzleHttp\Client;

class HttpClient
{
    public static function sentHttpRequest($method, $url, $options = []){
        $client = new Client([
            'http_errors' => false,
            'timeout' => 2,
        ]);
        $res = $client->request($method, $url, $options);
        return $res;
    }

}