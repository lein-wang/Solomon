<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-03
 * Time: 13:48
 */


//  Register the auto loader
require '../vendor/autoload.php';
//require('../vendor/smarty/smarty/libs/Smarty.class.php');
require './helper.php';
require './Db.php';
// require './config.php';
require './SmartyView.php';
require './dao.php';

use DB\Db;
use Predis\Client;
use App\Util\Session;


//  Load `.env` configuration file and keys but not $_SERVER
$previousKeys = array_keys($_ENV);
$env = new Dotenv\Dotenv(ROOT_PATH);
$env->load();
$currentKeys = array_keys($_ENV);
$newKeys = array_diff($currentKeys, $previousKeys);
array_map(function ($key) {
    unset($_SERVER[$key]);
}, $newKeys);
//  Set timezone
@ date_default_timezone_set(env('TIMEZONE', 'PRC'));
//  Create app
$app = new \Slim\App(
    [
        'settings' => [
            'displayErrorDetails' => env('APP_DEBUG', true),


            'logger' => [
                'name' => 'slim-app',
                'level' => Monolog\Logger::DEBUG,
                'path' => ROOT_PATH . '/app.log',
            ],

        ]
    ]
);

$container = $app->getContainer();
$container['logger'] = function ($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler(ROOT_PATH . '/logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['master'] = function ($container) {
    $db = new \Medoo\Medoo(
        [
            'database_type' => env('MASTER_DB_TYPE', 'mysql'),
            'database_name' => env('MASTER_DB_DATABASE'),
            'server' => env('MASTER_DB_HOST'),
            'username' => env('MASTER_DB_USERNAME'),
            'password' => env('MASTER_DB_PASSWORD'),
            'charset' => env('MASTER_DB_CHARSET', 'utf8'),
            'port' => env('MASTER_DB_PORT', 3306),
            'prefix' => env('MASTER_DB_PREFIX', ''),
//            'container' => $container
        ]
    );
    return $db;
};
$container['slave'] = function ($container) {
    $db = new \Medoo\Medoo(
        [
            'database_type' => env('SLAVE_DB_TYPE', 'mysql'),
            'database_name' => env('SLAVE_DB_DATABASE'),
            'server' => env('SLAVE_DB_HOST'),
            'username' => env('SLAVE_DB_USERNAME'),
            'password' => env('SLAVE_DB_PASSWORD'),
            'charset' => env('SLAVE_DB_CHARSET', 'utf8'),
            'port' => env('SLAVE_DB_PORT', 3306),
            'prefix' => env('SLAVE_DB_PREFIX', ''),
//            'container' => $container
        ]
    );
    return $db;
};

$container['dao'] = function ($container) {
    $db = new \DB\Dao($container);
    return $db;
};

$container['redis'] = function ($c) {
    $db = new Client([
        'scheme' => env('REDIS_SCHEME', 'tcp'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 1),
    ]);
    return $db;
};


//session
$handler = env('SESSION_HANDLER');
$expired = env('SESSION_EXPIRE');
$prefix = env('SESSION_PREFIX');
if (!empty($handler)) {
    $handler_class = "\\App\\Util\\" . ucfirst($handler) . "Handler";
    $client = new Client([
        'scheme' => env('REDIS_SCHEME', 'tcp'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 1),

    ], ['prefix' => env('SESSION_PREFIX', 'session:'),]);
    $handler = new $handler_class($client, $expired);
    $handler->register();
    session_set_save_handler($handler, true);
}
ini_set('session.cookie_domain', $_SERVER['SERVER_ADDR']);
ini_set('session.name', 'PHPSESSID_' . $_SERVER['SERVER_PORT']);
if ($expired > 0) {
    ini_set('session.cookie_lifetime', $expired);
}
session_start();


$container['session'] = function () {
    return Session::getInstance();
};


use \Slim\Views\SmartyView as View;

$container['view'] = function ($c) {
    $smarty_config = array(
        'templateDir' => ROOT_PATH . env('SMARTY_templateDir'),
        'compileDir' => ROOT_PATH . env('SMARTY_compileDir'),
        'cachedDir' => ROOT_PATH . env('SMARTY_cachedDir'),
        'configDir' => ROOT_PATH . env('SMARTY_configDir'),
        'pluginsDir' => array(),
        'compile_id' => time() . mt_rand(1, 100)
    );
    $view = new View($smarty_config);
    $view->addExtionsions($c, $smarty_config['pluginsDir']);
    return $view;
};