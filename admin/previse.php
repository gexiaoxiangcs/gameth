<?php
$env = $_GET['env'];
if($env == 2){
    define('ENV_GENRATE', 1);//生成
}else{
    define('ENV_PREVISE', 1);//预览
}
unset($_GET['env']);

define('ARC_PUBLISH_STATUS', 0);
define('SYS_DYNAMIC_REQUEST_OPEN', 1);
define('SITE_PATH', '../site/');

include SITE_PATH . 'index.php';