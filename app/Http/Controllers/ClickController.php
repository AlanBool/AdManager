<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClickController extends Controller
{
    public function to(Request $request)
    {
        $advertisement_uuid = $request->route()->parameter('ad_uuid'); // {user}
        $channel_uuid = $request->route()->parameter('ch_uuid'); // {role}
        $idfa = $request->get('idfa');
        $gaid = $request->get('gaid');
        $payout = $request->get('payout');
        $p = $request->get('p');
        $ip = $request->getClientIp();
        $ua = $request->headers->get('User-Agent');


        dd($advertisement_uuid,$channel_uuid,$idfa,$gaid,$payout,$p,$ip,$ua);
    }
}
