<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-04
 * Time: 11:29
 */

namespace App\Models;


use Interop\Container\ContainerInterface;
use resovler;

class BaseModel
{
    protected $container;
    protected $request;
    protected $response;
    protected $logger;
    protected $redis;
    protected $dao;
    protected $view;
    protected $session;
    static $_instance = array();

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $this->container->get('logger');
        $this->redis = $this->container->get('redis');
        $this->dao = $this->container->get('dao');
    }

    /**
     *
     * @param $name
     * @return mixed
     */
//    public function __get($name)
//    {
//        if ($this->container->get($name)) {
//            return $this->container->get($name);
//        }
//
//    }

//    public function __call($name, $arguments)
//    {
//        return call_user_func_array(array($this,$name),$arguments);
//    }
//

    /**
     * !!!NOTICE!!!
     * 需要把方法设为protected才能用 User::Fn
     * 而且构造函数带参数就会出错
     * !!!NOTICE!!!
     * @param $name
     * @param $arguments
     * @return mixed
     */
//    public static function __callStatic($name, $arguments)
//    {
//        return call_user_func_array(array(new static(), $name), $arguments);
//    }

    /**
     * 全局数据库日志
     */
    public function __destruct()
    {
        if (!empty($this->dao->getDb()->last())) {
            $this->logger->addInfo($this->dao->getDb()->last());
        }
    }


//    public static function instance($container)
//    {
//        $classFullName = get_called_class();
//        if (!isset(static::$_instance[$classFullName])) {
////            core_load_class($classFullName);
//            if (!class_exists($classFullName, false)) {
//                throw new \Exception('"' . $classFullName . '" was not found !');
//            }
//
//            // $_instance[$classFullName] = new $classFullName();
//            // 1、先前这样写的话，PhpStrom 代码提示功能失效；
//            // 2、并且中间变量不能是 数组，如 不能用 return $_instance[$classFullName] 形式返回实例对象，否则 PhpStrom 代码提示功能失效；
//            $instance = static::$_instance[$classFullName] = new static($container);
//            return $instance;
//        }
//
//        return static::$_instance[$classFullName];
//    }

}