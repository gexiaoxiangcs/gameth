<?php

define("SYS_ROOT", dirname(__DIR__));
define("SYS_CONFIG", SYS_ROOT . "/config");
define("SYS_LIBRARY", SYS_ROOT . "/library");
define("SYS_SOURCE", SYS_ROOT . "/source");
define("SYS_CACHE", SYS_ROOT . "/cache");
define("SYS_TEMPLATE", SYS_ROOT . "/template");
define("SYS_TPLCACHE", SYS_ROOT . "/cache/tpl");
define("SYS_UPLOAD", SYS_ROOT . "/upload");
define("SYS_STATIC", SYS_ROOT . "/static");
define("SYS_MODULE", SYS_ROOT . "/module");

require SYS_CONFIG . "/config.php";

function __autoload($clsName)
{
	$clsInfo = explode("\\", $clsName);
	$len = count($clsInfo);
	$clsName = $clsInfo[$len - 1];
	if($len == 1){
		$clsInfo[0] = SYS_LIBRARY;
	} else{
		$pref = substr($clsName, 0, 3);
		$clsInfo[0] = SYS_MODULE . "/" . strtolower($clsInfo[0]);
		if($pref == 'mdl'){
			$clsInfo[$len - 1] = 'model';
		} elseif($pref == 'ctl'){
			$clsInfo[$len - 1] = 'control';
		} elseif($pref == 'utf'){
			$clsInfo[$len - 1] = 'test';
		} else{
			$clsInfo[$len - 1] = 'library';
		}
	}
	$base = sprintf('%s/%s.php', strtolower(implode("/", $clsInfo)), $clsName);
	$base = preg_replace('/\/__ns__(\d+)\//is', '/$1/', $base);
	$file = realpath($base);
	if($file){
		require_once $file;
	} else{
		$base = str_replace(SYS_ROOT, '', $base);
		echo "Class $clsName($base) not exists!";
	}
}

if(!function_exists('__d')){
	function __d($var)
	{
		if(SYS_ENVIRONMENT == 'PRO'){
			return __f($var, TRUE);
		}
		return __f($var, FALSE);
	}
}

if(!function_exists('__f')){
	function __f($var, $file = TRUE)
	{
		$var = var_Export($var, 1);
		if($file){
			clsLog::write("debug", $var . "\n");
		} else{
			echo "<pre>\n" . $var . "\n</pre>";
		}
		return TRUE;
	}
}

if(!defined("SYS_TEST_MODE")){
	$router = new clsRouter();
	$router->parse();
	$router = NULL;
	unset($router);
}