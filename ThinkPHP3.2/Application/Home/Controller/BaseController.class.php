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

    protected function upload(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
            $this->success('上传成功！');
        }
    }


}