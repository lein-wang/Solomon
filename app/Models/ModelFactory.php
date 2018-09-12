<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-07
 * Time: 10:51
 */

namespace App\Models;


use Interop\Container\ContainerInterface;

class ModelFactory
{
    protected $container;
    protected $request;
    protected $response;
    protected $logger;
    protected $redis;
    protected $db;
    protected $view;
    protected $session;
    protected $ins_arr = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $this->container->get('logger');
        $this->redis = $this->container->get('redis');
        $this->db = $this->container->get('db');
    }

    public function make($model_name){
        if (!empty($this->ins_arr[$model_name])){
            return $this->ins_arr[$model_name];
        }else{
            return new $model_name($this->container);
        }
    }

}