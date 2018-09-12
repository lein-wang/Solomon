<?php
/**
 * 时间相关
 *
 *
 */

class CTime {

    //指定日期的那一月的第一天
    static function monFirstDay($dtime=null)
    {
        return date("Y-m-01", $dtime?strtotime($dtime):time());
    }
    //计算距离当前n月时间(nm可为负)
    static function monDiff($nm=0, $delta=1)
    {
        $nm = intval($nm);
        $mon_last_first = date('Y-m-01', strtotime($nm.' month'));
        $mon_last_end   = date('Y-m-01', strtotime($delta.' month',strtotime($mon_last_first)));
        return array($mon_last_first, $mon_last_end);
    }
    //指定日期的下一天
    static function monPrevDay($dtime=null)
    {
        return self::dayDiff($dtime, -1);
    }
    //指定日期的上一天
    static function monNextDay($dtime=null)
    {
        return self::dayDiff($dtime, 1);
    }

    //计算距离当前n周时间(nm可为负)
    static function weekDiff($nw=0)
    {
        $nw = (string)(intval($nw));
        $wd = date('w');
        $week_last_first = date('Y-m-d', time()-$wd*86400 + $nw*604800);//strtotime($nw.' week sunday')
        $week_last_end   = date('Y-m-d', strtotime($week_last_first)+604800);
        return array($week_last_first, $week_last_end);
    }

    //指定日期的距离n天
    static function dayDiff($dtime=null, $nd=0)
    {
        return date('Y-m-d', $dtime?strtotime("{$nd} day",strtotime($dtime)):strtotime("{$nd} day"));
    }
    //指定日期的下一天
    static function dayNext($dtime=null)
    {
        return self::dayDiff($dtime, -1);
    }
    //指定日期的上一天
    static function dayPrev($dtime=null)
    {
        return self::dayDiff($dtime, 1);
    }
    /*
    * desc: 获取当月的最后一天
    *       2013-04-14 ==> 2012-03-[29-31]
    *
    */
    static function getMonthLast()
    {
        $days = date('t');
        return date("Y-m-".$days);
    }

    /*
    * desc: 获取明天日期
    *
    *
    */
    static function getTomorrow()
    {
        $stime = strtotime(date("Y-m-d 23:59:59")) + 2;
        return date("Y-m-d", $stime);
    }

    /*
    * desc: 获取上月的第一天
    *       2013-04-14 ==> 2012-03-01
    *
    */
    static function getLastMonthFirst()
    {
        $stime = strtotime(date("Y-m-01 00:00:00")) - 2;
        return date("Y-m-01", $stime);
    }

    /*
    * desc: 获取上月的最后一天
    *       2013-04-14 ==> 2012-03-[29-31]
    *
    */
    static function getLastMonthLast()
    {
        $stime = strtotime(date("Y-m-01 00:00:00")) - 2;
        $days  = date('t', $stime);
        return date("Y-m-".$days, $stime);
    }

    /*
    * desc: 获取下月的第一天
    *       2013-04-14 ==> 2012-05-01
    *
    */
    static function getNextMonthFirst()
    {
        $days  = date('t');
        $stime = strtotime(date("Y-m-".$days." 23:59:59")) + 2;
        return date("Y-m-01", $stime);
    }

    /*
    * desc: 获取下月的最后一天
    *       2013-04-14 ==> 2012-05-[29-31]
    *
    */
    static function getNextMonthLast()
    {
        $days  = date('t');
        $stime = strtotime(date("Y-m-".$days." 23:59:59")) + 2;
        $days  = date('t', $stime);
        return date("Y-m-".$days, $stime);
    }


    /*
    * desc: 获取去年的当月初
    *       2013-04-14 ==> 2012-04-01
    *
    */
    static function getLastYearFirst()
    {
        $stime = time()-86400*360;
        return date("Y-m-01", $stime);
    }

    /*
    * desc: 获取去年的当月末
    *       2013-04-14 ==> 2012-04-[28-31]
    *
    */
    static function getLastYearLast()
    {
        $stime = time()-86400*360;
        $days  = date('t', $stime);
        return date("Y-m-".$days, $stime);
    }
    /*
    * desc: 计算距离当前n月时间(n可为负)
    *
    *
    */
    static function DeltaMonths($nm)
    {
        $nm = intval($nm);
        $mon_last_first = date('Y-m-01 00:00:00', strtotime($nm.' month'));
        $mon_last_end   = date('Y-m-d H:i:s', strtotime(date('Y-m-01 00:00:00', strtotime(($nm+1).' month')))-1);
        return array($mon_last_first, $mon_last_end);
    }

    /*
    * desc: 计算距离当前n周时间(n可为负)
    *
    *
    */
    static function DeltaWeeks($nw)
    {
        $week_curr_first = strtotime(date('Y-m-d 0:0:0',time()-date('w')*86400)); //当前周的第一天
        $week_last_first = $week_curr_first + ($nw*7) * 86400;
        $week_last_end   = $week_last_first + 604799; // 604799 = 7 * 86400 -1
        return array(date("Y-m-d H:i:s", $week_last_first), date("Y-m-d H:i:s", $week_last_end));
    }

    static function IsCur($datetime, $format="Y-m-d")
    {
        $d1 = date($format);
        $d2 = date($format, strtotime($datetime));
        return $d1==$d2?true:false;
    }
    /*
    * desc: 判断一个时间是否为当天
    *
    */
    static function IsToday($datetime)
    {
        return self::IsCur($datetime, "Y-m-d");
    }
    /*
    * desc: 判断一个时间是否为本周
    *
    */
    static function IsWeek($datetime)
    {
        return self::IsCur($datetime, "Y-W");
    }
    /*
    * desc: 判断一个时间是否为当月
    *
    */
    static function IsMonth($datetime)
    {
        return self::IsCur($datetime, "Y-m");
    }
    /*
    * desc: 判断一个时间是否为当年
    *
    */
    static function IsYear($datetime)
    {
        return self::IsCur($datetime, "Y");
    }
};
