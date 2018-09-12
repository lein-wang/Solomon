<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-06
 * Time: 16:45
 */

namespace App\Controllers\Admin;


use App\Controllers\Controller;
use App\Models\User;
use App\Models\Autoload;
use App\Models\Cache;

class LoginController extends Controller
{
    function index($request, $response, $args)
    {
        if ($this->request->isPost()) {
            $account = trim($this->request->getParam('username'));
            $password = trim($this->request->getParam('password'));
            if (empty($account)) {
                $this->view->assign('message', '用户名不能为空！');
            }
            if (empty($password)) {
                $this->view->assign('message', '密码不能为空！');
            }
//            dump($account);
//            $users = $this->db->select('user','*');
            $user = $this->dao->findOne('user',['name' => $account]);
//            dump($user);
//             dump(md5($password) == $user['passwd']);
//             die;
            if ($user) {
                if (md5($password) == $user['passwd']) {
                    $user['userid'] = $user['id'];
//                    $ur = $this->db->select('user_role', array("uid" => $user['id']));
                    $ur = (new User($this->container))->getUserRoles($user['id']);
                    $is_sys = $is_editor = $is_market = $is_art = 0;
                    if (!empty($ur)) {
                        foreach ($ur as $r) {
                            if (1 == $r["group_id"]) {
                                $is_sys = 1; //sys账号除了不能充值以外，其他功能和管理员一模一样
                            } elseif (2 == $r["group_id"]) {
                                $is_editor = 1;
                            } else if (3 == $r['group_id']) {
                                $is_market = 1;
                            } else if (4 == $r['group_id']) {
                                $is_art = 1;
                            }
                        }
                    }
                    $user["is_sys"] = $is_sys;
                    $user["is_editor"] = $is_editor;
                    $user["is_market"] = $is_market;
                    $user["is_art"] = $is_art;
                    $this->saveDataSession($user);
                    // $this->output(array("info"=>"登陆成功！","status"=>"y"));
                    if ($is_market) {
                        $url = '/admin/index';
                    } elseif ($is_art) {
                        $url = '/admin/index';
                    } else {
                        $url = '/admin/index';
                    }
                    $this->location($url);//登录成功跳转后台首页

                } else {
                    $this->view->assign('message', '密码不正确！');
                }
            } else {
                // $this->output(array("info"=>"用户名不存在","status"=>"n"));
                $this->view->assign('message', '用户名不存在');
            }
        }

        return $this->view->display('login.html', [
//            'name' => 'gjgjgjgjgjg',
//            'is_admin'=>1,
//            'is_sys'=>1,
//            'is_editor'=>1,
//            'is_art'=>1,
//            'is_market'=>1,
//            'is_market'=>1,
//            'controller'=>1,
//            'action'=>1,
        ]);
    }

    public function error_page($request, $response, $args){
        $msg = $args['msg'];
        return $this->view->display('error.html', [
            'msg' => $msg,
        ]);
    }

    public function logout($request, $response, $args){
        $this->session->clean();
        // $this->assign('refurl', $this->get('refurl','/'));
        // $this->assign('error', '已安全退出!');
        $url='/admin/login';
        $this->location($url);
    }

}