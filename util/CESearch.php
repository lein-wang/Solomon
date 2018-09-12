<?php
/**
 *autor: cuity@20111104
 * func: elsticsearch搜索接口
*/

class CESearch extends _CFilter {
    protected $htkey          = '';       //用于高亮的关键词(最原始的搜索关键词)
    protected $keys           = '';       //搜索时的关键词(如果htkey没有设也可用于高亮)
    protected $appendedqstr   = '';       //在query_string添加的附加条件
    protected $dftoperator    = 'AND';    //默认操作符

    protected $filterArr      = array();
    protected $facetsArr      = array();  //聚合查询
    protected $facetfields    = array();  //被聚合字段,它是一个数组

    protected $mappArr        = array();  //字段类型(创建索引时使用)
    protected $sortArr        = array();  //排序字段array('field'=>'asc|desc');
    protected $htfieldArr     = array();  //高亮字段
    protected $highlightArr   = array();  //高亮字段(详见setHighArr方法)
    protected $maxPage        = 100;      //最大页数
    protected $highType       = 1;        //highlight类型(1:php client高亮,2:ES高亮)
    
    public  $baseUrl   = '';
    private $ip        = '127.0.0.1';
    private $port      = 9200;
    private $analyzer  = 'ik'; //分词器[ik|mmseg|stand|whitespace]
    
    private $realUrl   = '';   //搜索url
    private $dropUrl   = '';   //删除索引url
    private $mappUrl   = '';   //设置字段类型url
    private $typeUrl   = '';   //此url只到type为止
    private $indexName = '';
    private $typeName  = '';
    private $indxUrl   = '';
    
    const SEARCH_SORT_AND = ' AND ';
    const SEARCH_SORT_OR  = ' OR ';
    
    /**
    * @cfgArr --- array 
    *             index: 一般传数据库表名就可以了
    *             ip: ES server ip
    *             port: ES server port
    */
    public function __construct($cfgArr=array()) 
    {
        $this->setConfig($cfgArr);
    }
    public function setConfig($configArr)
    {
        if(is_array($configArr)){
            extract($configArr);
        }else{
            $index = $type = $configArr;
        }
        $this->ip      = $ip      = isset($ip)?$ip:$this->ip;
        $this->port    = $port    = isset($port)?$port:$this->port;
        
        $this->indexName = $indexName = isset($index)?$index:'';
        $this->typeName  = $type = isset($type)?$type:null;
    
        $this->baseUrl = "http://{$ip}:{$port}";
            
        $type = null===$type ? $indexName : $type;
        $this->realUrl   = $this->baseUrl . '/'. $indexName . '/' . $type . '/_search';
        $this->mappUrl   = $this->baseUrl . '/'. $indexName . '/' . $type . '/_mapping';
        $this->typeUrl   = $this->baseUrl . '/'. $indexName . '/' . $type;
        $this->dropUrl   = $this->baseUrl . '/'. $indexName . '/';
        $this->indxUrl   = $this->dropUrl;
        
        $this->typeName  = $type;
        $this->indexName = $indexName;
    }
    public function getConfig()
    {
        $configArr = array();
        $configArr['baseUrl']   = $this->baseUrl;
        $configArr['ip']        = $this->ip;
        $configArr['port']      = $this->port;
        $configArr['analyzer']  = $this->analyzer;   //分词器[ik|mmseg]
        $configArr['realUrl']   = $this->realUrl;   //搜索url
        $configArr['dropUrl']   = $this->dropUrl;   //删除索引url
        $configArr['mappUrl']   = $this->mappUrl;   //设置字段类型url
        $configArr['typeUrl']   = $this->typeUrl;   //此url只到type为止
        $configArr['indexName'] = $this->indexName;
        $configArr['typeName']  = $this->typeName;
        $configArr['indxUrl']   = $this->indxUrl;
        return $configArr;
    }
    public function useIndex($index)
    {
        $cfgArr = array('index'=>$index);
        $configArr = $this->getConfig();
        $configArr = array_merge($configArr, $cfgArr);
        $this->setConfig($configArr);
        return $this;
    }
    /**
    * add by cuity on 2011-10-21
    * 搜索需要json格式如
    ：'{"query":{"bool":{"must":{"range":{"created_time":{"from":"1275361350","to":"1275361352"}}},"must":{"query_string":{"query":"幽默"}}}}}'
    * 因为有must同级同名，需要拼装json
    *
    * @param string $keywords 搜索的关键字
    * @param array $filter 需要搜索的字段。格式为 array('range' => array(field => value, ...), 'equals' => array(field => value, ....))
    * @param string $sort 需要搜索字段，如果正序排列就为fieldname， 如果反序则为filedname:reverse （目前post方式不支持）
    * @param int $from 开始位置
    * @param int $size 取数据的条数
    * 
    * 示例:
    * Array
        (
            [query] => Array
                (
                    [query_string] => Array
                        (
                            [query] => username:126 OR email:123
                        )
        
                )
        
            [size] => 6
            [from] => 0
            [min_score] => 0.01
            [filter] => Array
                (
                    [and] => Array
                        (
                            [0] => Array
                                (
                                    [range] => Array
                                        (
                                            [userid] => Array
                                                (
                                                    [from] => 11432
                                                    [to] => 152557
                                                )
        
                                        )
        
                                )
        
                            [1] => Array
                                (
                                    [term] => Array
                                        (
                                            [userid] => 11432
                                        )
        
                                )
        
                            [2] => Array
                                (
                                    [term] => Array
                                        (
                                            [type] => 1
                                        )
        
                                )
        
                        )
        
                )
        
        )
        [ sortArr = array('id'=>'asc') ] trimQuerys
    */
    private function trimQuerys($keywords, $from, $size) 
    {
        $whArr = array();
        $cdtions = $this->appendedqstr;
        $dftoperator = $this->dftoperator;
        if(strlen($keywords)>0) {
            $whArr['query']['query_string'] = array('query'=>$keywords.$cdtions);
            $whArr['query']['query_string']['default_operator'] = $dftoperator;
            // $whArr['query']['query_string']['analyzer'] = 'ik';
        }else{
            if(strlen($cdtions)>0){
                $cdtions = ltrim(ltrim(ltrim($cdtions),'OR'),'AND');
                $whArr['query']['query_string'] = array('query'=>$cdtions);
                $whArr['query']['query_string']['default_operator'] = $dftoperator;
            }
        }
        // $whArr['explain'] = true;
        //hightline
        /*
        $highArr = json_decode('{
                "tags_schema" : "styled",
                "pre_tags" : ["<b style=\"color:#ff0000\">"],
                "post_tags" : ["</b>"],
                "fields" : {
                    "name" : {"fragment_size" :  512,"number_of_fragments" : 128 },
                    "description" : {"fragment_size" :  512,"number_of_fragments" : 128 }
                }
            }', true);
        $whArr['highlight'] = $highArr;
        */
        //改用php客户端着色
        if(2==$this->highType && is_array($this->highlightArr) && count($this->highlightArr)>0) {
            $whArr['highlight'] = $this->highlightArr;
        }
        $whArr['size'] = $size;
        $whArr['from'] = $from;
        // $whArr['min_score'] = 0.01;
        
        $filter = $this->getAllFilter();
        if(is_array($filter) && count($filter)>0){
            $whArr['filter']['and'] = $filter;
        }
        if(is_array($this->sortArr) && count($this->sortArr)>0) {
            $whArr['sort'] = $this->sortArr; //array('age'=>'desc');
        }
        //$whArr['filter']['ids'] = array('values'=>array(1));
        if(is_array($this->idinArr) && count($this->idinArr)>0) {
            $whArr['filter']['and'][] = array('ids' => array('values'=>$this->idinArr));
        }
        if(is_array($this->idoutArr) && count($this->idoutArr)>0) {
            $whArr['filter']['and'][]['not'] = array('ids' => array('values'=>$this->idoutArr));
        }
        $whArr = array();
        // $whArr['filter']['and'][] = array('term' => array('name'=> '张飞'));
        // $whArr['filter']['and'][] = array('range' => array('age'=>array('from'=> 2001, 'to'=>2010)));
        // $whArr['filter']['and'][]['or'] = array(array('term' => array('name'=> '张飞')));
        // $whArr['filter']['and'][]['prefix'] = array('name'=> '关');
        
        $whArr = $this->getFilters('and');
        /* //地理位置过滤
        {
            "filtered" : {
                "query" : {
                    "match_all" : {}
                },
                "filter" : {
                    "geo_bounding_box" : {
                        "pin.location" : {
                            "top_left" : [40.73, -74.1],
                            "bottom_right" : [40.717, -73.99]
                        }
                    }
                }
            }
        }*/ 
        // 31.295788,120.669265', 'addr'=>'苏州维景国际大洒店', 'desc'=>'test'),
        // 2=>array('id'=>2, 'll'=>'31.149945,121.804619 //31.295788,120.669265
        if(is_array($this->filterGeoDist) && count($this->filterGeoDist)>0) {
            $whArr['filter']['and'][] = $this->filterGeoDist;
        }
        if(is_array($this->filterGeoBBox) && count($this->filterGeoBBox)>0) {
            $whArr['filter']['and'][] = $this->filterGeoBBox;
        }
        // print_r($whArr);
        return $whArr;
    }
    
