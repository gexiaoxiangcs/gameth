<?php
namespace Home\Controller;

use Think\Controller;

class BaseController extends Controller{

    public function __construct(){
        parent::__construct();
        $Admin = D('Admins');
        if(!$Admin->isLogin()){ //检查是否有登陆
            $this->redirect('Index/login');
        }
    }

    protected function showAjaxResult($status, $succmsg = '', $failmsg = ''){
        if($status){
            $this->ajaxReturn(array(
                'success' => true,
                'msg' => $succmsg
            ));
        }else{
            $this->ajaxReturn(array(
                'success' => false,
                'msg' => $failmsg
            ));
        }
    }

    /**
     * 检查权限
     * @param $priv
     * @return bool
     */
    protected function checkPriv($priv, $ajax = false){
        $Admin = D('Admins');
        if(!$Admin->testPriv($priv)){
            if($ajax){
                $this->ajaxReturn(array(
                    'success' => false,
                    'msg' => '没有权限'
                ));
            }else{
                $this->error('没有权限');
            }
        }
        return true;
    }


}