<?php
/**
 * Author: cty@2009-12-01
 *   Desc: php xml parser
 *  
 * xml类命名规则:
 *   root  -- 顶层结点
 *   pNode -- 父结点
 *   cNode -- 当前结点
 *   sNode -- 子结点
 *   dNode -- 要删除结点
 *   nNode -- 新结点
 *   oNode -- 旧结点
 *   mNode -- 下一个结点
 *   fNode -- 第一个子结点
 *
 */

class CXml extends DomDocument{

    var $error  = null;
    var $encode = 'utf-8';
    var $ver    = '1.0';
    function __construct($encode='utf-8', $ver="1.0")
    {
        parent::__construct($ver, $encode);//必须调用!    
        $this->encode = $encode;
        $this->ver    = $ver;
    }
    
    function getError()
    {
        return $this->error;
    }
    
    //load xml from file
    function loadFile($fpxml)
    {  
        return $this -> load($fpxml);    
    }
    
    //load xml from string
    function loadString($string)
    {
        //load from source string
        $string = $this->cleanCDATAs($string);
        return $this -> loadXML($string);  
    }
    
    //根据标签名称获取结点列表(get node list by tag name)
    function getNodeList($tag)
    {
        $nodeList = $this -> getElementsByTagName($tag);
        return $nodeList;
    }
    
    //get root node  
    function getRoot()
    {
        return $this->firstChild;
    }
    
