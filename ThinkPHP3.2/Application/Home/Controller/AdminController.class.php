<?php
namespace Home\Controller;

use Think\Controller;

/**
 * 后台管理员操作
 */
class AdminController extends BaseController{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 新建管理员表单
     */
    public function newAdmin(){
        $this->checkPriv('PRIV_ALL');
        $this->assign('privsets', $this->getPrivSets());
        $this->display();
    }

    /**
     * 增加后台管理员
     */
    public function addAdmin(){
        $this->checkPriv('PRIV_ALL');
        $Admin = M('Admins');
        $username = I('post.username', '', 'htmlspecialchars');
        $user = $Admin->where("username='%s'", $username)->find();
        if($user){
            $this->error('这个用户名已经存在，请使用其他的用户名。');
        }
        $Admin->create();
        $Admin->password = I('post.password', '', 'md5,md5');
        if(I('post.username') == '' || I('post.password') == ''){
            $this->error('数据不完整');
        }
        if(I('post.password') != I('post.repassword')){
            $this->error('两个密码不一致');
        }
        $Admin->create_time = time();
        $flag = $Admin->add();
        if($flag){
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }

    /**
     * 显示管理员列表
     */
    public function adminList(){
        $this->checkPriv('PRIV_ALL');
        $Admin = M('Admins');
        $adminlist = $Admin->select();
        $this->assign('adminlist', $adminlist);
        $this->display();
    }

    /**
     * 更新管理员信息表单
     */
    public function edit(){
        $this->checkPriv('PRIV_ALL');
        $Admin = M('Admins');
        $PRIV = D('Privileges');
        $id = I('get.id/d');
        $admininfo = $Admin->where('id=%d', $id)->find();
        $privs = $PRIV->getPrivs();
        $privsets = $PRIV->getPrivSets();
        $this->assign('adminprivs', explode(',', $admininfo['privs']));
        $this->assign('privs', $privs);
        $this->assign('privsets', $privsets);
        $this->assign('admininfo', $admininfo);
        $this->display();
    }

    /**
     * 更新管理员信息
     */
    public function updateAdmin(){
        $this->checkPriv('PRIV_ALL');
        $Admin = M('Admins');
        $password = I('post.password');
        if($password != '' && $password != I('post.repassword')){
            $this->error('两次密码不一致');
        }else if($password){
            $data['password'] = md5(md5($password));
        }
        $data['id'] = I('post.id/d');
        $data['realname'] = I('post.realname');
        $data['nickname'] = I('post.nickname');
        $data['email'] = I('post.email');
        $data['privs'] = implode(',', I('post.privs', array()));
        $data['priv_set_id'] = I('post.priv_set_id/d');
        $data['phone'] = I('post.phone');
        $flag = $Admin->save($data);
        if($flag){
            $this->success('更新成功');
        }else{
            $this->error('更新失败');
        }
    }

    /**
     * 修改密码表单
     */
    public function editPassword(){
        $Admin = M('Admins');
        $id = session('admin_id');
        $admininfo = $Admin->where('id=%d', $id)->find();
        $this->assign('admininfo', $admininfo);
        $this->display();
    }

    /**
     * 更新密码
     */
    public function savePassword(){
        $Admin = M('Admins');
        $password = I('post.password');
        if($password != '' && $password != I('post.repassword')){
            $this->error('两次密码不一致');
        }else if($password){
            $data['password'] = md5(md5($password));
        }
        $data['id'] = session('admin_id');
        $flag = $Admin->save($data);
        if($flag){
            $this->success('更新成功', U('Index/Content'));
        }else{
            $this->error('更新失败');
        }
    }

    /**
     * 删除后台用户
     */
    public function deleteAdmin(){
        $this->checkPriv('PRIV_ALL');
        $Admin = M('Admins');
        $id = I('post.id/d');
        if($id === 1){
            $this->ajaxReturn(array(
                'success' => false,
                'msg' => '超级管理员都敢删除，不想混了呀。'
            ));
        }
        $affected = $Admin->delete($id);
        if(!$affected){
            $this->ajaxReturn(array(
                'success' => false,
                'msg' => '删除失败'
            ));
        }
        $this->ajaxReturn(array(
            'success' => true,
            'msg' => '删除成功'
        ));
    }

    /**
     * ip列表
     */
    public function ipList(){
        $this->checkPriv('PRIV_ALL');
        $D = D('Admins');
        $ips = $D->getControlIPList();
        $this->assign('ips', $ips);
        $this->display();
    }

    /**
     * 添加ip的表单
     */
    public function addIP(){
        $this->checkPriv('PRIV_ALL');
        $this->assign('expire', date('Y-m-d', (time()+864000)));
        $this->display();
    }

    public function saveIP(){
        $this->checkPriv('PRIV_ALL');
        $Admin = D('Admins');
        $data['ip'] = I('post.ip', 0, 'trim');
        $data['expire'] = strtotime(I('post.expire'));
        $data['iptype'] = I('post.type/d', 1);
        if($data['iptype'] == 1){
            $data['expire'] = $data['expire'] < strtotime('+5 day') ? $data['expire'] : strtotime('+5 day');
        }
        if(!preg_match('/(\d{1,3}\.?){4}/', $data['ip'])){
            $this->error('ip填写不正确');
        }
        $flag = $Admin->addControlIP($data);
        if($flag){
            $this->success('增加成功');
        }else{
            $this->error('增加失败');
        }

    }

//    public function editIP(){
//        $this->checkPriv('PRIV_ALL');
//    }
//
//    public function updateIP(){
//        $this->checkPriv('PRIV_ALL');
//    }

    public function deleteIP(){
        $this->checkPriv('PRIV_ALL');
        $D = D('Admins');
        $D->disableControlIP(I('get.id/d'));
        $this->ajaxReturn(array(
            'success' => true,
            'msg' => '删除成功'
        ));
    }

    /**
     * 权限组列表
     */
    public function privSetsList(){
        $groups = $this->getPrivSets();
        $this->assign('groups', $groups);
        $this->display();
    }

    /**
     * 修改权限组表单
     */
    public function editPrivSet(){
        $this->checkPriv('PRIV_ALL');
        $sid = I('get.sid');
        $D = D('Privileges');
        $setinfo = $D->getOnePrivSet($sid);
        $privs = $D->getPrivs();
        $setprivs = explode(',', $setinfo['privs']);
        $this->assign(array(
            'setinfo' => $setinfo,
            'privs' => $privs,
            'setprivs' => $setprivs
        ));
        $this->display();
    }

    /**
     * 保存权限组
     */
    public function savePrivSet(){
        $this->checkPriv('PRIV_ALL');
        $D = D('Privileges');
        $flag = $D->savePrivSet();
        if($flag){
            $this->success('权限更新成功', U('Admin/privSetsList'));
        }else{
            $this->error('更新失败', U('Admin/privSetsList'));
        }
    }

    /**
     * 新增权限组表单
     */
    public function newPrivSet(){
        $D = D('Privileges');
        $this->assign(array(
            'privs' => $D->getPrivs()
        ));
        $this->display();
    }

    /**
     * 添加权限组
     */
    public function addPrivSet(){
        $this->checkPriv('PRIV_ALL');
        $D = D('Privileges');
        $flag = $D->addPrivSet();
        if($flag){
            $this->success('添加成功', U('Admin/privSetsList'));
        }else{
            $this->error('更新失败', U('Admin/privSetsList'));
        }
    }

    /**
     * 显示权限列表
     */
    public function privList(){
        $this->checkPriv('PRIV_ALL');
        $privs = $this->getPrivs();
        $this->assign(array(
            'privs' => $privs
        ));
        $this->display();
    }

    /**
     * 修改权限表单
     */
    public function editPriv(){
        $this->checkPriv('PRIV_ALL');
        $privcode = I('get.priv_code');
        $PRIV = D('Privileges');
        $privinfo = $PRIV->getOnePriv($privcode);
        $this->assign('privinfo', $privinfo);
        $this->display();
    }

    public function deletePriv(){

    }

    /**
     * 添加权限
     */
    public function addPriv(){
        $this->checkPriv('PRIV_ALL');
        $PRIV = D('Privileges');
        $flag = $PRIV->addPriv();
        if($flag){
            $this->success('权限增加成功', U('Admin/privlist'));
        }else{
            $this->error('不可更新', U('Admin/privlist'));
        }
    }

    /**
     * 修改权限
     */
    public function savePriv(){
        $this->checkPriv('PRIV_ALL');
        $PRIV = D('Privileges');
        $flag = $PRIV->savePriv();
        if($flag){
            $this->success('权限修改成功', U('Admin/privlist'));
        }else{
            $this->error('不可更新', U('Admin/privlist'));
        }

    }

    /**
     * 获取权限集合列表
     * @return mixed
     */
    private function getPrivSets(){
        $PrivGroup = M('privilege_groups');
        $privsets = $PrivGroup->select();
        return $privsets;
    }

    /**
     * 获取权限列表
     */
    private function getPrivs(){
        $Priv = M('privileges');
        $privs = $Priv->order('gid')->select();
        return $privs;
    }
}