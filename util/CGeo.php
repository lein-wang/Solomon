<?php
/**
 * desc: geo相关
 *
 *
 *
*/

class CGeo{
    
    /*
    * desc: 获取两经纬度之间的距离
    *
    */
    static function getDist($lon1,$lat1,$lon2,$lat2)
    {
        $PI = pi();
        $R  = 6.3781e6;
        $x  = ($lon2-$lon1)*$PI*$R*cos( (($lat1+$lat2)/2) *$PI/180)/180;
        $y  = ($lat2-$lat1)*$PI*$R/180;
        $d  = hypot($x,$y)/1000;
        return $d; 
    }
    /*
    * desc: x,y-> lon,lat
    *
    */
    function xy2ll($x,$y)
    {
        $PI  = pi();
        $lon = $x / 93206.7556; //pow(2,17) / 256 * 360;
        $lat = (atan(exp( $y/pow(2, 17)/256 *$PI* 2))*2-$PI/2)*180/$PI;
        return array($lon, $lat);
    }
    /*
    * desc: lon,lat -> x,y
    *
    */
    function ll2xy($lon, $lat)
    {
        $PI = pi();
        $x  = round($lon/360*256 * pow(2,17));
        $y  = round(log(tan(($lat*$PI/180+$PI/2)/2))*256/$PI/2 * pow(2,17)); 
        return array($x, $y);
    }
}