<?php

class clsConfig
{
	/**
	 * Enter description here ...
	 *
	 * @var array
	 */
	private static $_config = array();

	/**
	 * Enter description here ...
	 *
	 * @param string $key
	 * @param string $name
	 *
	 * @return array
	 */
	static public function load($key, $name = '')
	{
		if(empty(self::$_config[$key])){
			$file = realpath(SYS_CONFIG . '/cnf.' . $key . '.php');
			if($file){
				self::$_config[$key] = include($file);
			} else{
				return FALSE;
			}
		}
		if($name){
			return self::$_config[$key][$name];
		}
		return self::$_config[$key];
	}

	/**
	 * Enter description here ...
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	static public function dbInfo($key)
	{
		return self::load($key, 'db');
	}

	/**
	 * Enter description here ...
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	static public function mcInfo($key)
	{
		return self::load($key, 'mc');
	}

	/**
	 * Enter description here ...
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	static public function redisInfo($key)
	{
		return self::load($key, 'redis');
	}

	/**
	 * coreseek
	 * @param string $key
	 * @return array
	 */
	static public function sphinxInfo($key)
	{
		return self::load($key, 'sphinx');
	}

	/**
	 * Enter description here ...
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	static public function ftpInfo($key)
	{
		return self::load($key, 'ftp');
	}

    /**
     * 登录认证
     * @param string $key
     * @return array
     */
    static public function ptLoginInfo($key)
    {
        return self::load($key, 'ptlogin');
    }

    /**
     * 上传图片服务器的key
     * @param string $key
     * @return string
     */
    static public function srvUploadInfo($key)
    {
        return self::load($key, 'srvUpload');
    }

    /**
     * 后台与servcie通信的加密key
     * @param string $key
     * @return string
     */
    static public function adminKeyInfo($key)
    {
        return self::load($key, 'adminKey');
    }

    /**
     * 百度地图api key
     * @param string $key
     * @return string
     */
    static public function baiduMapKeyInfo($key)
    {
        return self::load($key, 'baiduMapKey');
    }

    /**
     * 人人网api key、密码
     * @param string $key
     * @return string
     */
    static public function renrenApiKeyInfo($key)
    {
        return self::load($key, 'renrenApiKey');
    }

    /**
     * 人人网api key、密码
     * @param string $key
     * @return string
     */
    static public function qqApiKeyInfo($key)
    {
        return self::load($key, 'qqApiKey');
    }

    /**
     * ip白名单
     * @param string $key
     * @return string
     */
    static public function allowIps($key)
    {
        return self::load($key, 'allowIps');
    }
}