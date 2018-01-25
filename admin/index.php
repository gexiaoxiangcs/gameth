<?php
ini_set('session.gc_maxlifetime',   3600 * 3);
set_time_limit(60);
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件
header("content-type:text/html;charset=utf-8");

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 定义应用目录
define('APP_PATH', realpath(dirname(__FILE__) . '/../ThinkPHP3.2/Application/') . '/');

define('THINK_PATH', realpath(dirname(__FILE__) . '/../ThinkPHP3.2/ThinkPHP/') . '/');

include APP_PATH . 'Common/Conf/init.php';

// 引入ThinkPHP入口文件
require THINK_PATH . 'ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单