<?php
/**
 * desc: 树型结构相关操作
 *       
 *
 *
 *
*/

class CTree {

    public function getNode($id, $ortreeArr, $istree=false)
    {
        if(empty($ortreeArr))return $ortreeArr;
        if($istree){
            $ortreeArr = $this->treeToDimensions($ortreeArr);//变成二维数组
        }
        foreach($ortreeArr as $key=>$node){
            if($id == $node['id']){
                return $node;
            }
        }
        return null;
    }

    /*
    * desc: 获取trees
    *
    *@whArr --- arr [必须为数组]
    *
    *
    */
    public function getTrees($treeids=null, $whArr=array(), $exArr=array())
    {
    }
    public function getTree($treeid=null, $whArr=array(), $exArr=array())
    {
    }

    /*
    * desc: 添加一条tree信息(node)
    *
    *return: array( status  --- 状态(1:成功,0:失败)
    *               message --- 提示信息
    *               tree    --- tree信息
    *               ) 
    *
    */
    public function addNode($node, $ortreeArr, $istree=false)
    {
        if(empty($node))return $ortreeArr;
        if(empty($ortreeArr)){
            $node['id'] = isset($node['id'])&&intval($node['id'])>0?$node['id']:1;
            return array($node);
        }
        if($istree){
            $ortreeArr = $this->treeToDimensions($ortreeArr);
        }
        if(!isset($node['id'])){
            $idArr = array_column($ortreeArr, 'id');
            rsort($idArr); //寻找最大id
            $node['id'] = isset($idArr[0])?intval($idArr[0])+1:1;
        }
        $ortreeArr[] = $node;
        return $istree?$this->dimensionsToTree($ortreeArr):$ortreeArr;
    }
    /*
    * desc: 更新一条tree信息
    *
    *return: array 根据istree返回tree或常规的数组
    *
    */
    public function updateNode($id, $node, $ortreeArr, $istree=false)
    {
        if(empty($node))return $ortreeArr;
        if($istree){
            $ortreeArr = $this->treeToDimensions($ortreeArr);//变成二维数组
        }
        foreach($ortreeArr as &$r0001){
            if($id == $r0001['id']){
                $r0001 = array_merge($r0001, $node);
                break;
            }
        }
        return $istree?$this->dimensionsToTree($ortreeArr):$ortreeArr;
    }
    public function getChildren($id, $ortreeArr, $istree=false)
    {
        if(empty($node))return $ortreeArr;
        if($istree){
            $ortreeArr = $this->treeToDimensions($ortreeArr);//变成二维数组
        }
        $childrenArr = array();
        foreach($ortreeArr as $node){
            if($id == $node['parentid']){
                $childrenArr = $node;
            }
        }
        return $istree?$this->dimensionsToTree($childrenArr):$childrenArr;
    }
    public function getBranch($id, $treeTrr)
    {
        if(empty($treeTrr))return $treeTrr;
        foreach($treeTrr as $key=>$node){
            if($id == $node['id']){
                return $node;
            }
            $branch = $this->getBranch($id, $node['children']);
            if($branch){
                return $branch;
            }
        }
        return null;
    }

    public function dropBranch($id, $treeTrr)
    {
        function _drop($id, &$treeTrr, &$oobj=null){
            if(empty($treeTrr))return $treeTrr;
            foreach($treeTrr as $key=>&$node){
                if($id == $node['id']){
                    unset($treeTrr[$key]);
                    // return $treeTrr;
                    return true;
                }
                $ok = _drop($id, $node['children'], $oobj);
                if($ok){
                    return true;
                }
            }
            return false;
        };
        _drop($id, $treeTrr);
        return $treeTrr;
        /*
        if(empty($treeTrr))return $treeTrr;
        foreach($treeTrr as $key=>&$node){
            if($id == $node['id']){
                unset($treeTrr[$key]);
                // return $treeTrr;
                return true;
            }
            $ok = $this->dropBranch($id, $node['children']);
            if($ok){
                return true;
            }
        }
        return false;
        */
    }
    public function getRoots($ortreeArr, $istree=true)
    {
        if(empty($ortreeArr))return $ortreeArr;
        if(!$istree){
            $ortreeArr = $this->dimensionsToTree($ortreeArr);//变成二维数组
        }
        $rootArr = array();
        foreach($ortreeArr as $node){
            unset($node['children']);
            $rootArr[] = $node;
        }
        return $rootArr;
    }
    /*
    * desc: 切底删除tree的node(推荐用dropBranch)
    * 步骤: 1, 删除tree表中的数据
    *
    */
    public function dropNode($id, $ortreeArr, $istree=false)
    {
        if(empty($ortreeArr))return $ortreeArr;
        if($istree){
            $ortreeArr = $this->treeToDimensions($ortreeArr);//变成二维数组
        }
        foreach($ortreeArr as $key=>$r0001){
            if($id == $r0001['id']){
                unset($ortreeArr[$key]);
                break;
            }
        }
        return $istree?$this->dimensionsToTree($ortreeArr):$ortreeArr;
    }

    /*
    * desc: 切底删除tree的node
    * 步骤: 1, 删除tree表中的数据
    *
    */
    public function dropChildren($id)
    {
        
    }
    /*
    * desc: 根据任意一个id获取它的根节点
    *
    *
    *
    */
    public function getRoot($id, $dataArr=array())
    {
        if(0 and empty($dataArr)){
            //从db查找
        }else{
            /*
            $root = array();
            foreach($dataArr as $row){
                if($row['id'] == $id){
                    $root = $row; //其意义为此函数永远只有一条记录返回
                    if(0 != $row['parentid']){
                        $root = $this->getRoot($row['parentid'], $dataArr);
                    }else{
                        return $root;
                    }
                }
            }
            return $root;
            */
            return array_slice($this->getPedigree($id, $dataArr), 0, 1)[0];
        }
    }

