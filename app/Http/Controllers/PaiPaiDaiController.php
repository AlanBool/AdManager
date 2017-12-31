<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaiPaiDaiController extends Controller
{
    public function index(Request $request)
    {
        //处理回传逻辑
        $clickId = $request->get('clickId');
        $uuid1 = $request->get('uuid1');//广告ID
        $uuid2 = $request->get('uuid2');//渠道id
        
//        dd($clickId, $uuid1, $uuid2);

        echo 'success';
    }
}
