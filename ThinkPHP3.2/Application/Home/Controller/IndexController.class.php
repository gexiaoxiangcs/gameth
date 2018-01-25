<?php
namespace Home\Controller;

use Think\Controller;
use Home\Model\AdminsModel;

class IndexController extends Controller{

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $Admin = D('Admins');
        if(!$Admin->isLogin()){ //检查是否有登陆
            $this->redirect('Index/login');
        }
        $this->display();
    }

    public function login(){
        //访问控制
        $this->UALimit();
        if(!$this->ipLimit()){
            $this->assign(array(
                'outsidecompany' => 1
            ));
        }
        $this->display();
    }

    protected function UALimit(){
        $ua = $_SERVER['HTTP_USER_AGENT'];
//        if(!preg_match('/3533/', $ua)){   //用户useragent判断
//            exit('not allowed to login!!');
//        }
        return true;
    }

    protected function ipLimit($exitflag = false){
        $D = D('Admins');
//        if($D->testPriv('PRIV_ALL')){ //如果是管理员帐号的话不需要ip限制
//            return true;
//        }
        $ip = get_client_ip();
        if($D->isAllowIp($ip)){
            return true;
        }
        $D->clearLoginStatus();
        if($exitflag){
            exit('not allowed to login!!');
        }else{
            return false;
        }
    }

    public function logout(){
        $Admin = D('Admins');
        $Admin->clearLoginStatus();
        $this->redirect('Index/login');
    }

    public function loginVerify(){
        //访问控制
        $this->UALimit();

        $username = I('username', '', 'htmlspecialchars');
        $password = I('password', '', 'htmlspecialchars');
        $verifycode = I('verifycode');
        if(!$username || !$password || !$verifycode){
            $this->error('数据不完整', U('Index/login'), 2);
        }

        if(!$this->checkVerifyCode($verifycode, array('username' => $username))){
            $this->error('验证验错误', U('Index/login'), 3);
        }

        //密码验证
        $Admin = D('Admins');
        $flag = $Admin->verify($username, $password);

        if($flag === -2){
            $this->error('密码连续错误5次，您的帐户已经被锁定。', U('Index/login'), 3);
        }
        if($flag === -1){
            $this->error('密码错误', U('Index/login'), 3);
        }

        if($flag === true){
            $this->success('登陆成功', U('Index/index'), 1);
        }
        return false;
    }

    public function verifyCode(){
        $config =    array(
            'expire' => 1800,
            'fontSize'    =>    25,
            'length'      =>    4
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }

    public function checkVerifyCode($code, $options = ''){
            $Verify = new \Think\Verify();
            return $Verify->check($code, $options['id']);
    }
}