    /* translate xml node to string
    *  if $node=NULL then return string of all nodes 
    */
    function xml2string($node=NULL)
    {
        return $this -> saveXML($node);     
    }
    function xml2array($root=null, $level=0, $level_sort = -1)
    {
        if(null === $root)$root = $this->getRoot();
        $result = array(); 
        if($root->hasAttributes()){ 
            $attrs = $root->attributes; 
            foreach ($attrs as $i => $attr) 
                $result[$attr->name] = $attr->value; 
        }
        if($root->nodeType == XML_TEXT_NODE){
            $result[$root->nodeName] = $root->nodeValue; 
        }else{
            $children = $root->childNodes;
            if(1 == $children->length){ 
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE){ 
                    $result['_value'] = $child->nodeValue;
                    if (count($result) == 1) 
                        return $result['_value']; 
                    else 
                        return $result; 
                }
            }
            $group = array();
            $temp  = array();
            for($i = 0; $i < $children->length; $i++){ 
                $child = $children->item($i);
                $nName = $child->nodeName;
                
                $_level = $level + 1;
                if(!isset($result[$nName])){
                    $arr = $this->xml2array($child, $_level, $level_sort);
                    $result[$nName] = $arr;
                    $temp[$nName][$i] = $arr;
                }else{
                    if(!isset($group[$nName])){ 
                        $tmp = $result[$nName]; 
                        $k = array_keys($temp[$nName])[0];
                        if($level <= $level_sort)
                            $result[$nName] = array($k=>$tmp); 
                        else
                            $result[$nName] = array($tmp); 
                        $group[$nName] = 1;
                    }
                    //同一级保持相同的顺序
                    if($level <= $level_sort)
                        $result[$nName][$i] = $this->xml2array($child, $_level, $level_sort);
                    else
                        $result[$nName][] = $this->xml2array($child, $_level, $level_sort); 
                }
            }
        }
        return $result;
    }

    //添加顶层结点
    function addRoot($name="root")
    {
        $root = $this->createElement($name);
        $this->appendChild($root);
        if($root)
            return $root;
        else 
            return false;
    }
    
    //添加子结点(父结点,子结点)
    function addNode($cNode, $pNode=null)
    {
        //nNode: new node
        if(is_string($cNode)){
            $cNode = $this->string2xml($cNode);
        }
        if(null == $pNode){
            $this->appendChild($cNode);
            return $this;
        }else{
            $pNode->appendChild($cNode);    
        }
        return $pNode;
    }
    private function _cloneNode($node,$doc)
    {
        $nd=$doc->createElement($node->nodeName);
                
        foreach($node->attributes as $value){
            // echo $value->nodeName . " =====\n";
            $nd->setAttribute($value->nodeName,$value->value);
        }
        if(!$node->childNodes) 
            return $nd;
        foreach($node->childNodes as $child) {
            if($child->nodeName=="#text")
                $nd->appendChild($doc->createTextNode($child->nodeValue));
            else
                $nd->appendChild($this->_cloneNode($child,$doc));
        }
        return $nd;
    }
    /*
    * desc: 附加一段xml
    *
    */
    function append($strxml, $pNode=null)
    {
        try{
            $tmp = new DomDocument($this->encode, $this->ver);
            $tmp->loadXML($strxml);
        }catch(Exception $e){
            return $this;
        }
        $nodes = $this->_cloneNode($tmp->firstChild, $this);
        if(null == $pNode){
            try{
                $this->getRoot()->appendChild($nodes);
            }catch(Exception $e){
                echo $e;
            }
            return $this;
        }else{
            $pNode->appendChild($nodes);    
        }
        return $pNode;
    }
    //创建新结点
    function newNode($tag)
    {
        return $this->createElement($tag);
    }
    //查找一个结点
    function lookupNode($tag, $pNode=null)
    { 
        if(null === $pNode) {
            return $this->getElementsByTagName($tag)->item(0);
        }else {
            return $pNode->getElementsByTagName($tag)->item(0);
        }
    }
    //查找一个结点
    function lookupNodeByKeyVal($tag, $key, $val)
    {
        $nodeList = $this->getNodeList($tag);    
        $nodeArr = array();  
        foreach($nodeList as $node) 
        {    
            if($node->getAttribute($key) == $val) {
                return $node;
            }
        }
        return false;
    }
    function setNodeText($node, $text)
    {
        $node->nodeValue = $text;
    }
    
    function getNodeText($node)
    {
        return $node->nodeValue;
    }
    //更新结点
    function updateNode($pNode, $nNode, $oNode)
    {
        return $pNode->replaceChild($nNode, $oNode);
        try
        {
            // throw new Exception();
            return $pNode->replaceChild($nNode, $oNode);
        }catch(Exception $e) {
            $this->error = "Remove node failure!\nDetail:$e!";
            return false;
        }    
    }
    //移除子结点(父结点,子结点)
    function removeChilds($pNode, $tag, $att=null, $val=null)
    { 
        if($pNode) {
            $childList = $pNode->childNodes;
            $rmArr = array();
            if($childList) {
                foreach($childList as $cNode) {
                    if(null === $att) {
                        if($tag==$cNode->nodeName) $rmArr[] = $cNode;
                    }else {
                        if($tag==$cNode->nodeName && $val==$this->getAtt($cNode,$att)) $rmArr[] = $cNode;
                    }
                }
                foreach($rmArr as $cNode) {
                    $pNode->removeChild($cNode);
                }
            }
            return true;
        }else {
            return false;
        }
    }
    //移除子结点(父结点,子结点)
    function removeNode($pNode, $cNode)
    { 
        if($pNode && $cNode) {
            return $pNode->removeChild($cNode);
        }else {
            return false;
        }
    }
    //移除含有cNode的所有子结点(父结点
    function removeChildren($pNode, $cNode)
    {
        return  $pNode->removeChild($cNode);    
    }
    //获取pNode所有子结点, return child array
    function getChildren($pNode)
    { $arrChild = array();
        $cNode = $pNode->firstChild;
        if(!$cNode) return $arrChild;
        $arrChild[] = $cNode;    
        while($cNode=$cNode->nextSibling)
        {      
        $arrChild[] = $cNode;
        }
        return $arrChild;
    }
    function getChild($pNode, $tag=null)
    { 
        if(null === $tag) {
        return $pNode->firstChild;
        }else {
        $childList = $pNode->childNodes;
        foreach($childList as $cNode) {
            if($tag == $cNode->nodeName) {
            return $cNode;
            }
        }
        }
    }
    function getChildList($pNode, $tag=null)
    {
        if(!$pNode) return false;
        $childList = $pNode->childNodes;
        if(!$childList) return false;
        $partArr = array();
        if(null === $tag) {
        foreach($childList as $cNode) $partArr[] = $cNode;
        }else {      
        foreach($childList as $cNode) {
            // echo $cNode->nodeName . " >>>>>>>: ";
            if($tag == $cNode->nodeName) {
            $partArr[] = $cNode;
            // echo $cNode->nodeName . " add =======================\n";
            // $pNode->removeChild($cNode);
            }
        }
        }
        return $partArr;
    }
    //获取pNode子结点, return child node
    function getChildByTag($pNode, $tag)
    { 
        if(!$pNode) return false;
        $arrChild = array();
        $dNode = $pNode->firstChild;
        if($this->getNodeName($dNode) == $tag) {
        return $dNode;
        }
        while($dNode=$dNode->nextSibling)
        { if($this->getNodeName($dNode) == $tag) {
            return $dNode;
        }
        }
        return false;
    }
    //根据key=val查找相应结点
    function getChildByKeyVal($pNode, $key, $val)
    { if(!$pNode) return false;
        $nodeList = $pNode->childNodes;
        if(!$nodeList) return false;
        $nodeArr = array();  
        foreach($nodeList as $node) 
        { if(XML_ELEMENT_NODE != $node->nodeType)continue;
        if($node->getAttribute($key) == $val) {
            return $node;
        }
        }
        return false; 
        /* 
        $cNode = $pNode->firstChild;
        // echo $cNode->nodeType;
        // echo XML_ELEMENT_NODE;
        if(XML_ELEMENT_NODE==$cNode->nodeType && $val==$cNode->getAttribute($key)) {
        return $cNode;
        }
        while($nNode=$cNode->nextSibling) {
        // echo $nNode->nodeValue;
        if(XML_ELEMENT_NODE==$nNode->nodeType && $val==$nNode->getAttribute($key)) {
            return $nNode;
        }
        }
        return false; */
    }
    //获取结点名称
    function getNodeName($node)
    { if(!$node) return '';
        return $node->nodeName;
    }
    //移除所有子结点(父结点
    function removeAllCNode($pNode)
    {
        $cNode = $pNode->firstChild;    
        while($nNode=$cNode->nextSibling)
        {
        $pNode->removeChild($nNode);
        }
        $pNode->removeChild($cNode);
        return True;
    }
    //在众多字结点中删除含有$key=$val的结点(当前结点名称,属性,值)
    function removeNodeByKeyVal($cName, $key, $val)
    {
        $nodeList = $this->getNodeList($cName);    
        //echo $nodeList->length; //长度(结点个数)
        $nodeArr = array();  
        foreach($nodeList as $node) 
        {    
        if($node->getAttribute($key) == $val) {                
            $nodeArr[] = $node;        
        }
        }
        foreach($nodeArr as $node)
        {
        $node->parentNode->removeChild($node);
        }
    }  
        
    //设置属性(当前结点,属性名称,属性值)
    function setAtt($cNode, $att, $val)
    {  
        return $cNode -> setAttribute($att, $val);
    }
    function getAtt($cNode, $att)
    { 
        if(!$cNode) return false;
        return $cNode -> getAttribute($att);    
    }
    
    //移除属性(当前结点,属性名称)
    function removeAttr($cNode, $aName)
    { 
        if(!$cNode) return false;
        return $cNode -> removeAttribute ($aName);    
    }
    
    function save($fpxml)
    {
        parent::save($fpxml);
    }
    
    //将字符串转换为结点,如:<nnn a1='1' a2='2' ... >text</nnn>
    function string2node($str)
    {
        if(strlen($str)<=0) return;
        preg_match_all("/<([a-z_0-9\:]+)\s*.*?>([^<]*)/si", $str, $tagArr);
        if(empty($tagArr[0])) return;
        $tag  = trim($tagArr[1][0]);
        $text = trim($tagArr[2][0]);
        if(strlen($tag)<=0) return;
        $nNode = $this->createElement($tag);
        if(strlen($text)>0) {
            $tNode = $this->createTextNode($text);
            $nNode->appendChild($tNode);
        }
        preg_match_all("/([a-z0-9]*)\s*?\=\s*[\"\'](.*?)[\"\']/si", $str, $attArr);
        $keyArr = $attArr[1];
        $valArr = $attArr[2];
        for($i=0,$max=count($keyArr); $i<$max; $i++){
            $aName = $keyArr[$i];
            $aVal  = $valArr[$i];
            $nNode -> setAttribute($aName, $aVal);
        }
        //echo htmlspecialchars($this->xml2string($nNode));
        return $nNode;
    }
    
    //遍历当前结点的所有子结点, callback:回调函数
    function walkSubNode($cNode, $callback=null)
    {
        //$tagName = $cNode->nodeName;
        if($callback) { 
            $isWalk = $callback($cNode);
            if(!$isWalk) return;//不再继续遍历
        }
        if($cNode->hasChildNodes()) {
            foreach($cNode->childNodes as $sNode)
            {
                walkSubNode($sNode, $callback);
            }
        }
    }

    function cleanCDATAs($xml)
    {
        $state = 'out';
        $a = str_split($xml);
        $new_xml = '';
        foreach ($a AS $k => $v) {
            // Deal with "state".
            switch ($state) {
                case 'out' :
                    if ('<' == $v) {
                        $state = $v;
                    } else {
                        $new_xml .= $v;
                    }
                    break;
                case '<' :
                    if ('!' == $v) {
                        $state = $state . $v;
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                    break;
                case '<!' :
                    if ('[' == $v) {
                        $state = $state . $v;
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                    break;
                case '<![' :
                    if ('C' == $v) {
                        $state = $state . $v;
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                    break;
                case '<![C' :
                    if ('D' == $v) {
                        $state = $state . $v;
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                    break;
                case '<![CD' :
                    if ('A' == $v) {
                        $state = $state . $v;
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                    break;
                case '<![CDA' :
                    if ('T' == $v) {
                        $state = $state . $v;
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                    break;
                case '<![CDAT' :
                    if ('A' == $v) {
                        $state = $state . $v;
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                    break;
                case '<![CDATA' :
                    if ('[' == $v) {
                        $cdata = '';
                        $state = 'in';
                    } else {
                        $new_xml .= $state . $v;
                        $state = 'out';
                    }
                    break;
                case 'in' :
                    if (']' == $v) {
                        $state = $v;
                    } else {
                        $cdata .= $v;
                    }
                    break;
                case ']' :
                    if (']' == $v) {
                        $state = $state . $v;
                    } else {
                        $cdata .= $state . $v;
                        $state = 'in';
                    }
                    break;
                case ']]' :
                    if ('>' == $v) {
                        $new_xml .= htmlentities($cdata);
                        #       $new_xml.= $cdata;
                        //                        $new_xml .= str_replace('>','>',
                        //                                  str_replace('>','<',
                        //                                str_replace('"','"',
                        //                              str_replace('&','&',
                        //                            $cdata))));
                        $state = 'out';
                    } else {
                        $cdata .= $state . $v;
                        $state = 'in';
                    }
                    break;
            } // switch
        }
        return $new_xml;
    }

    
    //html修复(需要tidy库)
    static function repairHTML($html, $encode="utf8")
    {
        if(!function_exists("tidy_parse_string")) return $html;
        $config = array('indent' => TRUE, 
                        );
    
        $tidy = tidy_parse_string($html, $config, 'UTF8');
        $body = $tidy->body();
        // return html_entity_decode($body->value);
        return $body->value;
    }
    
    //xml修复(需要tidy库)
    static function repairXML($strxml, $encode="utf8")
    {
        if(!function_exists("tidy_parse_string")) return $strxml;
        $strxml = self::repairHTML($strxml, $encode);
        $config = array('indent' => TRUE, 
                        "add-xml-decl"=>true, 
                        "add-xml-space"=>true,
                        "assume-xml-procins"=>true,
                        "input-xml"=>true,
                        "output-xml"=>true,
                        );
    
        $tidy = tidy_parse_string($strxml, $config, 'UTF8');
        return $tidy->value;
    
        //$tidy = tidy_parse_string($strxml);
        // $body = $tidy->Body();
        // return html_entity_decode($body->value);
        /* 
        if(!function_exists("tidy_repair_string")) return $strxml;
        $cfgArr = array("add-xml-decl"=>true, 
                        "add-xml-space"=>true,
                        "assume-xml-procins"=>true,
                        "input-xml"=>true,
                        "output-xml"=>true,
                        );
        return tidy_repair_string($strxml, $cfgArr, $encode);
        */
    }
};
