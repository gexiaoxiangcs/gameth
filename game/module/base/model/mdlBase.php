<?php

namespace Base;

use clsDatabase;
use clsMemcached;
use clsConfig;
use clsSphinxClient;

class mdlBase
{
    /**
     * @var array
     */
    private static $_instance = array();

    /**
     * @var array
     */
    public static $modules = array();

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * Enter description here ...
     * @param string $name
     * @param string $namespace
     * @return object
     */
    public function loadModule($name, $namespace = '')
    {
        $name = $namespace . "\\" . $name;
        if (!(self::$modules[$name] instanceof $name)) {
            self::$modules[$name] = new $name();
        }
        return self::$modules[$name];
    }

    /**
     * Enter description here ...
     * @return object
     */
    static public function instance()
    {
        static $modules = array();
        $class = get_called_class();
        if (!isset($modules[$class]) || !($modules[$class] instanceof $class)) {
            $modules[$class] = new $class();
        }
        return $modules[$class];
    }

    /**
     * Enter description here ...
     * @param string $key
     * @return clsDatabase
     */
    public function db($key)
    {
        $block = 'db';
        if (empty(self::$_instance[$block][$key]) || !(self::$_instance[$block][$key] instanceof clsDatabase)) {
            $db = new clsDatabase();
            $dbInfo = clsConfig::dbInfo($key);
            $db->connect($dbInfo['host'], $dbInfo['user'], $dbInfo['pass'], $dbInfo['name']);
            $db->charset($dbInfo['charset']);
            self::$_instance[$block][$key] = $db;
        }
        return self::$_instance[$block][$key];
    }

    /**
     * Enter description here ...
     * @param string $key
     * @return clsMemcached
     */
    public function mc($key)
    {
        $block = 'mc';
        if (empty(self::$_instance[$block][$key]) || !(self::$_instance[$block][$key] instanceof clsMemcached)) {
            $mc = new clsMemcached();
            $mcInfo = clsConfig::mcInfo($key);
            $mc->addServers($mcInfo);
            self::$_instance[$block][$key] = $mc;
        }
        return self::$_instance[$block][$key];
    }

    /**
     * Enter description here ...
     * @param string $key
     * @return \clsSphinxClient
     */
    public function sphinx($key)
    {
        $block = 'sphinx';
        if (empty(self::$_instance[$block][$key]) || !(self::$_instance[$block][$key] instanceof clsSphinxClient)) {
            $sphinx = new \clsSphinxClient();
            $sphinxInfo = clsConfig::sphinxInfo($key);
            $sphinx->SetServer($sphinxInfo['host'], $sphinxInfo['port']);
            $sphinx->SetMatchMode(SPH_MATCH_ALL);
            $sphinx->SetArrayResult(TRUE);
            $sphinx->SetConnectTimeout(3);
            self::$_instance[$block][$key] = $sphinx;
        }
        return self::$_instance[$block][$key];
    }

    /**
     * @param string $message
     * @return bool
     */
    protected function _halt($method, $message = '') {
        exit($method . ': ' . $message);
    }
    
    
    /**
     * 依据参数获取一个缓存key
     * @param array $param
     * @return string
     */
    public function getMemKey($param) {
        $param=(array)$param;
        array_multisort($param);
        return md5(serialize($param));
    }
    
    /**
     * 获取缓存数据
     * @param string $key
     * @return FALSE|mix
     */
    public function getMemValue($key) {
        if(isset($_GET['nocache']) || isset($_GET['no_cache']))return false;
        return $this->mc('base')->get($key);
    }
    
    /**
     * 缓存数据
     * @param string $key
     * @param mix $value
     * @param int $expire 0:永久存储 >0：距离过期秒数
     * @return Boolean
     */
    public function setMemValue($key,$value,$expire=300) {
        //解决不能超过30天的问题
        if($expire>2592000)$expire=time()+$expire;
        
        return $this->mc('base')->set($key, $value, $expire);
    }
    
    /**
     * 是否含有过滤词
     * @param $content
     * @return bool
     */
    public function hasFilterWord($content){
        $wordpath = \Index\mdlCache::getCachePath('wordfilter');
        $wordlist = explode("\n", @file_get_contents($wordpath));
        foreach($wordlist as $v){
            $v = trim($v);
            if(!$v){
                continue;
            }
            if(strpos($content, trim($v)) !== false){
                return true;
            }
        }
        return false;
    }
}
