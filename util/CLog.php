<?php
/**
* author: cty@20131101
*   desc: 写日志 
*   
*/

class CLog {
    
    static $dir =  "/var/log";
    
    static function GetLogFileName($basename) {
        return realpath(dirname(dirname(__FILE__)))."/logs/".$basename.date("Ymd").'.log';
    }
    
    static function WriteFile($filename, $logs, $mod='a+')
    {
        $fp = fopen($filename, $mod);
        if(!$fp)return false;
        fputs($fp, $logs);
        fclose($fp);
    }
    /**
     * $basename一般为空
     * @param type $logs
     * @param type $basename
     * @param type $mod
     */
    static function WriteLog($logs, $basename, $mod="a+")
    {
        if(CFun::isWindows()){
            $filename = self::GetLogFileName($basename);
        }else{
//            $dir = self::$dir;
            $dir = dirname(dirname(__FILE__)).'/logs';
            $filename = $dir.'/'.$basename.date("Ymd").'.log';
        }
        
        $time = date("Y-m-d.H:i:s");
        ob_start();
        echo ">>>>>>>>>>>>>>>>>>>>({$time})\r\n";
        print_r($logs);
        echo "\r\n";
        echo "<<<<<<<<<<<<<<<<<<<<({$time})\r\n";
        echo "\r\n";
        $logconent = ob_get_clean();

        self::WriteFile($filename, $logconent, $mod);
    }
    static function WriteHttpLog($logs='', $basename='http-req')
    {
        self::WriteLog(array($logs,$_POST,$_GET), $basename);
    }
    static function WriteError($logs, $basename='error', $mod='a')
    {
        self::WriteLog($logs, $basename, $mod);
    }
};
