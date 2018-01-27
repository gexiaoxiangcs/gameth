<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\AdminsModel;
class IndexController extends Controller {

    public function test(){
        $Ip = new \Org\Net\IpLocation(); // 实例化类 参数表示IP地址库文件
        $area = $Ip->getlocation(get_client_ip()); // 获取某个IP地址所在的位置
        echo mb_internal_encoding();
//        var_dump(\Common\Util\StringTool::checkBadwords('毛泽东，胡锦涛，习近平'));
    }

    public function index(){
        $Admin = D('Admins');
        if(!$Admin->isLogin()){ //检查是否有登陆
            $this->redirect('Index/login');
        }
        $this->display();
    }

    /**
     * 登陆框
     */
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

    /**
     * 发送sms验证码
     */
    public function sendSmsVerifyCode(){
        $username = I('post.username');
        $password = I('post.password');
        if(!trim($username) || !trim($password)){
            $this->ajaxReturn(array(
                'msg' => '信息没有填写完整'
            ));
        }

        //发送验证码
        $Admin = D('Admins');
        $flag = $Admin->sendSmsCode($username, $password);

        if($flag === AdminsModel::FLAG_LOGIN_VERIFY_WRONG){
            $this->ajaxReturn(array(
                'msg' => '用户名或是密码错误，或者由于多次输入错误帐户已经被冻结'
            ));
        }

        if($flag === AdminsModel::FLAG_SMS_NO_PHONE){
            $this->ajaxReturn(array(
                'msg' => '该帐户没有提供短信验证码功能。请选择有提供验证码功能的帐户来登陆。'
            ));
        }

        if($flag === AdminsModel::FLAG_SMS_REST){
            $this->ajaxReturn(array(
                'msg' => '距离上次发送手机短信时间间隔不到2分钟，请稍后再尝试登录!'
            ));
        }

        if($flag === AdminsModel::FLAG_SMS_REQUEST_LIMIT){
            $this->ajaxReturn(array(
                'msg' => '频繁使用手机验证码功能，已经冻结该功能。'
            ));
        }

        if($flag === AdminsModel::FLAG_SMS_SEND_SUCCESS){
            $this->ajaxReturn(array(
                'msg' => '验证码已发送',
                'code' => 200
            ));
        }

        if($flag === AdminsModel::FLAG_SMS_SEND_FAIL){
            $this->ajaxReturn(array(
                'msg' => '短信发送失败，请稍后再试，如果仍然有问题，请联系管理员。'
            ));
        }
    }

    public function logout(){
        $Admin = D('Admins');
        $Admin->clearLoginStatus();
        $this->redirect('Index/login');
    }

    /**
     * 登陆验证
     */
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

    /**
     * 验证码
     */
    public function verifyCode(){
        $config =    array(
            'expire' => 1800,
            'fontSize'    =>    25,
            'length'      =>    4
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }

    protected function UALimit(){
        $ua = $_SERVER['HTTP_USER_AGENT'];
//        if(!preg_match('/3533/', $ua)){   //用户useragent判断
//            exit('not allowed to login!!');
//        }
        return true;
    }

    /**
     * @return bool
     */
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

    /**
     * 验证验证码
     * @param $code
     * @param $options
     * @return bool
     */
    public function checkVerifyCode($code, $options = ''){
        if($this->ipLimit()){
            $Verify = new \Think\Verify();
            return $Verify->check($code, $options['id']);
        }else{
            $Admin = D('Admins');
            return $Admin->smsCodeVerify($code, $options['username']);
        }
    }

}