    /*
    * desc: 根据任意一个id获取它的父节点
    *
    *
    *
    */
    public function getParent($id, $ortreeArr, $istree=false)
    {
        if(empty($ortreeArr))return $ortreeArr;
        if($istree){
            $ortreeArr = $this->treeToDimensions($ortreeArr);//变成二维数组
        }
        $node = $this->getNode($id, $ortreeArr);
        foreach($ortreeArr as $key=>$parent){
            if($node['parentid'] == $parent['id']){
                return $parent;
            }
        }
        return false;
    }

    /*
    * desc: 根据任意一个id在一棵树(arr)或db中获取它的兄弟节点
    *
    *
    *
    */
    public function getSiblings($id, $dataArr=array())
    {
        $siblings = array();
        foreach($dataArr as $row){
            if($row['id'] == $id){
                $siblings[] = $row;
                $parentid   = $row['parentid'];
            }
        }
        if(isset($parentid)){
            foreach($dataArr as $row){
                if($row['parentid'] == $parentid && $row['id'] != $id){
                    $siblings[] = $row;
                }
            }
        }
        return $siblings;
    }

    /*
    * desc: 根据任意一个id获取它的家谱序列(所有祖宗主所有子孙)
    *   实现过程可分两步:
    *       1, 取得它的最顶(root)祖宗(topid);
    *       2, 根据topid获取它的所有子孙
    *
    */
    public function getPedigree($id, $ortreeArr, $istree=false)
    {
        if(empty($ortreeArr))return $ortreeArr;
        if($istree){
            $ortreeArr = $this->treeToDimensions($ortreeArr);//变成二维数组
        }

        $pedigree = array();
        foreach($ortreeArr as $row){
            if($row['id'] == $id){
                $pedigree[] = $row; //其意义为此函数永远只有一条记录返回($pedigree = array($row))
                if(0 != $row['parentid']){
                    $pedigree = array_merge($this->getPedigree($row['parentid'], $ortreeArr), $pedigree);
                }
            }
        }

        return $istree?$this->dimensionsToTree($pedigree):$pedigree;
    }
    /*
    * desc: 获取所有叶子节点
    *
    */
    public function getLeafs($ortreeArr, $isarr=true)
    {
        if(empty($ortreeArr))return $ortreeArr;
        if($isarr){
            $ortreeArr = $this->dimensionsToTree($ortreeArr);//变成二维数组
        }
        $leafArr = array();
        foreach($ortreeArr as $subtree){
            if(!empty($subtree['children'])){
                $_tfarr  = $this->getLeafs($subtree['children'], false);
                $leafArr = array_merge($leafArr, $_tfarr);
            }else{
                unset($subtree['children']);
                $leafArr[] = $subtree;
            }
        }
        return $leafArr;
    }
    /*
    * desc: 根据任意一个id获取它的所有祖先
    *
    *
    *
    */
    public function getAncestors($id, $ortreeArr, $istree=false)
    {

    }
    /*
    * desc: 将一个二维数组转换为树结构
    *
    */
    public function dimensionsToTree($dataArr, $pid=0, $id='id', $parentkey='parentid', $subkey='children', $maxDepth=0, $depth=0)
    {
        if(empty($dataArr))return false;
        $depth++;  
        if(intval($maxDepth) <= 0){  
            $maxDepth=count($dataArr) * count($dataArr); 
        }
        if($depth > $maxDepth){  
            return null;
        }  
        $tree=array();
        foreach($dataArr as $rk => $rv){
            if($rv[$parentkey] == $pid){
                $rv[$subkey] = $this->dimensionsToTree($dataArr, $rv[$id], $id, $parentkey, $subkey, $maxDepth, $depth);
                $tree[]=$rv;
            }
        }
        return $tree; 
    }
    /*
    * desc: 将树转换为二维数组结构
    *
    */
    public function treeToDimensions($tree, $pid=0, $id='id', $parentkey='parentid', $subkey='children', $__sorter=1, $__level=0)
    {
        if(!is_array($tree)){
            return $tree;
        }
        $level = $__level + 1;
        $dimensions = array();
        $place  = 0;
        $child_cnt = count($tree);//子节点个数
        foreach($tree as $k=>$node){
            if(empty($node) || !is_array($node))continue;
            $node[$parentkey] = isset($node[$parentkey])?$node[$parentkey]:$pid;//预防子结点没有写parentid
            $node['__sorter'] = isset($node['__sorter'])?$node['__sorter']:$__sorter++;

            $subtree = isset($node[$subkey])?$node[$subkey]:array();
            unset($node[$subkey]);
            $node['__count'] = count($subtree);
            $node['__level'] = $__level;
            $node['__leaf']  = empty($subtree)?1:0;
            $node['__place'] = $place;
            $node['__last']  = $k==($child_cnt-1)?1:0;
            $dimensions[]  = $node;
            $_id_as_child  = isset($node['id'])?$node['id']:0;
            $subdemensions = $this->treeToDimensions($subtree, $_id_as_child, $id, $parentkey, $subkey, $__sorter, $level);
            $dimensions    = array_merge($dimensions, $subdemensions);
            $place++;
        }
        return $dimensions;
    }


    public function changeKeyAsField($dataArr, $field='id')
    {
        if(!$dataArr || !is_array($dataArr))return $dataArr;
        $newArr = array();
        foreach($dataArr as $row) {
            if(!isset($row[$field])){
                return $dataArr;
            }
            $fieldval = $row[$field];
            $newArr[$fieldval] = $row;
        }
        return $newArr;
    }
};