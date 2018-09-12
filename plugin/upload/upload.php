<?php
class upload {

    private $maxsize = 50000000;//50M

    private $errors  = array(
        1 => '超过php.ini允许的大小',
        2 => '超过表单允许的大小',
        3 => '图片只有部分被上传',
        4 => '请选择图片',
        6 => '找不到临时目录',
        7 => '写文件到硬盘出错',
        8 => '不支持的后辍',
    );

    function __construct($uppath){
    	$this->updir=$uppath;

    }
    /**
    * desc: 文件上传(图片)
    * 文件命名规则:年(4).月(2).日(2).唯一ID(12)，年.月.日又是目录名(8位),共20位
    *
    *
    *
    */
    public function up($keyname='Filedata')
    {

    	// echo '<pre>';
    	// print_r($_FILES);die;

        $uploadLoc = $this->updir;//上传到服务器的位置

        $uploadUrl = '/static/upload';//返回的路径名

        //文件保存目录路径
        $save_root  = $uploadLoc. '/';
        //文件保存目录URL
        $url_prefix = $uploadUrl. '/';
        //定义允许上传的文件扩展名
        $ext_arr = array(
            'image'  => array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'ico'),
            'media'  => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
            'attach' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2','pdf','mov','dat','rmvb','mp3','mp4','apk','bundle'),
        );
        if(!function_exists('_trim_exts')){
            function _trim_exts($ext_arr){
                $tarr = array();
                foreach($ext_arr as $d=>$exts){
                    $tarr = array_merge($tarr, array_fill_keys($exts,$d));
                }
                return $tarr;

            }
        }
        //最大文件大小
        $max_size  = $this->maxsize;
        $save_root = realpath($save_root) . '/';
        if(@is_dir($save_root) === false){ //检查目录
        	return array(
        		'status'=>-1,
        		'error'=>'上传目录不存在'
        		);
        }
        if(@is_writable($save_root) === false) {//检查目录写权限
        	return array(
        		'status'=>-1,
        		'error'=>'上传目录没有写权限'
        		);
        }
        if(!isset($_FILES["$keyname"])){
        	return array(
        		'status'=>-1,
        		'error'=>'文件key不存在'
        		);
        }

        //创建目录的年份===============================
        $year  = date("Y");
        $month = date("m");
        $day   = date("d");
        $ext_brr = _trim_exts($ext_arr);
        //创建目录的年份============================end

        //整理数据=====================================
        //willArr为将要上传的文件二维数组
        // if(is_array($_FILES["$keyname"])){
            // $willArr = CUtil::formArrayFormatting($_FILES["$keyname"]);
        // }else{
            $willArr = array($_FILES["$keyname"]);
        // }

        // var_dump($willArr);die;
 
        //整理数据==================================end
        
        //循环复制文件=================================
        $resultArr = array(); //上传的结果
        foreach($willArr as $will){
            $errid = intval($will['error']);
            if($errid){
                $resultArr[] = array(
                	'status'=>-1,
                    'error' => isset($this->errors[$errid])?$this->errors[$errid]:'未知错误',
                    'url'   => '',
                );
                continue;
            }
            $file_name = $will['name']; //原文件名
            // var_dump($file_name);exit;
            $tmp_name  = $will['tmp_name']; //服务器上临时文件名

            $file_size = $will['size']; //文件大小
            if(!$file_name){//检查文件名
                $resultArr[] = array('status'=>-1,'error'=>'请选择文件','url'=>'','name'=>$file_name);
                continue;
            }
            if(@is_uploaded_file($tmp_name) === false) {//检查是否已上传
                $resultArr[] = array('status'=>-1,'error'=>'上传失败','url'=>'');
                continue;
            }
            if($file_size > $max_size){ //检查文件大小
                $resultArr[] = array('status'=>-1,'error'=>'上传文件大小超过限制','url'=>'','name'=>$file_name);
                continue;
            }
            //检查目录名
            //获得文件扩展名
            $file_ext = strtolower(substr($file_name,strrpos($file_name,'.')+1,10));
            if(isset($ext_arr['image']))
            $dir_name = isset($ext_brr[$file_ext])?$ext_brr[$file_ext]:'attach';
            $save_url  =  $url_prefix . "{$dir_name}/$year/$month/$day/";
            $save_path =  $save_root  . "{$dir_name}/$year/$month/$day/";
            //检查扩展名
            if(!isset($ext_brr[$file_ext])) {
                $resultArr[] = array('status'=>-1,'error'=>'不允许的文件格式','url'=>'','name'=>$file_name);
                continue;
            }
            $this->mkDir($save_path);
            
            //新文件名
            if(isset($_GET['fixed'])){
                $fixed = strlen($_GET['fixed'])>1?$_GET['fixed']:'tmp';
                $new_file_name = $fixed . '.' . $file_ext;
            }else{
                $new_file_name = CFun::crcU32($tmp_name) . '.' . $file_ext;
            }
            $file_path = $save_path . $new_file_name;
            if(false === move_uploaded_file($tmp_name, $file_path)){//移动文件

                // var_dump($tmp_name);var_dump($file_path);die;
                $resultArr[] = array('status'=>-1,'error'=>'上传文件失败','url'=>'','name'=>$file_name);
                continue;
            }
            
            //是否裁剪
            $cut = isset($_GET['x'])?$_GET['x']:(isset($_POST['x'])?$_POST['x']:null);
            if($cut && strpos($cut, 'x') && 'image'==$dir_name){
                if(strpos($cut, 'x')){
                    $cutArr = explode('i', $cut);
                    foreach($cutArr as $_cut){
                        if(strpos($cut, 'x')){
                            list($cut_w, $cut_h) = explode('x', $_cut);
                            $file_cuted = UImg::cutImg($file_path, $cut_w, $cut_h);
                        }
                    }
                }else{
                    list($cut_w, $cut_h) = explode('x', $cut);
                    $file_cuted = UImg::cutImg($file_path, $cut_w, $cut_h);
                }
            }
            if(isset($file_cuted) && $file_cuted){
                $file_url = $save_url . basename($file_cuted);
            }else{
                @chmod($file_path, 0644);
                $file_url = $save_url . $new_file_name;
            }
            $resultArr[] = array('status'=>1,'error'=>'上传成功','url'=>$file_url,'name'=>$file_name);
        }
        //循环复制文件==============================end

        //输出结果=====================================
        // print_r($resultArr);
        // $urlArr = $this->getArrayColumn($resultArr, 'url');
        // CFun::removeArrayNull($urlArr,true,true);
        return $resultArr[0];
        // $this->response($resultArr);
        //输出结果==================================end
    }
    public function mkDir($dir, $mode=0755)
    {
        $dir = rtrim(str_replace('\\', '/', $dir), '/');
        $dirs = explode('/', $dir);
        for($i=0,$len=count($dirs); $i<$len; $i++) {
            $tdir = implode('/', array_slice($dirs, 0, $i+1));
            // echo $tdir."\n";
            if (is_dir($tdir)) {
                continue;
            }
            @mkdir($tdir, $mode, true);
        }
    }

};