    private function makeQuerys($keywords, $from, $size) 
    {
        $whArr   = $this->getFilters('and');
        $cdtions = $this->appendedqstr;
        $dftoperator = $this->dftoperator;
        if(strlen($keywords)>0) {
            $whArr['query']['query_string'] = array('query'=>$keywords.$cdtions);
            $whArr['query']['query_string']['default_operator'] = $dftoperator;
            $whArr['query']['query_string']['analyzer'] = $this->analyzer;
        }else{
            if(strlen($cdtions)>0){
                $cdtions = ltrim(ltrim(ltrim($cdtions),'OR'),'AND');
                $whArr['query']['query_string'] = array('query'=>$cdtions);
                $whArr['query']['query_string']['default_operator'] = $dftoperator;
            }
        }
        // $whArr['explain'] = true;
        /*
        $highArr = json_decode('{
                "tags_schema" : "styled",
                "pre_tags" : ["<b style=\"color:#ff0000\">"],
                "post_tags" : ["</b>"],
                "fields" : {
                    "name" : {"fragment_size" :  512,"number_of_fragments" : 128 },
                    "description" : {"fragment_size" :  512,"number_of_fragments" : 128 }
                }
            }', true);
        $whArr['highlight'] = $highArr;
        */
        //改用php客户端着色
        if(2==$this->highType && is_array($this->highlightArr) && count($this->highlightArr)>0) {
            $whArr['highlight'] = $this->highlightArr;
        }
        //{"fields":["_parent","_source"],"query":{"bool":{"must":[],"must_not":[],"should":[{"match_all":{}}]}},"from":0,"size":50,
        //"sort":[{"rank":{"reverse":false}}],"facets":{},"version":true}:
        if(is_array($this->sortArr) && count($this->sortArr)>0) {
            $whArr['sort'] =  $this->sortArr;/*array(array('rank'=>array('reverse'=>true)));*/ //array('age'=>'desc');
        }

        $whArr['size'] = $size;
        $whArr['from'] = $from;
        // $whArr['min_score'] = 0.01;
        // print_r($whArr);

        if(!empty($this->facetsArr) && isset($this->facetsArr['facets'])){
            $whArr['facets'] = $this->facetsArr['facets'];
        }

        return $whArr;
    }
    
    /*
    * desc: facets切面
    * 协议说明:
        curl -X POST http://localhost:9200/goods/_search -d '{
            "query" : { "query_string" : {"query" : "title:eco"} },
            "size":0,
            "facets" : {
              "brandid" : { "terms" : {"field" : "brandid", "size":50} },
              "cateid" : { "terms" : {"field" : "cateid", "size":50} },
            }
        }'
    *@fields --- str 字段名(eg.brandid,cateid)
    */
    public function facets($fields, $size=100, $exArr=array())
    {
        $fields = trim(trim($fields), ',');
        if(empty($fields)) return $this;
        $fieldArr = explode(',', $fields);
        $facetArr = array();
        foreach($fieldArr as $field){
            $facetArr[$field] = array("terms" => array('field'=>$field, 'all_terms'=>false, 'size'=>$size));
        }
        $this->facetsArr = array(
            'facets' => $facetArr,
        );
        /*
        $this->facetsArr = array(
            'facets' => array(
                //array('fields'=>array('id', 'cateid')
                // 'tags' => array("terms" => array('field'=>$field, 'all_terms'=>false, 'size'=>$size))
                // 'tags' => array("terms" => array('fields'=>array('brandid', 'cateid'), 'all_terms'=>false, 'size'=>$size))
                'brandid' => array("terms" => array('field'=>'brandid')),
                'cateid'  => array("terms" => array('field'=>'cateid')),
            )
        );*/
        $this->facetfields = $fieldArr;
        return $this;
    }
    
    protected function setDftOperator($optor='OR')
    {
        $this->dftoperator = $optor;
    }
    public function operator($op='AND')
    {
        $this->dftoperator = $op;
        return $this;
    }
    /*
    public function search2($key, $fieldArr, $page=0, $size=20)
    { $realkey = '';
        if(is_array($fieldArr) && count($fieldArr)) {
        foreach($fieldArr as $field) {
            
        }
        }else {
        $realkey = $key;
        }
    }*/
    /**
    *以post方式获取数据
    *@page : int --- 当前页
    *@size : int --- 一次搜索的条数
    *return array
    */
    public function search($keys=null, $size=20, $page=0)
    {
        $page = ($page > 0) ? $page : 1;
        $from = ($page - 1) * $size;
        $this->keys = $keys;
        $paraArr = $this->makeQuerys($keys, $from, $size);
        /*$paraArr['facets'] = array(
            'tags' => array('terms' => array('field'=>'brandid'))
        );*/
        // $query = '{
        //     "query": {"query_string": {"query": "title:105"}},
        //     "facets": {"brandid": {
        //         "terms": {"field": "brandid"}},
        //     "cateid": {
        //         "terms":{"field":"cateid"}}
        //     }
        // }';
        // $paraArr = json_decode($query, true);
        // print_r($paraArr);
        /*
        curl -X POST http://localhost:9200/goods/_search -d '{
            "query" : { "query_string" : {"query" : "title:eco"} },
            "size":0,
            "facets" : {
              "tags" : { "terms" : {"field" : "brandid", "size":50} }
            }
        }'
        {"query":{"query_string":{"query":"(nickname:mtce2 OR truename:mtce2)","type":"phrase"}},"size":20,"from":0,"min_score":0.01,"filter":{"and":[{"range":{"age":{"from":"25","to":"35"}}}]},"sort":{"userid":"asc"}}
        */
        $result = self::curlSend($this->realUrl, $paraArr);

        $rstArr = $this->_parseGetDataResult($result['text'], $page, $size);
        $rstArr['page'] = $this->_addPage($rstArr['total'], $page, $size);
        return $rstArr;
    }
    
