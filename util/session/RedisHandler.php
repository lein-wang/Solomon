<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-06
 * Time: 14:40
 */

namespace App\Util;

use Predis\Session\Handler;
use Predis\Client;


class RedisHandler extends Handler
{
    public function __construct($client,$expired)
    {
        parent::__construct($client,['gc_maxlifetime'=>$expired]);
    }


}