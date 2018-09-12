<?php

/*
 * --------------------------------
 * 跨域
 * --------------------------------
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, UPDATE');

/*
 * --------------------------------
 * 定义常量
 * --------------------------------
 */
define('ROOT_PATH', dirname(dirname(__FILE__)));                  //根物理目录
define('ROOT_URL', $_SERVER["SERVER_ADDR"]);                            //根URL
define('RES_PATH', dirname(dirname(__FILE__)) . '/resources');    //资源物理目录
define('LOG_LOC', dirname(dirname(__FILE__)) . '/logs');          //日志物理目录

/*
 * --------------------------------
 * 引入路由
 * --------------------------------
 */
require './bootstrap.php';
require './route.php';

/*
 * --------------------------------
 * 启动
 * --------------------------------
 */
$app->run();