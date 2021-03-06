<?php

namespace App\Api\Controllers;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    protected $code = 200;

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code)
    {
        $this->code = $code;
        return $this;
    }//返回状态码

    public function responseNoToken($message = "Token is required")
    {
        return $this->setCode(401)->responseError($message);
    }

    public function responseNoChannel($message = 'token is error')
    {
        return $this->setCode(402)->responseError($message);
    }

    public function responseChannelNoAuth($message = 'token is forbidden')
    {
        return $this->setCode(403)->responseError($message);
    }

    public function responseNoData($message = 'no data')
    {
        return $this->setCode(501)->responseError($message);
    }

    public function responseNotFound($message = "Not Found")
    {
        return $this->setCode(404)->responseError($message);
    }

    public function responseError($message)
    {
        return $this->response([
            'status' => 'failed',
            'code'  => $this->getCode(),
            'message' => $message,
        ]);
    }

    public function response($data)
    {
        return \Response::json($data,$this->getCode());
    }
}
