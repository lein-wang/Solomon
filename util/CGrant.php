<?php
/**
 * desc: 权限验证
 *      1,检查当前角色是否访问指定URI;
 *      2,
 *
 *
 *
 *
 *
 *
*/
class CGrant {
    
    /**
     *   desc 判断一个角色是否有访问uri的权限
     * @param array  $privilegeArr 权限配置表
     * @param string $route 路由 
     * @param string $uri 
     * @param string $role 角色名称,如果为null时表示当前用户的角色
     *
    */
    static function hasPermission(&$privilegeArr, $route, $uri=null, $role=0)
    {
        $uri   = is_null($uri)?$route:$uri;
        $route = rtrim($route, '/');
        // echo "$routeId -- $dirId -- $ctrlId -- $actionId <br/>";

        //检查ignore表=========================================
        $ignoredArr = &$privilegeArr['ignored'];  //被忽略的action
        $ignoredChr = isset($ignoredArr[$route])?$ignoredArr[$route]:(isset($ignoredArr[$uri])?$ignoredArr[$uri]:null);
        if(!$ignoredChr){
            foreach($ignoredArr as $igroute=>$chr){
                if(strpos($igroute, '*')){
                    $igroute = trim(trim($igroute,'*'), '/');
                    if(1==strpos($route,$igroute) || 1==strpos($uri,$igroute)){
                        //等于1表示route有前斜线
                        $ignoredChr = $chr;
                    }
                }
            }
        }
        // echo ",$route";
        if($ignoredChr){ //对当前路由设置了'忽略'
            if(1 == intval(substr($ignoredChr,-1,1))){ //右边第一位
                return true; //放行
            }else{
            }
        }
        //检查ignore表======================================end

        if(!isset($privilegeArr[$role])){
            return false; //角色不存在
        }
        $priroleArr = &$privilegeArr[$role];
        // echo $route;
        // print_r($priroleArr);
        //         exit;
        //检查route权限====================================
        if(isset($priroleArr[$route])){
            if(1 == intval($priroleArr[$route])){ //0表示被禁止
                $allowed = true;
            }else{
                return false;
            }
        }
        //检查route权限=================================end

        // $this->dump($priroleArr[$uri]);
        //检查uri权限======================================
        if(isset($priroleArr[$uri])){
            if(1 == intval($priroleArr[$uri])){ 
                return true; //明令放行(1)
            }else{
                return true; //明令禁止(0)
            }
        }else{//查找通配符(如:a/b/*)
            foreach(array_keys($priroleArr) as $_p){
                if(strpos($_p, '*')){
                    $__p = trim(trim($_p,'*'), '/');
                    if(0===strpos($route,$__p) || 0===strpos($uri,$__p)){
                        //恒等于0表示__p必须在前端
                        if(1 == intval($priroleArr[$_p])){
                            return true;  //放行
                        }else{
                            return false; //禁止该目录的所有
                        }
                    }
                }
            }
            if(isset($allowed) && $allowed){
                //如果用uri没有找到匹配则用route的匹配结果
                return true;
            }
        }
        //检查uri权限===================================end

        //如果上面都放行===================================
        $_default   = isset($priroleArr['default'])?$priroleArr['default']:0; //默认是放行(1)还是禁止(0)
        if(1 != intval($_default)){
            return false; //默认是禁止的
        }
        //如果上面都放行================================end

        return true;
    }
};
