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
    public function __invoke($request, $response, $args)
    {
        $action = $args['action'];
        if(method_exists($this,$action)){
            return $this->$action();
        }else{
            return $this->output(-1,null,'method not exist!!!');
        }
    }

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