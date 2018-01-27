<?php
namespace Home\Model;

use Think\Model;

/**
 * 权限管理
 */
class PrivilegesModel extends Model{

    protected $_validate = array(
        array('priv_code', 'require', '权限编码不能为空', self::MUST_VALIDATE)
    );

    /**
     * 获取权限组信息
     * @param $id
     * @return mixed
     */
    public function getOnePrivSet($id){
        $M = M('privilege_groups');
        return $M->find($id);
    }

    /**
     * 保存权限组
     */
    public function savePrivSet($data = array()){
        $M = M('privilege_groups');
        if($data){
            $M->create($data);
        }else{
            $M->create();
        }
        if(strpos($M->privs, 'PRIV_ALL') !== false){
            return false;
        }
        $M->privs = preg_replace('/\s+/', ",", $M->privs);
        return $M->save();
    }

    /**
     * 添加权限组
     * @param array $data
     * @return bool|mixed
     */
    public function addPrivSet($data = array()){
        $M = M('privilege_groups');
        if($data){
            $M->create($data);
        }else{
            $M->create();
        }
        if(strpos($M->privs, 'PRIV_ALL') !== false){
            return false;
        }
        $M->privs = str_replace("\n", ",", $M->privs);
        return $M->add();
    }

    /**
     * 删除权限，暂时不需要
     */
    public function deletePriv(){

    }

    /**
     * 添加权限
     * @param $data
     */
    public function addPriv($data = array()){
        if($data){
            $this->create($data);
        }else{
            $this->create();
        }
        if($this->priv_code == 'PRIV_ALL'){
            return false;
        }
        return $this->add();
    }

    /**
     * 修改权限
     * @param $data
     */
    public function savePriv($data = array()){
        if($data){
            $this->create($data);
        }else{
            $this->create();
        }
        if($this->priv_code == 'PRIV_ALL'){
            return false;
        }
        return $this->save();
    }

    /**
     * 获取权限
     * @param $privcode
     * @return mixed
     */
    public function getOnePriv($privcode){
        return $this->where("priv_code='%s'", $privcode)->find();
    }

    /**
     * 获取权限集合列表
     * @return mixed
     */
    public function getPrivSets(){
        $PrivGroup = M('privilege_groups');
        $privsets = $PrivGroup->select();
        return $privsets;
    }

    /**
     * 获取所有权限
     * @return mixed
     */
    public function getPrivs($gid = 0){
        if($gid){
            $this->where('gid=%d', $gid);
        }
        $privs = $this->order('gid')->select();
        return $privs;
    }
}