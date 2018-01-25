<?php
error_reporting(E_ALL ^ E_NOTICE);
define('ENV_PREVIEW', 1);
define('SYS_DYNAMIC_REQUEST_OPEN', 1);
define('ARC_PUBLISH_STATUS', 0);
define('SITE_PATH', '../site/');

$type = strtolower($_GET['type']);

switch($type){
    case 'funnyimg':    //囧图
    case 'dryhumor':    //冷笑话
    case 'wonderfultalk':   //奇葩说
    case 'video':   //视频
    case 'zhoukan':   //周刊
    case 'note':   //短篇
        $_GET['_RW_'] = 'index/' . $type . '-content-id-' . $_GET['id'];
        break;
    case 'course':   //原创视频
        $_GET['_RW_'] = 'index/funnycourse-content-id-' . $_GET['id'];
        break;
    default: //默认预览首页
        $_GET['_RW_'] = 'index/index-index';
        break;
}

if($_GET['route']){
    $_GET['_RW_'] = preg_replace('/[^\/\w\-]+/', '', $_GET['route']);
}

include SITE_PATH . 'index.php';