    private function enHighlight(&$dataArr)
    {
        $htfieldArr = $this->htfieldArr;
        $htkey  = empty($this->htkey)?$this->keys:$this->htkey;
        if(empty($htkey)) return null;
        $anaArr = $this->getTokens($htkey);
        $highlightArr = array();
        foreach($dataArr as $_id=>&$arr) {
            foreach($arr as $field=>$val) {
                if(!in_array($field, $htfieldArr)) continue;
                // $val = "我们都是从学校出来的";
                // $val  = self::mbSub($val, 96);
                $epos  = 0;
                $posArr = array();
                foreach($anaArr as $aArr){
                    $word = $aArr['token'];
                    // if($aArr['end_offset'] - $aArr['start_offset'] <= 1)continue;
                    $pos  = stripos($val, $word, $epos);
                    if(false !== $pos) {
                        // echo "$pos ($word)==\n";
                        $start = $pos;
                        $end   = $pos + strlen($word);
                        $ok    = true;
                        foreach($posArr as $fArr) {
                            $_s = $fArr['start'];
                            $_e = $fArr['end'];
                            if(($start>=$_s && $start<$_e) || ($end>=$_s && $end<$_e)) {
                                $ok = false;  break;
                            }
                        }
                        if($ok) {
                            $posArr[] = array('start'=>$start,'end'=>$end, 'word'=>$word);
                        }
                    }
                }
                $temp = '';
                $pos  = 0;
                // print_r($posArr);
                if(count($posArr) > 0) {
                    foreach($posArr as $pArr) {
                        $start = $pArr['start'];
                        $end   = $pArr['end'];
                        $word  = $pArr['word'];
                        $hword = '<b style="color:#f00">'.$word.'</b>';
                        $val  = str_ireplace($word, $hword, $val);
                    }
                    $highlightArr[$_id][$field] = $val;
                    // echo $val."\n";
                    // debug($highlightArr);
                }
            }
        // echo "=========================<br/>\n";
        }
        // print_r($highlightArr);
        // echo "=================================";
        return $highlightArr;
    }

    /**
    * 根据id获取一条数据
    */
    public function get($page=1, $size=20)
    {
        $page = ($page > 0) ? $page : 1;
        $from = ($page - 1) * $size;
        $whArr = $this->getFilters();
        $whArr['from'] = $from;
        $whArr['size'] = $size;
        $result = self::curlSend($this->realUrl, $whArr);
        print_r($result);
        $rstArr = $this->_parseGetDataResult($result['text'], $page, $size);
        $rstArr['page'] = $this->_addPage($rstArr['total'], $page, $size);
        return $rstArr;
    }
    /**
    * 根据id获取一条数据
    */
    public function getOne($id) {
        $url = $this->typeUrl . '/' . $id;
        $result = self::curlGet($url);
        $rstArr = json_decode($result, true);
        if(isset($rstArr['_source']) && is_array($rstArr['_source']) && count($rstArr['_source'])>0) {
            return $rstArr['_source'];
        }
        return array();
    }
    /**
    * 根据多个id获取多条数据
    *
    */
    public function getMore($idArr)
    {
        $url = $this->baseUrl .'/'. $this->indexName . '/_search';
        if(is_string($idArr)) {
            $idArr = explode(',', $idArr);
        }
        $paraArr = array('query'=> array('ids'=>array('type'=>$this->typeName, 'values'=>$idArr)));
        $result  = self::curlSend($url, $paraArr);
        //$rstArr = json_decode($result['text'], true);
        $rstArr  = $this->_parseGetDataResult($result['text']);
        return $rstArr;
    }
    /**
    * 批量添加数据
    PUT /_bulk HTTP/1.1
    Host: 192.168.1.93:9200
    Accept: *\/*
    Content-Length: 441
    Content-Type: application/x-www-form-urlencoded
    
    {"index":{"_index":"company","_type":"company","_id":1}}
    {"id":"1","type":"1","industry":"it","name":"A\u516c\u53f8","description":"\u6d4b\u8bd5","logo":"a","banner":"a","join_type":"0","userid":"11432","adminid":"11432","add_time":"2011-11-04","tag":"php","announcement":"1","member_count":"1","topic_count":"1","is_pass":"1","province_id":"1","province_name":"asdf","city_id":"1","city_name":"asdf","photo_count":"1","job_company_id":"1"}
    
    
    PUT /_bulk HTTP/1.1
    Host: 192.168.1.93:9200
    Accept: *\/*
    Content-Length: 970
    Content-Type: application/x-www-form-urlencoded
    
    {"index":{"_index":"group","_type":"group","_id":1}}
    {"id":"1","type":"1","name":"\u6d4b\u8bd5\u5708\u5b50","description":"\u70ed\u70c8\u795d\u8d3a\u5927\u6811\u7f51\u7684\u7b2c\u4e00\u4e2a\u6d4b\u8bd5\u5708\u5b50\u4e0a\u7ebf\u4e86,\u54c8\u54c8\u54c8\u54c8\u54c8..","logo":"a","banner":"a","join_type":"0","userid":"11432","adminid":"11432","add_time":"2011-11-12","tag":"\u79df\u623f;\u552e\u623f","announcement":null,"member_count":"123","topic_count":"485","is_pass":"1","province_id":"2","province_name":"\u5317\u4eac","city_id":"23","city_name":"\u5317\u4eac","photo_count":"23"}
    {"index":{"_index":"group","_type":"group","_id":2}}
    {"id":"2","type":"2","name":"asdf","description":"afsdf","logo":"a","banner":"a","join_type":"0","userid":"11432","adminid":"11432","add_time":"2011-11-12","tag":"aa","announcement":null,"member_count":"0","topic_count":"0","is_pass":"1","province_id":"0","province_name":null,"city_id":"0","city_name":null,"photo_count":null}
    
    *dataArr: array   --- array(id1=>array(...),id2=>array(...)....)
    */
    public function addMore($dataArr, $type=null)
    {
        $type = (null===$type)?$this->typeName:$type;
        if(empty($dataArr)) return false;
        $jsonAll = '';
        foreach($dataArr as $id=>$row) {
            $headoneArr = array('index'=>array('_index'=>$this->indexName, '_type'=>$type, '_id'=>$id)); //_id不设话es会自动生成
            $onejson  = json_encode($headoneArr)."\n".json_encode($row);
            $jsonAll .= $onejson."\n";
        }
        $bulkUrl = $this->baseUrl.'/_bulk';
        $result  = self::curlSend($bulkUrl, $jsonAll, array('method'=>'PUT'));
        if(!$result) return false;
        // print_r($result);
        if(200 == intval($result['http_code'])){
            $jArr = json_decode($result['text'], true);
            // print_r($jArr);
            if(isset($jArr['items'][0]['index']['ok']) && $jArr['items'][0]['index']['ok']) return true;
        }
        return false;
    }
    /*
    * desc: 添加一条数据
    *@row --- array
    PUT /xodoa/xodoa/15 HTTP/1.1
    Host: 192.168.1.93:9200
    Accept: *\/*
    Content-Length: 135
    Content-Type: application/x-www-form-urlencoded
    {"id":15,"uid":1006,"username":"\u7f8e\u5de5","email":"abc@163.com","category_cn":"\u7f51\u7ad9\u5f00\u53d1","trade_cn":"\u524d\u7aef"}
    */
    public function add($id, $row) {
        $url  = $this->typeUrl . '/'. $id;
        $result = self::curlSend($url, $row, array('method'=>'PUT'));
        // $this->_refresh();
        return $this->_parseAddDataResult($result['text']);
    }
    /**
    * 刷新索引
    POST /xodoa/_refresh HTTP/1.1
    Host: 192.168.1.93:9200
    Accept: *\/*
    Content-Length: 40
    Content-Type: application/x-www-form-urlencoded
    
    {"username":"hans","test":["2","3","5"]}
    */
    private function _refresh()
    {
        return true; //暂不启用此功能(因为看不出有可差异)
        $url  = $this->baseUrl . '/'. $this->indexName. '/_refresh';
        $result = self::curlSend($url);
        return $this->_parseAddDataResult($result['text']);
    }
    /*
    * desc: 根据id删除数据
    *
    */
    public function remove($id)
    {
        $delUrl = $this->typeUrl .'/'. $id;
        $result = $this->curlSend($delUrl, NULL, array('method'=>'DELETE'));
        return $this->_parseDeleteDataResult($result['text']);
    }
    /**
    * author: cty@20111230
    *   func: 根据query删除数据
    *@keys   --- str 关键词序列(,分隔)
    *@fields --- str 字段序列(,分隔)
    * curl -XDELETE 'http://localhost:9200/twitter/tweet/_query' -d '{
            "term" : { "user" : "kimchy" }
        }
        '
    */
    public function removes($keys=null, $fields='_all')
    {
        $delUrl  = $this->typeUrl .'/_query';
        $whArr   = array();
        $kArr    = explode(',', $keys);
        $fArr    = explode(',', $fields);
        $len     = count($kArr);
        for($i=0; $i<$len; $i++){
            $f = isset($fArr[$i])?$fArr[$i]:'_all';
            $whArr[$f] = $kArr[$i];
        }
        $paraArr = array('term' => $whArr);
        $result  = $this->curlSend($delUrl, $paraArr, array('method'=>'DELETE'));
        return $this->_parseDeleteDataResult($result['text']);
    }
    /*
    * desc: 更新数据
    *
    */
    public function update($id, $dataArr)
    {
        $oldArr = $this->getOne($id);
        $newArr = array_merge($oldArr, $dataArr);
        return $this->add($id, $newArr);
    }
    /*
    * desc: 批量更新
    *@limit --- int 一次更新的条数
    *@loops --- int 更新的次数(0不限制)
        curl -XPOST 'localhost:9200/test/type1/1/_update' -d '{
            "script" : "ctx._source.tags.contains(tag) ? ctx.op = "delete" : ctx.op = "none"",
            "params" : {
                "tag" : "blue"
            }
        }'
    */
    public function updates($dataArr, $limit=100, $loops=0)
    {
        $page   = 1;
        $limits = 0;
        $loop   = 0;
        while(true){
            if(isset($total) && $limits>=$total) return true;
            // echo "=======================$page\n";
            $rstArr  = $this->search(null, $page, $limit);
            $total   = $rstArr['total'];
            $oldArr  = $rstArr['data'];
            if(empty($oldArr) || $limits>$total) return true;
            $newArr  = array();
            foreach($dataArr as $id=>$row){
                if(isset($oldArr[$id])){
                    $new = array_merge($oldArr[$id], $row);
                    $newArr[$id] = $new;
                }
            }
            $ok = $this->addMore($newArr);
            if(!$ok) return false;
            $page++;
            $loop++;
            $limits += $limit;
            if($loops>0 && $loop>=$loops) break;
        }
        return true;
    }

