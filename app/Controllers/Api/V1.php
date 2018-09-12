<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-04
 * Time: 11:28
 */

namespace App\Controllers\Api;



class V1 extends BaseApi
{
    public function index(){
        // dump(debug_backtrace());
        echo 'end :'.microtime() .'<br>';
        die;
        $this->logger->addInfo('Something interesting happened');
        $users = $this->dao->select('user','*');
        var_dump($users);
        die;
        $this->redis->set('users',json_encode($users));
        print_r($_FILES);
        print_r($_POST);
        print_r($this->request->getUploadedFiles());
        die;
    }

}