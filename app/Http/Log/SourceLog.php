<?php
/**
 * Created by PhpStorm.
 * User: yingjun
 * Date: 2018/1/25
 * Time: 上午12:34
 */

namespace App\Http\Log;


use Illuminate\Support\Facades\Storage;

class SourceLog
{
    public static function writeSourceLog($fileType, $data){
        $filename = $fileType.'_'.date('Ymd').'.log';
        $data = array_merge(['logtime' => date('Y-m-d H:i:s')], $data);
        $jsonData = json_encode($data,JSON_UNESCAPED_UNICODE);
        Storage::disk('local')->append($filename,$jsonData);
    }
}