    /**
    * 将get请求获取的结果解析。返回格式为array('total' => 总条数， 'data' => array(每条记录))
    */
    private function _parseGetDataResult($result)
    {
        $return = array();
        $result = json_decode($result, true);
        $return['total'] = 0;
        if(is_array($result) && isset($result['hits']['total'])) {
            $return['total'] = $result['hits']['total'];
        }
        $return['data'] = array();
        $htArr          = array();
        $facetsArr      = array();
        if(is_array($result)) {
            if(isset($result['hits']['hits']) && is_array($result['hits']['hits'])){
                $hitArr = $result['hits']['hits'];
                foreach ($hitArr as $key => $val) {
                    $_id = $val['_id']; //es的id
                    if(isset($val['_source']) && is_array($val['_source'])){
                        $temp = array();
                        foreach ($val['_source'] as $field => $value) {
                            if (is_string($value)) {
                            $temp[$field] = urldecode($value);
                            } else {
                            $temp[$field] = $value;
                            }
                        }
                        //$temp['id'] = $val['_id'];
                        $_id = $val['_id']; //es的id
                        // $return['data'][$_id] = $temp;
                        $return['data'][] = $temp;
                    }
                    //author:cty@20111117, highlight
                    if(isset($val['highlight']) && is_array($val['highlight']) && count($val['highlight'])>0) {
                        $oldhtArr = $val['highlight'];
                        $tArr = array();
                        foreach ($oldhtArr as $field => $fragArr) {
                            $tArr[$field] = implode('...', $fragArr);
                        }
                        $htArr[$_id] = $tArr;
                    }
                }
            }
            // print_r($result['facets']);
            if(isset($result['facets']) && is_array($result['facets'])){
                $_facet_arr = &$result['facets'];
                /*if($this->facetfild){
                    foreach($facetsArr as &$_facet_row){
                        $_facet_row[$this->facetfild] = $_facet_row['term'];
                    }
                }*/
                $facetsArr  = array();
                if(is_array($this->facetfields) && count($this->facetfields)){
                    foreach($this->facetfields as $field){
                        if(isset($_facet_arr[$field]['terms'])){
                            $facetsArr[$field] = $_facet_arr[$field]['terms'];
                        }
                    }
                }
                $return['facets'] = $facetsArr;
            }
        }
        $return['highlight'] = $htArr;
        $_hArr = $this->htfieldArr;
        if(1==$this->highType && is_array($_hArr) && count($_hArr)>0) {
            $return['highlight'] = $this->enHighlight($return['data']);
        }
        return $return;
    }
    
    private function _addPage($rssum = 0, $strpage = 0, $pageNumber = 20)
    {
        $pageView = $strpage;
    
        if (!is_numeric($pageNumber) || 1 > $pageNumber) {
            $pageNumber = 20;
        }
    
        $pageRssum = $rssum;
        //分页总页数  [记录总数/每页显示记录数]
        if ((int) ($pageRssum % $pageNumber) == 0) {
            if (($pageSum = (int) ($pageRssum / $pageNumber)) < 1) {
                $pageSum = 1;
            }
        }else{
            if (($pageSum = (int) ($pageRssum / $pageNumber) + 1) < 1) {
                $pageSum = 1;
            }
        }
    
        if ($pageSum > 100 && $pageSum < 1000) {
            $pageSpace = 6;
        } else if ($pageSum > 1000 && $pageSum < 10000) {
            $pageSpace = 4;
        } else if ($pageSum > 10000) {
            $page_space = 3;
        }
    
        //当前显示页 [当前页码 > 总页数=总页数]
        if ($pageView > $pageSum) {
            $pageView = $pageSum;
        }
        //当前显示页 [当前页码 < 1 = 1]
        if ($pageView < 1) {
            $pageView = 1;
        }
        //当只有一页时 [每页记录数=总记录数]
        if ($pageView == $pageSum && $pageView == 1) {
            $pageNumber = $pageRssum;
        }
    
        $pageSpace = empty($pageSpace) ? 6 : $pageSpace;
        $temp_range = (int)($pageSpace / 2);
        if ($temp_range * 2 != $pageSpace) {
            $temp_range--;
        }
        $pageAb = $pageView - $temp_range;
        $pageAe = $pageAb + $pageSpace - 1;
        //$pageAb 活动页码初始值
        if ($pageAb < 1) {
            $pageAb = 1;
            if (($pageAe = $pageAe - $pageAb +1) > $pageSum) {
                $pageAe = $pageSum;
            }
        }
        //$pr 活动页码最终值
        if ($pageAe > $pageSum) {
            $pageAe = $pageSum;
            if (($pageAb = $pageAb + $pageSum - $pageSum) < 1) {
                $pageAb = 1;
            }
        }
    
        $pagePrevNum = ($pageView > 1) ? $pageView - 1 : 1;
        $pageNextNum = ($pageView < $pageSum) ? $pageView + 1 : $pageSum;
        $pageFirstNum = 1;
        $pageLastNum = $pageRssum;
    
        return array(
            'pager' => array(
                'pagePrevNum' => $pagePrevNum,
                'pageFirstNum' => $pageFirstNum,
                'pageNextNum' => $pageNextNum,
                'pageLastNum' => $pageSum,
                'page' => $pageView,
                'pageAb' => $pageAb,
                'pageAe' => $pageAe,
            ),
        );
    }
    
