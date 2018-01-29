<?php
namespace Index;

use clsVars;
use clsConfig;
use clsTools;

class ctlIndex extends \Base\ctlBase {

    /**
     * 初始化
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * 站点首页
     */

    public function funcIndex(){

        $this->display('index/index');
    }
}