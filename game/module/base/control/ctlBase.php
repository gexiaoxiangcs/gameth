<?php

namespace Base;

use clsData;
use clsVars;
use clsTemplate;
use Base\libTools;

class ctlBase
{
    /**
     * Enter description here ...
     * @var string
     */
    public $entry = '';

    /**
     * Enter description here ...
     * @var string
     */
    public $handle = '';

    /**
     * Enter description here ...
     * @var string
     */
    public $module = '';

    /**
     * Enter description here ...
     * @var string
     */
    public $method = '';

    /**
     * Enter description here ...
     * @var string
     */
    public $base = '';

    /**
     * Enter description here ...
     * @var string
     */
    public $baseDir = '';

    /**
     * 当前base静态资源版本号
     * @var string
     */
    const VERSION = "_IMG_VERSION_CUR_";

    /**
     * 当前模块静态资源版本号
     * @var string
     */
    public $curImgVersion = "20180119";

    /**
     * constructor
     */
    public function __construct()
    {
        $this->entry = clsVars::fetch('GEntry');
        $this->handle = clsVars::fetch('GHandle');
        $this->module = clsVars::fetch('GModule');
        $this->method = clsVars::fetch('GMethod');
        $this->base = clsVars::fetch('GBase');
        $this->baseDir = clsVars::fetch('GBaseDir');
    }

    /**
     * 默认入口方法
     */
    public function funcIndex()
    {
        echo "default index page";
    }

    /**
     * Enter description here ...
     * @param string $dir
     * @param string $imgPrefix
     * @return \clsTemplate
     */
    private function template($dir = '', $imgPrefix = SYS_IMAGE_URL)
    {
        static $tpl = NULL;
        if (empty($tpl)) {
            $tpl = new clsTemplate();
            $tpl->cache = 0;//!isset($_REQUEST['TPLRELOAD']);
            $tpl->imgPrefix = $imgPrefix;
            $tpl->dirCompiles = SYS_TPLCACHE;
            $tpl->dirTempalte = ($dir ? $dir : $this->base . '/template');
            $tpl->dirVersion = $this->curImgVersion;
            $tpl->tplNamePrefix = $this->baseDir;
            $tpl->dir('base', array('dir' => SYS_MODULE . '/base/template', 'ver' => self::VERSION, 'img' => '/base'));
        }
        return $tpl;
    }

    /**
     * Enter description here ...
     * @var array
     */
    private $_tplData = array();

    /**
     * @param string $title
     * @param bool $affix
     */
    public function setTitle($title = '', $affix = TRUE)
    {
        $title = trim($title);
        if ($title) {
            $title .= " - ";
        }
        $this->assign('GTitle', $title);
        $this->assign('GTitleAffix', $affix);
    }

    /**
     * @param string $keywords
     * @param string $description
     */
    public function setSeoContents($keywords = '', $description = '')
    {
        $this->assign('GKeywords', $keywords);
        $this->assign('GDescription', $description);
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setRobots($name, $value = '')
    {
        $data = clsVars::fetch('GRobots');
        if (!is_array($data)) {
            $data = array();
        }
        $data[$name] = $value;
        $this->assign('GRobots', $data);
    }

    /**
     * @param string|array|object $name
     * @param mixed $value
     * @return bool
     */
    final public function assign($name, $value = NULL)
    {
        return clsVars::set($name, $value);
    }

    /**
     * @param string $tplName
     * @param bool $return
     * @return bool|string
     */
    public function display($tplName, $dir = "", $return = FALSE)
    {
        //header("Content-Type: text/html;charset=utf-8");
        $this->template()->assign(clsVars::fetchAll());
        return $this->template($dir)->display($tplName, '', $return);
    }

    /**
     * @param mixed $result
     * @return bool
     */
    public function result($result)
    {
        if (defined('IN_OLD_MODE')) {
            $GLOBALS['RESULT'] = array('code' => 200, 'data' => $result, 'uid' => $this->__uid);
            return FALSE;
        }
        //header("HTTP/1.1 200 Ok");
        header("Content-Type: text/html;charset=utf-8");
        echo is_array($result) ? clsData::jsonEncode($result, JSON_UNESCAPED_UNICODE) : $result;
        return exit(1);
    }

    /**
     * @param int $code
     * @param string $message
     * @param string $desc
     * @return bool
     */
    public function error($code, $message = NULL, $desc = 'ERROR')
    {
        if (defined('IN_OLD_MODE')) {
            $GLOBALS['RESULT'] = array('code' => $code, 'data' => $message);
            return FALSE;
        }
        header("HTTP/1.1 {$code} {$desc}");
        header("Content-Type:text/html;charset=utf-8");
        if ($message) {
            echo $message;
        }
        return exit(1);
    }

    /**
     * 转404页面
     */
    public function __halt(){
        header_remove('Cache-Control');
        libTools::redirect(libTools::U('', 'index', '404'), 404);
    }

    /**
     * 以ajax返回结果
     */
    public function ajaxReturn($data){
        exit(clsData::jsonEncode($data));
    }

    /**
     * 输出缓存
     */
    public function outputCache($cache, $noredirect = SYS_DYNAMIC_REQUEST_OPEN){
        if(defined('ENV_PREVIEW') || isset($_GET['no_cache'])){ //预览环境
            return false;
        }
        if(!$noredirect){
            if($cache){
                exit($cache);
            }else{
                $this->__halt();
            }
        }else{
            if($cache){
                exit($cache);
            }else{
                return !!$cache;
            }
        }
    }
}