    private function _parseAddDataResult($result){
        $result = json_decode($result, true);
        if(is_array($result) && isset($result['ok']) && $result){
            return true;
        }
        return false;
    }
    
    private function _parseDeleteDataResult($result) {
        return $this->_parseAddDataResult($result);
    }
    private function parseResult($result, $type=1)
    {
    
    }
    
    
    ///////////////////////////////////////////////////////////////////////////////////////
    /**
    *判断index是否存在
    *return: exist:true, not exist:false
    *
    */
    public function existedIndex()
    {
        $result = self::curlSend($this->indxUrl, NULL, array('method'=>'HEAD'));
        $code = intval($result['http_code']);
        if(404 == $code) {
            return false;
        }else {
            return true;
        }
    }
    /**
    *func: 删除索引
    *DELETE /group/ HTTP/1.1
        Host: 192.168.1.93:9200
        Accept: \*\/*
    */
    public function dropIndex()
    {
        $result = self::curlSend($this->dropUrl, NULL, array('method'=>'DELETE'));
        $rstArr = json_decode($result['text'], true);
        if(isset($rstArr['ok'])) {
            return true;
        }else if(isset($rstArr['error'])) {
            $result['error'] = $rstArr['error'];
            return false;
        }else {
            return false;
        }
    }
    /**
    * func: 创建索引
    *   eg: $es->createIndex
    *@isdrop  --- bool  是否删除原来的索引
    *@$shards --- int   分片数
    *@$reps   --- int   副本数
    *@mappArr --- array 字段类型
    *             'id'    => array('type'=>'long',   'inall'=>false, 'isindx'=>true),
    *             'age'   => array('type'=>'long',   'inall'=>false, 'isindx'=>true),
    *             'dtime' => array('type'=>'date',   'inall'=>false, 'isindx'=>true),
    *             'mark'  => array('type'=>'string', 'inall'=>true, 'isindx'=>'space'),
    *@typeArr --- array 索引下不同分支(type),默认和索引名相同
    */
    // public function createIndex($isdrop=true, $xArr, $mappArr=array())
    public function createIndex($isdrop=true, $xArr=array(), $mappArr=array(), $typeArr=array())
    {
        $indexName = $this->indexName;
        $isdrop && $this->dropIndex();
        
        $shards  = isset($xArr['shards'])?$xArr['shards']:2;
        $reps    = isset($xArr['reps'])?$xArr['reps']:0;
        $dynamic = isset($xArr['dynamic']) && false==$xArr['dynamic']?false:true; //是否动态mapping
        // $shards=2, $reps=0,
        
        $createUrl = $this->baseUrl.'/'.$indexName;
        // number_of_replicas:一般设置成主机数减1即可(如果是两台主机一般不用设置)
        $paraArr = array('index'=>array('number_of_shards'=>$shards, 'number_of_replicas'=>$reps));
        // $paraArr = array('index'=>array('number_of_shards'=>$shards, 'term_index_interval'=>32));
        // $paraArr = array('index'=>array('number_of_shards'=>$shards));
        // $paraArr['index']['store']['type'] = 'memory';
        // print_r($paraArr);
        $result = self::curlSend($createUrl, $paraArr, array('method'=>'PUT'));
        // print_r($result);
        $rstArr = json_decode($result['text'], true);
        // print_r($rstArr);
        if(isset($rstArr['ok'])){
            //设置字段类型
            if(is_array($typeArr) && count($typeArr)>0) {
                foreach($typeArr as $type) {
                    $ok = $this->setMapping($mappArr, $type, null, array('dynamic'=>$dynamic));
                }
            }else {
                $ok = $this->setMapping($mappArr, null, null, array('dynamic'=>$dynamic));
            }
            if($ok) {
                return true;
            }else {
                $this->dropIndex();
                return false;
            }
        }else if(isset($rstArr['error'])){
            $result['error'] = $rstArr['error'];
            return false;
        }else {
            return false;
        }
    }
    /**
    * author: cuity@20111104
    * func:   设置字段类型(创建索引时使用)
    *PUT /group/group/_mapping HTTP/1.1
        Host: 192.168.1.93:9200
        Accept: *\/*
        Content-Length: 231
        Content-Type: application/x-www-form-urlencoded
        {"group":{"properties":{"id":{"type":"long"},"userid":{"type":"integer"},"adminid":{"type":"integer"},"member_count":{"type":"integer"},"topic_count":{"type":"integer"},"photo_count":{"type":"integer"}},"_source":{"enabled":true}}}
    */
    public function setMapping($mappArr=null, $type=null, $index=null, $xArr=array())
    {
        $indexName = (null===$index)?$this->indexName:$index;
        $type      = (null===$type)?$this->typeName:$type;
        $analyzer  = isset($this->analyzer)?$this->analyzer:'ik';
        $dynamic   = isset($xArr['dynamic']) && false==$xArr['dynamic']?false:true;
        $mappUrl   = $this->baseUrl . '/'. $indexName . '/'.$type.'/_mapping';
        /* //old
        if(is_array($this->mappArr) && count($this->mappArr)>0) {
        $tArr = array();
        foreach($this->mappArr as $field=>$ftype) {
            $tArr[$field] = array('type'=>$ftype);
            if('string' == $ftype) {
            $tArr[$field]['store'] = 'yes';
            $tArr[$field]['term_vector'] = 'with_positions_offsets';
            }
        }
        $mappArr[$indexName]['properties'] = $tArr;
        $mappArr[$indexName]['_source']    = array('enabled'=>true);
        $mappArr[$indexName]['_all']    = array('term_vector'=>"with_positions_offsets","enabled" =>"true","index_analyzer" => "standard","search_analyzer" => "standard", "store"=>"yes");
        $mappArr[$indexName]['_type']    = array("store" => "yes");
    
        $mappArr[$indexName]['dynamic']  = true;
    
        // print_r($mappArr); 
    
        $result = self::curlSend($mappUrl, $mappArr, 'PUT');
        print_r($result);
        if(200 == intval($result['http_code'])) return true;
        }*/
        /*
        Array
        (
            [fulltext] => Array
                (
                    [properties] => Array
                        (
                            [content] => Array
                                (
                                    [analyzer] => ik
                                    [term_vector] => with_positions_offsets
                                    [type] => string
                                    [boost] => 10
                                )
                        )
                    [_all] => Array
                        (
                            [analyzer] => ik
                            [term_vector] => with_positions_offsets
                        )
    
                )
    
        )
        */
        $schemaArr = null===$mappArr?$this->mappArr:$mappArr;
        if(is_array($schemaArr) && count($schemaArr)>0){
            $mappArr = $tArr = array();
            foreach($schemaArr as $field=>$iArr){
                $ftype  = isset($iArr['type'])?$iArr['type']:'string';
                $inall  = isset($iArr['inall'])?$iArr['inall']:false;
                $isindx = isset($iArr['isindx'])?$iArr['isindx']:null;
                $tArr[$field]['type'] = $ftype;
                if('date' == $ftype) $tArr[$field]['format'] = 'YYYY-MM-dd HH:mm:ss';
                $tArr[$field]['analyzer'] = $analyzer;
                $tArr[$field]['include_in_all'] = $inall;
                isset($iArr['boost']) && $tArr[$field]['boost'] = $iArr['boost'];        
                // !$isindx && 'string'==$ftype && $tArr[$field]['index']='no';
                if(false === $isindx) {
                    $tArr[$field]['index']='no';
                }elseif('noana' === $isindx){
                    $tArr[$field]['index']='not_analyzed';
                }elseif('space' === $isindx){
                    $tArr[$field]['index']='not_analyzed';
                    $tArr[$field]['analyzer']='whitespace';
                }
                /*
                if('string' == $ftype && $isindx) {
                    $tArr[$field]['store'] = 'no'; //必须被索引
                    $tArr[$field]['term_vector'] = 'with_positions_offsets';
                }*/
            }

            $mappArr[$indexName]['properties'] = $tArr;
            $mappArr[$indexName]['_source']    = array('enabled'=>true);
            $mappArr[$indexName]['_all']       = array('term_vector'=>"no","enabled" =>"true","index_analyzer" => $analyzer,"search_analyzer" => $analyzer, "store"=>"no");
            $mappArr[$indexName]['_type']      = array("store" => "yes");
            $mappArr[$indexName]['dynamic']    = $dynamic;

            // print_r($mappArr); //exit;
            // $t2Arr[$indexName]['t1'] = $mappArr[$indexName];
            // $t2Arr[$indexName]['t2'] = $mappArr[$indexName];
            $result = self::curlSend($mappUrl, $mappArr, array('method'=>'PUT'));
            // print_r($result);
            if(200 == intval($result['http_code'])) return true;
        }
        return false;
    }
    /**
    *autor: cuity@20111206
    * func: 获取字段信息
    *
    */
    public function getMapping($index=null)
    {
        $indexName = (null===$index)?$this->indexName:$index;
        $mappUrl = $this->baseUrl."/_mapping";
        $restut  = self::curlGet($mappUrl);
        $mappArr = json_decode($restut, true);
        return $mappArr[$indexName];
    }
    /**
    *autor: cuity@20111206
    * func: 删除mapping(注意:删除mapping后数据也全部清空) 慎用
    *curl -XDELETE 'http://localhost:9200/demo/demo/_mapping'
    */
    public function dropMapping($index=null,$type=null)
    {
        $indexName = (null===$index)?$this->indexName:$index;
        $typeName  = (null===$type)?$this->typeName:$type;
        $mappUrl   = $this->baseUrl."/$indexName/$typeName/_mapping";
        $rstArr    = self::curlSend($mappUrl, null, array('method'=>'DELETE'));
        $code      = intval($rstArr['http_code']);
        return 200==$code?true:false;
    }
    // curl -XGET 'localhost:9200/demo/_analyze?analyzer=whitespace' -d 'this is a test'
    //获取分词情况
    function getTokens($str, $indexName=NULL)
    { 
        $indexName = (NULL==$indexName)?$this->indexName:$indexName;
        $analyzer  = $this->analyzer;
        $anaUrl = $this->baseUrl.'/'.$indexName.'/_analyze?analyzer='.$analyzer.'&text='.urlencode($str);
        $result = self::curlGet($anaUrl);
        $anaArr = json_decode($result, true);
        return $anaArr['tokens'];
    }
    /**
    * Author: cty@20120320
    *   func: 在查询字符串上添加额外的条件
    *@valArr  -- array|string
    *            array  --- 相当于mysql的in操作
    *                关联数组:操作符为AND
    *                索引数组:操作符为OR
    *            string --- field:valArr
    *@field   -- 字段
    *@optor   -- 操作符[OR]
    *@inoptor -- in里面的操作符[OR]
    */
    function appendkey($valArr, $field='_id', $optor='OR')
    {
        $qstring = '';
        $optor   = strtoupper($optor);
        $optor   = 'AND'==$optor?'AND':'OR';
        if(is_array($valArr) && count($valArr)>0) {
            foreach($valArr as $k=>$val) {
                if(0==strlen($val))continue;
                $_f = is_string($k)?$k:$field;
                $_o = is_string($k)?'AND':'OR';
                $qstring .= " $_o $_f:$val";
            }
            //去掉末端的操作符
            $qstring = ltrim(ltrim($qstring, ' OR '),' AND ');
            if(strlen($qstring) > 0) {
                $qstring = '('.$qstring.')';
                $qstring = " $optor ".$qstring;
            }
        }else if(is_string($valArr) && strlen($valArr)>0) {
            $qstring = " $optor ($field:$valArr)";
        }else {
            return '';
        }
        $this->appendedqstr .= $qstring;
        return $this;
    }
    /**
    * func: 修改索引(已经建好的索引不能修改sharding)
    *@$indexName str 索引名
    *@$reps      int 副本数
    */
    public function changeIndex($indexName, $reps=0)
    {
        if(!$indexName)return;
        $createUrl = $this->baseUrl.'/'.$indexName.'/_settings';
        /*
        "mappings" : {
            "type1" : {
                "_source" : { "enabled" : false },
                "properties" : {
                    "field1" : { "type" : "string", "index" : "not_analyzed" }
                }
            }
        }*/
        $paraArr = array('index'=>array('number_of_replicas'=>$reps),
            "mappings"=>array(
                "type1"=>array(
                "properties" =>array(
                        "userinfo_completed" => array("type" => "long")
                    )
                )
            )
        );
        // print_r($paraArr);
        $result = self::curlSend($createUrl, $paraArr, array('method'=>'PUT'));
        $rstArr = json_decode($result['text'], true);
        if(isset($rstArr['ok']) && $rstArr['ok']) return true;
        return false;
    }
    /**
    * func: 设置别名
    *@$indexName str 索引名
    *@$reps      str 别名
    curl -XPOST 'http://localhost:9200/_aliases' -d '
        {
            "actions" : [
                { "add" : { "index" : "test1", "alias" : "alias1" } }
            ]
        }'
    */
    public function setAlias($indexName, $alias)
    { 
        if(!$indexName)return;
        $aliasUrl = $this->baseUrl.'/_aliases';
        $paraArr = array('actions'=>array(
            array('add'=>array('index'=>$indexName, 'alias'=>$alias)))
        );
        // print_r($paraArr);
        $result = self::curlSend($aliasUrl, $paraArr, array('method'=>'POST'));
        // print_r($result);
        
        // curl -XGET 'localhost:9200/test/_aliases'
        $getAsUrl = $this->baseUrl.'/'.$indexName.'/_aliases';
        $rst = self::curlGet($getAsUrl);
        // print_r(json_decode($rst, true));
        
        $rstArr = json_decode($result['text'], true);
        if(isset($rstArr['ok']) && $rstArr['ok']) return true;
        return false;
    }
    /**
    * func: 打开/关闭索引
    *@$indexName str 索引名
    */
    public function openIndex($indexName=null, $action='_open')
    {
        null===$indexName && $indexName=$this->indexName;
        $createUrl = $this->baseUrl.'/'.$indexName.'/'.$action.'';
        $result = self::curlSend($createUrl, null, 'POST');
        $rstArr = json_decode($result['text'], true);
        if(isset($rstArr['ok']) && $rstArr['ok']) return true;
        return false;
    }
    /**
    *func: optimize index
    *
    *
    */
    public function optmIndex($indexName=null)
    {
        null===$indexName && $indexName=$this->indexName;
        $optmUrl = $this->baseUrl.'/'.$indexName.'/_optimize';
        $paraArr = array('max_num_segments'=>1, 'only_expunge_deletes'=>true);
        $result = self::curlSend($optmUrl, $paraArr, 'POST');
        // print_r($result);
        $rstArr = json_decode($result['text'], true);
        
        if(isset($rstArr['ok']) && $rstArr['ok']) return true;
        return false;
    }
    public function setMapArr($ftypeArr)
    {
        $this->mappArr = $ftypeArr;
    }
    /*
    * desc: 排序array('field'=>'asc|desc');
    *@sortArr --- array array('field'=>'asc|desc');
    */
    private function _set_sort($sortArr)
    {
        $this->sortArr = $sortArr;
        return $this;
    }
    /*
    *
    * array(array('rank'=>array('reverse'=>true)));
    *@sorts --- str eg. 'age desc, name asc'

    */
    public function st($sorts)
    {
        if(is_string($sorts)){
            $tArr = explode(',', $sorts);
            $sortArr = array();
            foreach($tArr as $so){
                list($field, $type) = explode(' ', trim($so));
                $reverse = 'desc' == $type ? true : false;
                $sortArr[] = array($field => array('reverse'=>$reverse));
            }
        }else{
            $sortArr = $sorts;
        }
        return $this->_set_sort($sortArr);
    }
    /**
    *author: cuity@20111117
    * 设置高亮字段
    *@fieldArr array 
    *  {
                "tags_schema" : "styled",
                "pre_tags" : ["<b style=\"color:#ff0000\">"],
                "post_tags" : ["</b>"],
                "fields" : {
                    "name" : {"fragment_size" :  512,"number_of_fragments" : 128 },
                    "description" : {"fragment_size" :  512,"number_of_fragments" : 128 }
                }
            }
    */
    private function setHighArr($fieldArr)
    {
        if(1 == $this->highType){
            $this->htfieldArr = $fieldArr;
        }else {
            $highArr = array();
            if(is_array($fieldArr) && count($fieldArr)>0) {
                foreach($fieldArr as $field) {
                    $highArr['fields'][$field] = array('fragment_size'=>256,'number_of_fragments'=>3);
                }
                // $highArr['tags_schema'] = 'styld';
                $highArr['pre_tags']   = array('<b style="color:#ff0000">');
                $highArr['post_tags']  = array('</b>');
                // $highArr['order']   = 'score';
                // $highArr['highlight_filter']   = true;
                $this->highlightArr = $highArr;
                return true;
            }else {
                return false;
            }
        }
    }
    public function ht($fields, $key=null)
    {
        $this->htkey = $key;
        $fieldArr = explode(',', $fields);
        $this->setHighArr($fieldArr);
        return $this;
    }
    
