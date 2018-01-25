<?php
/**
 * 例程
 */
unset($argv[0]);
unset($argv[1]);
$params = $argv;
define('CRONTAB_APP', true);

//安全限制
if($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR']){
    exit();
}


include dirname(__FILE__) . '/index.php';