<?php

class clsRouter
{
	/**
	 * 系统模块入口
	 * @var string
	 */
	public $entry = '';

	/**
	 * 功能模块入口
	 * @var string
	 */
	public $handle = '';

	/**
	 * 子模块入口
	 * @var string
	 */
	public $module = '';

	/**
	 * 调用的实际方法
	 * @var string
	 */
	public $method = '';

	/**
	 * 模块目录
	 * @var string
	 */
	public $base = '';

	/**
	 * Enter description here ...
	 * @var string
	 */
	public $baseDir = '';

	/**
	 * Enter description here ...
	 * @var string
	 */
	public $basePrefix = '';

	/**
	 * Enter description here ...
	 */
	public function __construct()
	{
		$this->_rewrite($_GET['_RW_']);
		unset($_GET['_RW_']);
	}

	/**
	 * Enter description here ...
	 * @return bool
	 */
	public function parse()
	{
		$moduleName = '';
		$base = SYS_MODULE . "/{$this->entry}";

		if(!file_exists($base)){
			return $this->_halt(99, 'Invalid entry!');
		}

		$moduleName .= "\\{$this->entry}";

		if($this->basePrefix){
			if(!$this->handle){
				return $this->_halt(98, 'Invalid handle!');
			}
			$base .= "/{$this->basePrefix}/{$this->handle}";

			if(!file_exists($base)){
				return $this->_halt(97, 'Invalid handle!');
			}
			$moduleName .= "\\__NS__{$this->basePrefix}\\{$this->handle}";
		} elseif($this->handle){
			$base .= "/{$this->handle}";

			if(!file_exists($base)){
				return $this->_halt(96, 'Invalid handle!');
			}
			$moduleName .= "\\{$this->handle}";
		} else{
			// /plugins/favorite  => /plugins/favorite/
			if($this->module && file_exists("{$base}/{$this->module}")){
				$uri = explode("?", $_SERVER['REQUEST_URI'], 2);
				$url = $uri[0] . "/";
				if(isset($uri[1])){
					$url .= "?" . $uri[1];
				}
				clsTools::redirect($url);
				return FALSE;
			}
		}

		$comFile = "{$base}/common.php";

		$base .= "/control/";
		if(!is_dir($base)){
			return $this->_halt(95, 'Invalid entry!');
		}

		if($this->method == '' && $this->module && $this->module != 'index'){
			$ctlModule = 'ctl' . ucfirst($this->module);
			$ctlModuleFile = "{$base}/{$ctlModule}.php";
			if(!is_file($ctlModuleFile)){
				$this->method = $this->module;
				$this->module = '';
			}
		}

		empty($this->module) && $this->module = 'index';
		empty($this->method) && $this->method = 'index';

		$ctlModule = "ctl" . ucfirst($this->module);
		$ctlModuleFile = "{$base}/{$ctlModule}.php";
		if(!is_file($ctlModuleFile)){
			return $this->_halt(94, 'Invalid module');
		}

		$moduleName .= "\\{$ctlModule}";

		if(is_file($comFile)){
			require_once $comFile;
		}
		require_once $ctlModuleFile;

		if(!class_exists($moduleName, FALSE)){
			return $this->_halt(93, "Invalid module!");
		}

		$this->base = dirname($base);
		$this->baseDir = substr($this->base, strlen(SYS_MODULE));

		clsVars::set('GEntry', $this->entry);
		clsVars::set('GHandle', $this->handle);
		clsVars::set('GModule', $this->module);
		clsVars::set('GMethod', $this->method);
		clsVars::set('GBase', $this->base);
		clsVars::set('GBaseDir', $this->baseDir);

		$control = new $moduleName();

		$methods = array();
		$methods[] = 'func' . $this->method;
		$methods[] = 'funcIndex';

		$method = NULL;

		foreach($methods as $tmp){
			if(method_exists($control, $tmp)){
				$method = $tmp;
				break;
			}
		}
		if(empty($method)){
			$this->_halt(92, "Invalid method!");
		}

		define('SYS_ENV_ROOT', $this->baseDir);
		$control->$method();
		$control = NULL;
		unset($control);
		return TRUE;
	}

	/**
	 * 过滤参数非法字符
	 * @param string $param
	 * @param bool $require
	 * @return string
	 */
	private function _filter($param, $require = TRUE)
	{
		$param = preg_replace('/^\W$/is', '', trim($param));
		if(!$param && $require){
			$param = 'index';
		}
		return $param;
	}

	/**
	 * Enter description here ...
	 * @param int $code
	 * @param string $message
	 * @return bool
	 */
	private function _halt($code, $message)
	{
        if(SYS_ENVIRONMENT == 'PRO'){
            file_put_contents("/tmp/sys_result.err", date("Y-m-d H:i:s") . "\t{$code}\t{$_SERVER['REQUEST_URI']}\t{$message}\n", FILE_APPEND);
            include SYS_SITEPATH_PREFIX . '/404.html';
        }else{
            exit("Error(#{$code}): $message");
        }
        exit;
	}

	/**
	 * Enter description here ...
	 * @param string $url
	 * @return void
	 */
	private function _rewrite($url)
	{
            if($url{0} == '/'){
                    $url = substr($url, 1);
            }

            $data = explode("/", $url);
            $this->entry = $this->_filter($data[0]);
            unset($data[0]);
            $module = implode('-', $data);
            
//            if(preg_match('/^\d+$/is', $data[1])){
//                $this->basePrefix = $data[1];
//                $this->handle = $this->_filter($data[2], FALSE);
//                $module = $data[3];
//            } else{
//                $len = count($data);
//                if($len == 3){
//                    $this->handle = $this->_filter($data[1], FALSE);
//                    $module = $data[2];
//                } else{
//                    $module = $data[1];
//                }
//            }

            $dat = explode("-", $module);
            $len = count($dat);
            $this->module = $this->_filter($dat[0]);
            if($len % 2 == 0){
                $this->method = $this->_filter($dat[1]);
                unset($dat[1]);
            } else{
                $this->method = '';
            }
            unset($dat[0]);
            $dat = array_values($dat);
            $len = count($dat);
            for($i = 0; $i < $len; $i += 2){
                $_GET[$dat[$i]] = $dat[$i + 1];
                $_REQUEST[$dat[$i]] = $dat[$i + 1];
            }
	}
}