    //////////////////////////////////////////////////////////////////////////////
    static function getClusterHealth()
    {
        $ies =  new CESearch('');
        $clhUrl = $ies->baseUrl."/_cluster/health?level=shards";
        $result = self::curlGet($clhUrl);
        $hthArr = json_decode($result, true);
        return $hthArr;
    }
    static function getClusterStatus()
    {
        $ies =  new CESearch('');
        $clhUrl = $ies->baseUrl."/_cluster/state?filter_metadata=true&filter_routing_table=true&filter_blocks=true";
        $result = self::curlGet($clhUrl);
        $hthArr = json_decode($result, true);
        return $hthArr;
    }
    
    
    /////////////////////////////////////////////////////////////////////////////
    /**
    * desc: send http request by curl 
    * @param string   -- $url target url
    * @param array    -- $postArr post paramters key-val pairs
    * @upArr arr|null -- array('key'=>本地文件路径),es中上传文件基本不用,所以置于最末端
    * return: array, info
    */
    static function curlSend($url, $postArr=null, $extArr=array(), $upArr=null)
    {
        // if(is_array($extArr)) {
        $method = isset($extArr['method'])?$extArr['method']:'POST';
        $format = isset($extArr['format'])?$extArr['format']:'json'; //body格式(如些需要json格式),在es中默认为json格式
        // }else {
        // $method = $extArr; //extArr如果是字符串则表示http方法
        // $format = 'json';
        // }
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        // curl_setopt($ch, CURLOPT_PORT, $port);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);//是否将头信息作为数据输出(HEADER信息)  
    
        $methodArr = array('POST'=>1, 'PUT'=>1, 'DELETE'=>1);
        $postbodys = '';
        if(is_array($upArr) && count($upArr)>0) { //文件上传
            foreach($upArr as $key => $file){
                $postArr[$key] = '@' . $file;
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postArr);
            $headArr[] = 'Expect: ';
        }else {
            if(is_array($postArr) && count($postArr)>0) {
                if('json' == $format) {
                    $postbodys = json_encode($postArr);
                }else {
                    $postbodys = http_build_query($postArr);
                }
            }else if(is_string($postArr)) {
                $postbodys = $postArr;
            }
            if(isset($methodArr[$method]) && !empty($postbodys)){
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postbodys);
            }
        }
        // echo '<pre>';
        // print_r($postArr);
        // print_r($postbodys);
        // echo '</pre>';
        if(isset($headArr) && is_array($headArr)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headArr);
        }
    
        for($i=1; $i<=3; $i++) {
            $result = curl_exec($ch);
            if(false === $result) {
                // echo $postbodys."\n";
                // echo 'curlerror:'.curl_error($ch)."[$i]\n";
                // trigger_error(curl_error($ch));
                usleep(200000 * $i);
                continue;
            }
            break;
        }
        if(false === $result) return false;
        // print_r($result);
        $repArr = curl_getinfo($ch);
        curl_close($ch); 
        if(200 != $repArr) {
            $repArr['status'] = 0;
        }else {
            $repArr['status'] = 1;
        }
        $repArr['text'] = $result;
        // print_r($repArr);
        return $repArr; 
    }
    /**
    * curl get请求
    *@url     string --- 请求uri
    *@paraArr array  --- 附加参数
    *         [ get     array --- url参数
    *           headers array --- 请求头数组,
    *           proxy   array --- 代理信息数组,
    *           timeout int   --- 超时时间(s)
    *           ishead  bool  --- 是否将头文件的信息作为数据流输出[false]
    *           &repArr array --- 转储应答信息
    *           loops   int   --- 连接出错时，重复连接的次数
    *         ]
    */
    function curlGet($url, $paraArr=array()) 
    {
        $get  = isset($paraArr['get'])?$paraArr['get']:null;
        $para = is_array($get)&&count($get)>0 ? http_build_query($get): '';
        if(strlen($para) > 0) {
            $url .= (strpos($url, '?') === FALSE ? '?' : '&'). $para;
        }
        $timeOut = isset($paraArr['timeout'])?$paraArr['timeout']:5;
        $ishead  = isset($paraArr['ishead'])?$paraArr['ishead']:false;
        $defaults = array( 
            CURLOPT_URL => $url, 
            CURLOPT_HEADER => $ishead, //是否将头信息作为数据流输出(HEADER信息)
            CURLOPT_RETURNTRANSFER => TRUE, 
            CURLOPT_TIMEOUT => $timeOut
        );
        $headers = array(
            'Mozilla/5.0 (Windows NT 6.1; rv:10.0) Gecko/20100101 Firefox/10.0', 
            'Accept-Language: zh-cn,zh;q=0.5',
            'Accept: */*',
            'Connection: keep-alive',
            'Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7', 
            'Cache-Control: max-age=0', 
        );
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);  
        if(isset($paraArr['headers']) && is_array($paraArr['headers']) && count($paraArr['headers'])>0){
            $headers = $headers + $paraArr['headers'];
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if(isset($paraArr['proxy']) && is_array($paraArr['proxy']) && count($paraArr['proxy'])>0){
            $pxyArr = $paraArr['proxy'];
            if(isset($pxyArr['pxyHost'])){
                $pxyPort = isset($pxyArr['pxyPort'])?$pxyArr['pxyPort']:80;
                $pxyType = (isset($pxyArr['pxyType'])&&'SOCKS5'==$pxyArr['pxyType'])?CURLPROXY_SOCKS5:CURLPROXY_HTTP; //(CURLPROXY_SOCKS5)
                curl_setopt($ch, CURLOPT_PROXYTYPE, $pxyType);  
                curl_setopt($ch, CURLOPT_PROXY,     $pxyArr['pxyHost']);
                curl_setopt($ch, CURLOPT_PROXYPORT, $pxyPort);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);    //启用时会将服务器服务器返回的“Location:”放在header中递归的返回给服务器
                //授权信息
                if(isset($pxyArr['auth']) && is_array($pxyArr['auth'])){
                    $authArr = $pxyArr['auth'];
                    if(isset($authArr['user']) && is_array($authArr['pswd'])){
                        $user = $authArr['user'];
                        $pswd = $authArr['pswd'];
                        $proxyAuthType = CURLAUTH_BASIC; //(CURLAUTH_NTLM)
                        curl_setopt($ch, CURLOPT_PROXYAUTH, $proxyAuthType);  
                        $authinfo = "[{$user}]:[{$pswd}]";
                        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $authinfo);
                    }
                }
            }
        }
        $loops = isset($paraArr['loops'])?$paraArr['loops']:5;
        for($i=1; $i<=$loops; $i++){
            $result = curl_exec($ch);
            if(false === $result) { usleep(500000 * $i); continue; }
            break;
        }
        
        if(!$result = curl_exec($ch)){
            trigger_error(curl_error($ch));
        }
        if(isset($paraArr['repArr'])){
            $paraArr['repArr'] = curl_getinfo($ch);
        }
        curl_close($ch);
        return $result; 
    }
    /**
    * author:cty@20111130
    * 获取当前毫秒级的时间
    * 用法:
    * $time_before = getUTime();
    *    中间代码...
    * $time_after = getUTime();
    * $Elapse = sprintf("%.4f", $time_after - $time_before);
    *
    */
    static function getUTime()
    {
        list($usec, $sec) = explode(' ',microtime());
        $time   = ((float)$usec + (float)$sec);
        return $time;  
    }
   
};

