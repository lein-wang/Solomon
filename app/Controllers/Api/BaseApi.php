<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-04
 * Time: 14:30
 */

namespace App\Controllers\Api;


use App\Controllers\Controller;

class BaseApi extends Controller
{
    public function output($status, $data, $msg)
    {
        return $this->response->withJson(
            [
                'status' => $status,
                'data' => $data,
                'message' => $msg
            ]
        );
    }
}