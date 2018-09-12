<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-04
 * Time: 14:30
 */

namespace App\Controllers\Admin;


use App\Controllers\Controller;
use App\Models\User;
use Interop\Container\ContainerInterface;

class BaseAdmin extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $logined = $this->isLogined('id');
        if (!$logined) {
            $this->location('/admin/login');
        }
        $is_sys = isset($_SESSION["is_sys_sess"]) ? intval($_SESSION["is_sys_sess"]) : 0;
        $is_editor = isset($_SESSION["is_editor_sess"]) ? intval($_SESSION["is_editor_sess"]) : 0;
        $is_market = isset($_SESSION["is_market_sess"]) ? intval($_SESSION["is_market_sess"]) : 0;
        $is_art = isset($_SESSION["is_art_sess"]) ? intval($_SESSION["is_art_sess"]) : 0;
        $is_admin = intval($_SESSION["admin_sess"]);
        $this->view->assign(compact("is_sys", "is_editor", "is_admin", "is_market", "is_art"));
        /*管理员直接跳过权限验证 */
        if ($_SESSION['admin_sess']) {
            return true;
        }

        /*判断是否有权限*/
        $route = $this->request->getRequestTarget();
//         var_dump($route);die;
        //sys这个账号除了不能充值，其他和管理员一模一样
        if ($is_sys && !in_array($route, ['admin/finance/rechargeByPlatform', 'admin/finance/prechargeShopBenefitByPlatform'])) {
            return true;
        }
        /*首页也跳过验证*/
//        if($method=='entry'&&$controller=='site')
//        {
//            return true;
//        }
        // var_dump($route);die;
        $uid = $_SESSION['id_sess'];//当前登录用户的id
        $authaccess = (new User($this->container))->check($route, $uid);

        if (!$authaccess) {
            $url = '/admin/error/您的权限不允许，请联系管理员！';
            $this->location($url);//登录成功跳转后台首页
        }
    }

}