/**
* desc: es过滤器相关方法,
*       把过滤器抽象成一个类是为了实现链式操作
*/
abstract class _CFilter {
    protected $tempArr   = array(); //临时用
    protected $filterArr = array();
    protected $connector = 'and';   //默认的连接符([and]|or|not)
    private   $wasMerged = false;   //标识 getFilters 方法是否已调用过
    /*
    Array
    (
        [filter] => Array
            (
                [or] => Array
                    (
                        [0] => Array
                            (
                                [term] => Array
                                    (
                                        [name] => Jak
                                    )
                            )
                        [1] => Array
                            (
                                [term] => Array
                                    (
                                        [age] => 2010
                                    )

                            )

                    )

            )

    )*/
    /*
    * desc: 过滤器开始
    *@$op --- str([and]|or|not)
    */
    public function ft($connector='and')
    {
        $this->connector = $connector;
        $this->tempArr   = array();
        $this->filterArr = array();
        return $this;
    }
    /*
    * author: cuity@20111103
    * 设置id过滤器(相当于mysql中的in操作,但它只适于_id字段)
    * in查询实际为terms查询
    * @param arr $valArr --- array(1,2...)
    */
    function in($valArr, $field='ids')
    {
        // $this->tempArr[] = $valArr;
        if('ids' == $field){
            $this->tempArr[] = array('ids' => array('values'=>$valArr));
        }else{
            /*
            {
                "terms" : {
                    "tags" : [ "blue", "pill" ],
                    "minimum_should_match" : 1
                }
            }*/
            $this->tempArr[] = array('terms' => array($field=>$valArr));
        }
        return $this;
    }

