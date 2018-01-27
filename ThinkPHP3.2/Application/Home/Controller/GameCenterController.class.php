<?php
namespace Home\Controller;

use Think\Controller;

/**
 * 后台管理员操作
 */
class GameCenterController extends BaseController{

    public function __construct(){
        parent::__construct();
    }

    public function gamelist(){
        //访问控制
        $model = new \Home\Model\GameCenterModel();
        $data = $model->gamelist();
        $this->assign('data',$data);
        $this->display('gamelist');
    }

    public function add() {

    }
}