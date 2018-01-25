<?php
header("Content-Type:text/html;charset=utf-8");
if(!defined('ARC_PUBLISH_STATUS')){
    define('ARC_PUBLISH_STATUS', 1);
}

if(($lastpos = strrpos($_GET['_RW_'],'/')) === strlen($_GET['_RW_']) - 1){ //去掉最后一个/
    $_GET['_RW_'] = substr($_GET['_RW_'], 0, $lastpos);
}

require "../game/source/common.inc.php";