    /*
    * author: cuity@20120322
    * 设置id过滤器(相当于mysql中的not in操作,同样但它只适于_id字段)
    * @param arr $idoutArr --- array(1,2...)
    */
    function nin($valArr, $field='ids') 
    {
        // $this->idoutArr = $valArr;
        if('ids' == $field){
            $this->tempArr[]['not'] = array('ids' => array('values'=>$valArr));
        }else{
            /*
                    {
                "constant_score" : {
                    "filter" : {
                        "terms" : { "user" : ["kimchy", "elasticsearch"]}
                    }
                }
            }*/
            $this->tempArr[]['not'] = array('terms' => array($field=>$valArr));
        }
        return $this;
    }
    /*
    * desc: 设置范围过滤(相当于mysql中的between)
    * @param str $field
    * @param int $from
    * @param int $to
    * $whArr['filter']['and'][] = array('range' => array('age'=>array('from'=> 2001, 'to'=>2010)));
    */
    public function bt($field, $from, $to=null)
    {
        if(null==$from && null==$to) return false;
        $rArr = array();
        null !== $from && $rArr['from'] = $from;
        null !== $to && $rArr['to']   = $to;
        $this->tempArr[]['range']   = array($field => $rArr);
        return $this;
    }
    /*
    * desc: 设置过滤器(相当于mysql中的=操作)
    * @param str $field --- 字段
    * @param str $val ----- 值 ftAnd
    */
    public function eq($f,$v)
    {
        $this->tempArr[]['term'] = array($f => $v);
        return $this;
    }
    /*
    *author: cty@20120117
    *  func: 设置dist过滤器(rad半径)
    *  desc: 设置距离某一点小于等于$dist公司的范围
    * @dist: 距离(km)
    * @$llArr: 某一点的坐标(纬度在前,经度在后)
        "filter" : {
                "geo_distance" : {
                    "distance" : "200km",
                    "pin.location" : {
                        "lat" : 40,
                        "lon" : -70
                    }
                }
            }
    
    */ 
    public function GEOrad($field, $llArr, $radius=5)
    {
        if(null==$field ||null===$llArr)return;
        $this->tempArr[] = array(
            'geo_distance' => array(
                'distance' => $radius,
                $field     => $llArr
            )
        );
        return $this;
    }
    /*
    *author: cty@20120117
    *  func: 矩形框过滤
    *        $llTLArr(左上角), $llBRArr(右下角)(纬度在前,经度在后)
    *@field: 字段名
    "filter" : {
                "geo_bounding_box" : {
                    "pin.location" : {
                        "top_left" : [40.73, -74.1],
                        "bottom_right" : [40.717, -73.99]
                    }
                }
            }
    
    */
    public function GEObox($field, $llTLArr, $llBRArr)// $lon1,$lat1, $lon2,$lat2
    {
        if(null==$field ||null===$llTLArr ||null===$llBRArr)return;
        $this->tempArr[] = array(
            'geo_bounding_box' => array(
                $field=>array('top_left'=>$llTLArr, 'bottom_right'=>$llBRArr)
            )
        );
        return $this;
    }
    /*
    * desc: 将tempArr听条件信息打包(归并)成一种过滤器作为filterArr中的一项过滤器
    *
    */
    public function gp($type='or')
    {
        $this->filterArr[]['or'] = $this->tempArr;
        $this->tempArr = array();
        return $this;
    }
    /*
    *
    *@connector --- str(and|or|not)
    *return array
    */
    public function getFilters($connector=null)
    {
        if($this->wasMerged){
            return $this->filterArr;
        }else{
            $connector = $connector?$connector:$this->connector;
            if(count($this->tempArr) > 0 || count($this->filterArr) > 0){
                $this->filterArr = array_merge($this->filterArr, $this->tempArr);
                $tArr = array($connector => $this->filterArr);
                $this->wasMerged = true;
                return $this->filterArr = array('filter'=> $tArr);
            }
            return array();
        }
    }
    function __destruct()
    {
        // print_r($this->filterArr); //测试
    }
};