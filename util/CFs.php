<?php
/**
 * author: 崔廷勇@20130712
 *   desc: 文件系统管理
 *
 *
*/
class CFs {

    public function writeLogs($content, $path='/tmp/l.log', $mod='a')
    {
        $content = date("Y-m-d H:i:s ") . $content . "\n";
        return $this->writeFile($path, $content, $mod);
    }
    public function writeFile($path, $content=null, $mod='w')
    {
        $fp = fopen($path, $mod);
        if(!$fp) return false;
        $len = fwrite($fp, $content);
        if(false === $len) return false;
        fclose($fp);
        return true;
    }
    
    public function getDirInfo($dir, $ignoreArr=array()) 
    {
        if(!is_dir($dir)) return;
        $subDirs = array();
        $handler = opendir($dir);
        while(false !== ($filename = readdir($handler)))
        {
            if($filename != '.' && $filename != '..' && $filename != 'System Volume Information') {
                $fullpath_gbk = rtrim($dir,'/').'/'.$filename;
                $fullpath = iconv('gbk', 'utf-8', $fullpath_gbk);
                // if('.' != substr($filename, 0, 1)) //.开头的不要
                $info = $this->getFInfo($fullpath_gbk);
                if(in_array(basename($fullpath_gbk), $ignoreArr)){
                    continue;
                }
                $info['path'] = $fullpath;
                if(is_dir($fullpath_gbk)){
                    $info['type'] = 'd';
                    $info['ext']  = null;
                }else{
                    $info['type'] = 'f';
                    $info['ext']  = substr($fullpath_gbk, strrpos($fullpath_gbk,'.')+1, 10);
                }
                $subDirs[] = $info;
            }
        }
        closedir($handler);
        return $subDirs;
    }
    
    public function walkdir($dir, &$subDirs=array(), $depth=1) 
    {
        if(!is_dir($dir)) return;
        $subDirs[] = $dir;
        $handler = opendir($dir);
        while(false !== ($filename = readdir($handler)))
        {
            if($filename != '.' && $filename != '..' && $filename != 'System Volume Information') {
                $fullpath  = rtrim($dir,'/').'/'.$filename;
                if('.' != substr($filename, 0, 1)) //.开头的不要
                    $subDirs[] = $fullpath;
                if(is_dir($fullpath)) {
                    if($depth>1) $this->walkdir($fullpath, $subDirs);
                }
            }
        }
        closedir($handler);
        return;
    }
        
    protected function makeDirs($dir, $isrevers=false)
    {
        if(is_dir($dir))return 409;
        $ndir = "";
        foreach(explode("/", $dir) as $pdir) {
            $ndir .= "$pdir/";
            if(!is_dir($ndir)) {
                if(!mkdir($ndir))return false;
            }
        }
    }
    /*
    * desc: 获取文件权限
    *
    */
    public function getFPerms($path)
    {
        $permArr = array();
        if(!file_exists($path)) return $permArr;
        $perms = fileperms($path);
        if (($perms & 0xC000) == 0xC000) {
            // Socket
            $info = 's';
        } elseif (($perms & 0xA000) == 0xA000) {
            // Symbolic Link
            $info = 'l';
        } elseif (($perms & 0x8000) == 0x8000) {
            // Regular
            $info = '-';
        } elseif (($perms & 0x6000) == 0x6000) {
            // Block special
            $info = 'b';
        } elseif (($perms & 0x4000) == 0x4000) {
            // Directory
            $info = 'd';
        } elseif (($perms & 0x2000) == 0x2000) {
            // Character special
            $info = 'c';
        } elseif (($perms & 0x1000) == 0x1000) {
            // FIFO pipe
            $info = 'p';
        }else {
            // Unknown
            $info = 'u';
        }
        $permArr['type'] = $info;

        // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
                    (($perms & 0x0800) ? 's' : 'x' ) :
                    (($perms & 0x0800) ? 'S' : '-'));
        // Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
                    (($perms & 0x0400) ? 's' : 'x' ) :
                    (($perms & 0x0400) ? 'S' : '-'));
        // World
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
                    (($perms & 0x0200) ? 't' : 'x' ) :
                    (($perms & 0x0200) ? 'T' : '-'));
        return $info;
    }
    public function getFInfo($path)
    {
        if(!file_exists($path)) return false;
        $ifArr = stat($path);
        $ifArr['atime2'] = date('Y-m-d H:i:s', $ifArr['atime']);
        $ifArr['mtime2'] = date('Y-m-d H:i:s', $ifArr['mtime']);
        $ifArr['ctime2'] = date('Y-m-d H:i:s', $ifArr['ctime']);

        $ifArr['write']  = is_writable($path);
        $ifArr['read']   = is_readable($path);
        $ifArr['perms']  = $this->getFPerms($path);
        
        $ifArr = array_merge($ifArr, pathinfo($path));
        return $ifArr;
    }
    public function getFile($path)
    {
        if(!is_file($path))return null;
        $fp = fopen($path, "r");
        if(!$fp) return false;
        $content = '';
        while($data = fread($fp, 1024)){
            $content .= $data;
        }
        fclose($fp);
        return $content;
    }
    public function head($path, $len=128)
    {
        if(!is_file($path))return null;
        $fp = fopen($path, "r");
        if(!$fp) return false;
        $info =fread($fp, $len);
        fclose($fp);
        return $info;
    }
    public function mvFile($src, $dest, $overwrite='T')
    {
        if(!file_exists($src)) return false;
        if(is_file($src)){
            $writable = true;
            if(file_exists($dest) && 'T'!=$overwrite) $writable = false;
            if($writable){
                if(file_exists($dest) && !is_writable($dest)){
                    chmod($dest, 0644);
                }
                for($t=1; $t<=5; $t++){
                    if($ok=copy($src, $dest)) break;
                    usleep(100000);//0.1s
                }
                if($ok){
                    @unlink($src);
                    return true;
                }
            }
        }elseif(is_dir($src)){
            //暂未实现
        }
        return false;
    }
    
};
