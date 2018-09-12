<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-06
 * Time: 15:39
 */

namespace App\Util;


class Session
{
    private static $_instance = null;

    public function __construct()
    {

    }

    static function getInstance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    function getAll(){
        return $_SESSION;
    }

    function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : '';
    }

    function set($key, $val)
    {
        return $_SESSION[$key] = $val;
    }

    function del($key)
    {
        unset($_SESSION[$key]);
    }
    function clean()
    {
        unset($_SESSION);
        session_destroy();
    }
}