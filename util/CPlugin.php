<?php
/**
 * desc:组件代号和对应的方法名
 * wpid含义:
 *   18:集阅读
*/
define('READING', 18);
define('FREEING', 19);
class CPlugin {
    //应用
    static $READING = READING;
    static $FREEING = FREEING;
    static $PluginArr = array(
        READING  => array('reading',1)//方法名，组件pluginid
    );
};
