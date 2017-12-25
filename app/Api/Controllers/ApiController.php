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
