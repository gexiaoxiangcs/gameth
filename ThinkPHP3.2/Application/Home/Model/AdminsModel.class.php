<?php
namespace Home\Model;

use Think\Model;



/**
 * 后台管理员模型类
 */
class AdminsModel extends Model{

    const FLAG_LOGIN_VERIFY_WRONG = -5;
    const FLAG_SMS_REST = -10;  //sms发送两分钟间隔
    const FLAG_SMS_REQUEST_LIMIT = -9; //sms发送请求限制
    const FLAG_SMS_SEND_SUCCESS = 2;    //sms发送成功
    const FLAG_SMS_SEND_FAIL = -8;  //sms发送失败
    const FLAG_SMS_NO_PHONE = -7;   //没有手机号，不允许发送验证码

    private $admininfo = array();

    static public function getAdminId(){
        return session('admin_id');
    }

    /**
     * 检查sms码是否是正确的。
     * @param $code
     * @param $username
     * @return bool
     */
    public function smsCodeVerify($code, $username){
        $ip = get_client_ip();
        if(!$code){
            return false;
        }
        $mcverifytimekey = md5('smscodeverifytime' . $username);
        $mccodekey = md5('smscodekey' . $username);
        $verifytime = S($mcverifytimekey);
        if($verifytime > 4){
            return false;
        }
        S($mcverifytimekey, $verifytime + 1, array('expire' => 1800));
        $smscode = S($mccodekey);
        if(intval($smscode) === intval($code)){
            S($mccodekey, null);
            return true;
        }
        return false;
    }

    /**
     * 验证密码是否正确
     * @param $username
     * @param $password
     * @return int|bool
     */
    public function verify($username, $password){
        $curday = strtotime(date('Y-m-d'));
        $password = md5(md5($password));
        $row = $this->where("username='%s'", $username)->find();
        if($curday < $row['last_time'] && $row['wrong_login_time'] > 5){    //错误登陆超过5次，禁止再登陆
            return -2;  //限制登陆
        }
        if($row['password'] != $password){
            if($curday < $row['last_time']){
                $this->where("username='%s'", $username)->save(array('wrong_login_time' => 'wrong_login_time+1'));
            }else{
                $this->where("username='%s'", $username)->save(array('wrong_login_time' => '1', 'last_time' => time()));
            }
            return -1; //密码错误
        }
        $this->setLoginStatus($row);    //保持登陆信息
        $this->updateLoginInfo($row);   //更新登陆信息
        return true;
    }

    /**
     * 设置登陆状态
     * @param $admin
     */
    public function setLoginStatus($admin){
        $privset = '';
        if($admin['priv_set_id']){
            $PRIV = D('Privileges');
            $row = $PRIV->getOnePrivSet($admin['priv_set_id']);
            $privset = $row['privs'];
        }
        session('admin_username', $admin['username']);
        session('admin_id', $admin['id']);
        session('admin_nickname', $admin['nickname']);
        session('admin_privs', $admin['privs'] . ',' . $privset);
    }

    /**
     * 清空登陆信息
     */
    public function clearLoginStatus(){
        session('admin_username', null);
        session('admin_id', null);
        session('admin_nickname', null);
        session('admin_privs', null);
    }

    /**
     * 是否登陆
     * @return bool
     */
    public function isLogin(){
        return session('admin_id') ? true : false;
    }

    /**
     * 更新登陆信息
     * @param $admin
     */
    public function updateLoginInfo($admin){
        $data['id'] = $admin['id'];
        $data['last_ip'] = get_client_ip();
        $data['last_time'] = time();
        $data['wrong_login_time'] = 0;
        $this->save($data);
    }

    /**
     * 检查权限
     * @param $priv
     * @return bool
     */
    public function testPriv($priv){
        $adminprivs = explode(',', session('admin_privs'));
        if(in_array('PRIV_ALL', $adminprivs) || in_array($priv, $adminprivs)){
            return true;
        }
        return false;
    }

    /**
     * 获取后台帐户信息
     * @param int $adminid
     * @return array|bool|mixed
     */
    public function getAdminInfo($adminid = 0){
        if($this->admininfo){
            return $this->admininfo;
        }
        if($adminid){
            $this->admininfo = $this->find($adminid);
            return $this->admininfo;
        }
        return false;
    }

    /**
     * 获取管理员用户名列表
     */
    public static function getAdminNames(){
        $M = M('admins');
        $adminnames = $M->getField('id,nickname');
        return $adminnames;
    }

    /**
     * 获取ip列表
     * @return mixed
     */
    public function getControlIPList(){
        $M = M('ip_control');
        $rows = $M->where('valid=1 AND expire>' . time())->order('id DESC')->select();
        return $rows;
    }

    /**
     * 添加管理ip
     */
    public function addControlIP($data){
        $data['ip'] = htmlspecialchars($data['ip']);
        $data['expire'] = intval($data['expire']) ? intval($data['expire']) : time() + 432000;
        $data['admin_id'] = self::getAdminId();
        $data['addtime'] = time();
        $M = M('ip_control');
//        var_dump($data);
//        var_dump($M->fetchSql(true)->add($data));exit;
        return $M->add($data);
    }

    /**
     * 删除管理ip
     * @param $id
     */
    public function disableControlIP($id){
        $id = intval($id);
        $data = array(
            'id' => $id,
            'valid' => 0
        );
        $M = M('ip_control');
        return $M->save($data);
    }

    /**
     * ip是否是允许访问里的
     */
    public function isAllowIp($ip){
        $M = M('ip_control');
        $ip = htmlspecialchars($ip);
        $todaytime = time();
        if(preg_match('/^192.168./', $ip) || preg_match('/^127.0./', $ip)){
            return true;
        }
        $row = $M->where("ip='%s' AND valid=1 AND expire>'%d'", $ip, $todaytime)->find();
        if(!$row){
            return false;
        }
        return true;
    }
}