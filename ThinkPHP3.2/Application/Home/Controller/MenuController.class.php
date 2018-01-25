<?php
namespace Home\Controller;

use Think\Controller;

class MenuController extends BaseController{
    public function menu(){
        $adminprivs = explode(',', session('admin_privs'));
        $menulist = $this->getMenuList();
        $this->assign('menulist', $menulist);
        $this->display();
    }

    public function newMenu(){
        $this->checkPriv('PRIV_ALL');
        $menuoptions = $this->getMenuOptions();
        $this->display();
    }

    /**
     * 获取菜单列表,二级菜单
     * @return array
     */
    protected function getMenuList(){
        $menulist = array();
        $Menu = M('Menus');
        $menus = $Menu->select();
        foreach($menus as $v){
            if($v['parent_id'] == 0){
                $menulist[$v['id']][0] = $v;
            }else{
                $menulist[$v['parent_id']][1][] = $v;
            }
        }
        return $menulist;
    }

    /**
     * 获取菜单options列表
     * @param $menulist
     * @return string
     */
    public function getMenuOptions(){
        $options = '';
        $Menu = M('Menus');
        $menus = $Menu->where('parent_id=0')->select();
        foreach($menus as $v){
            $options .= sprintf('<options value="%s">%s</options>', $v['id'], $v['name']);
        }
        return $options;
    }

}