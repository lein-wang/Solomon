<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-04
 * Time: 11:26
 */

namespace App\Controllers;

use App\Models;
use DB\Dao;
use DB\IDao;
use Interop\Container\ContainerInterface;
use Slim\Views\SmartyView;

class Controller
{
    protected $container;
    protected $request;
    protected $response;
    protected $logger;
    protected $redis;
    protected $dao;
    protected $view;
    protected $session;

    // constructor receives container instance
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $this->container->get('request');
        $this->response = $this->container->get('response');
        $this->logger = $this->container->get('logger');
        $this->redis = $this->container->get('redis');
        $this->dao = $this->container->get('dao');
        $this->session = $this->container->get('session');
        $this->view = $this->container->get('view');
    }

    public function location($url)
    {
        header('location:' . $url);
    }

    protected function isLogined($idflag = 'userid', $expired = 7200)
    {
        return isset($_SESSION[$idflag . '_sess']) ? true : false;
    }

    protected function saveDataSession($datas)
    {
        if (!is_array($datas)) {
            $this->session->set($datas, $datas);
            return;
        }
        foreach ($datas as $f => $v) {
            $fs = $f . '_sess';
            $this->session->set($fs, $v);
        }
    }

    /**
     * @param $model_name
     * @return self
     * @throws \Exception
     */
    protected function loadModel($model_name)
    {
        if (!empty($this->model_arr[$model_name])) {
            return $this->model_arr[$model_name];
        } else {
//            $class_name = "\\App\\Models\\" . $model_name;
            if (class_exists($model_name)) {
                $this->model_arr[$model_name] = $model = new $model_name($this->container);
                return $model;
            } else {
                throw new \Exception("not found $model_name class !!!");
            }

        }
    }


//    public function loadModel($classFullName)
//    {
//
//        dump(count($this->model_arr));
////        $classFullName = get_called_class();
//        if (!isset($this->model_arr[$classFullName]))
//        {
////            core_load_class($classFullName);
//            if (!class_exists($classFullName))
//            {
//                throw new \Exception('"' . $classFullName . '" was not found !');
//            }
//
//            // $_instance[$classFullName] = new $classFullName();
//            // 1、先前这样写的话，PhpStrom 代码提示功能失效；
//            // 2、并且中间变量不能是 数组，如 不能用 return $_instance[$classFullName] 形式返回实例对象，否则 PhpStrom 代码提示功能失效；
//            $instance = $this->model_arr[$classFullName] = new static();
//            $instance->container = $this->container;
//            $instance->db = $this->db;
//            $instance->redis = $this->redis;
//            dump(count($this->model_arr));
//            return $instance;
//        }
//
//        return $this->model_arr[$classFullName];
//    }


    protected function error($message = '服务器错误', $status = 0)
    {
        $jArr = array('status' => $status, 'message' => $message);
        return $this->response->withJson($jArr);
    }

    protected function response($data = null, $status = 1, $message = '')
    {
        $jArr = array('status' => $status, 'message' => $message, 'data' => $data);
        return $this->response->withJson($jArr);
//        exit(json_encode($jArr));


    }

    /**
     * 
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if($this->container->get($name)){
            return $this->container->get($name);
        }

    }

}