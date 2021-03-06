<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-04
 * Time: 11:28
 */

namespace App\Controllers\Admin;


class UserController extends BaseAdmin
{
    
    public function index(){
        $this->session->set('name', "hehe1212");
        return $this->view->display('activity/add.html', [
            'name' => 'gjgjgjgjgjg',
            'is_admin'=>1,
            'is_sys'=>1,
            'is_editor'=>1,
            'is_art'=>1,
            'is_market'=>1,
            'is_market'=>1,
            'controller'=>1,
            'action'=>1,
        ]);
    }
    public function name(){
        dump($this->session->getAll());
    }

    public function __invoke($request, $response, $args)
    {
        $action = $args['action'];
        if(method_exists($this,$action)){
            return $this->$action();
        }else{
            $url = '/admin/error/路径错误！';
            $this->location($url);
        }
    }

}