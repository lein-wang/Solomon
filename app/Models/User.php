<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-04
 * Time: 11:30
 */

namespace App\Models;

class User extends BaseModel
{

    private $user = 'user';
    private $user_role = 'user_role';
    private $group = 'group';
    private $rules = 'rules';

    protected $config = array(
        'AUTH_ON' => true,                      // 是否开启权限验证
        'AUTH_TYPE' => 1,                         // 认证方式，1为实时认证；2为登录认证。
    );


    /**
     * 检查权限
     * @param name string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param uid  int           认证用户的id
     * @param string mode        执行check的模式
     * @param relation string    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * @return boolean           通过验证返回true;失败返回false
     */
    public function check($name, $uid, $type = 1, $mode = 'url', $relation = 'or')
    {
        if (!$this->config['AUTH_ON'])
            return true;
        $authList = $this->getAuthList($uid, $type); //获取用户需要验证的所有有效规则列表 array   0=>'admin/app/entry'
// var_dump($authList);die;
        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = array($name);
            }
        }
        $list = array(); //保存验证通过的规则名
        if ($mode == 'url') {
            $REQUEST = unserialize(strtolower(serialize($_REQUEST)));
        }
        foreach ($authList as $auth) {
            $query = preg_replace('/^.+\?/U', '', $auth);
            if ($mode == 'url' && $query != $auth) {
                parse_str($query, $param); //解析规则中的param
                $intersect = array_intersect_assoc($REQUEST, $param);
                $auth = preg_replace('/\?.*$/U', '', $auth);
                if (in_array($auth, $name) && $intersect == $param) {  //如果节点相符且url参数满足
                    $list[] = $auth;
                }
            } else if (in_array($auth, $name)) {
                $list[] = $auth;
            }
        }
        if ($relation == 'or' and !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ($relation == 'and' and empty($diff)) {
            return true;
        }
        return false;
    }


    /**
     * 获得权限列表
     * @param integer $uid 用户id
     * @param integer $type
     */
    protected function getAuthList($uid, $type)
    {
        static $_authList = array(); //保存用户验证通过的权限列表
        $t = implode(',', (array)$type);
        if (isset($_authList[$uid . $t])) {
            return $_authList[$uid . $t];
        }
        if ($this->config['AUTH_TYPE'] == 2 && isset($_SESSION['_AUTH_LIST_' . $uid . $t])) {
            return $_SESSION['_AUTH_LIST_' . $uid . $t];
        }

        //读取用户所属用户组
        $groups = $this->getGroups($uid);

        $ids = array();//保存用户所属用户组设置的所有权限规则id
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);//得到当前用户当前角色下的所有可执行节点   roles.id=>'1,2,3,4'
        if (empty($ids)) {
            $_authList[$uid . $t] = array();
            return array();
        }

        $ids = implode(',', $ids);
        $whArr = array(" id in($ids) and type=$type and status=1 ");
        $exArr = array(
            // 'page'      => 1,
            'limit' => 55555,//所有，分个毛线的页
            // 'fields'    => 'cdate,count(mid) cnt_mid',
            'fields' => 'condition,name',
            'only_data' => true,
            // 'group'     => 'cdate',
            // 'order'     => 'cdate asc',
            // 'keyas'     => 'cdate',
        );

        //读取用户组所有权限规则
        // $rules = M()->table($this->_config['AUTH_RULE'])->where($map)->field('condition,name')->select();
//        $rules = $this->getData($this->rules,$whArr,$exArr);
        $rules = $this->dao->findMore($this->rules, ['id' => $ids, 'type' => $type, 'status' => 1], 'condition,name');
        //循环规则，判断结果。
        $authList = array();   //
        foreach ($rules as $rule) {
            if (!empty($rule['condition'])) { //根据condition进行验证
//                $user = $this->getUserInfo($uid);//获取用户信息,一维数组
//
//                $command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $rule['condition']);
//                //dump($command);//debug
//                @(eval('$condition=(' . $command . ');'));
//                if ($condition) {
//                    $authList[] = strtolower($rule['name']);
//                }
            } else {
                //只要存在就记录
                $authList[] = strtolower($rule['name']);
            }
        }

        $_authList[$uid . $t] = $authList;
        if ($this->config['AUTH_TYPE'] == 2) {
            //规则列表结果保存到session
            $_SESSION['_AUTH_LIST_' . $uid . $t] = $authList;
        }
        return array_unique($authList);
    }


    /**
     * 根据用户id获取用户组,返回值为数组
     * @param  uid int     用户id
     * @return array       用户所属的用户组 array(
     *                                         array('uid'=>'用户id','group_id'=>'用户组id','title'=>'用户组名称','rules'=>'用户组拥有的规则id,多个,号隔开'),
     *                                         ...)
     */
    public function getGroups($uid)
    {
        static $groups = array();
        if (isset($groups[$uid]))
            return $groups[$uid];

        /*        $user_groups = M()
                    ->table($this->_config['AUTH_GROUP_ACCESS'] . ' a')
                    ->where("a.uid='$uid' and g.status='1'")
                    ->join($this->_config['AUTH_GROUP']." g on a.group_id=g.id")
                    ->field('rules')->select();
        */

        //找出登录用户有哪些节点id     1,2,3,4,5,6
        //select rules from user_role a inner join role b on a.group_id=b.id
        $sql = 'select rules from user_role a inner join role b on a.group_id=b.id where a.uid = ' . $uid;
        $user_groups = $this->dao->query($sql);
        $groups[$uid] = $user_groups ? $user_groups : array();
        return $groups[$uid];
    }


    /**
     * 获得用户资料,根据自己的情况读取数据库
     */
    public function getUserInfo($uid = 1)
    {
        static $userinfo = array();
        if (!isset($userinfo[$uid])) {
            $whArr = array(
                'id' => $uid
            );
            $exArr = array();
            $userinfo[$uid] = $this->dao->findById($this->user, $uid);//这个是获取记录的一条查询语句
            // $userinfo[$uid]=M()->where(array('uid'=>$uid))->table($this->_config['AUTH_USER'])->find();
        }
        return $userinfo[$uid];
    }


    public function getUserRoles($uid)
    {
        if (empty($uid)) {
            return null;
        }
        $ret = $this->dao->findOne('user_role', array("uid" => $uid));
        return $ret;

    }


}



