<?php
namespace Home\Model;

use Think\Model;

class GameCenterModel extends Model{
    /**
     * 更新
     * @param $data
     */
    public function gamelist(){
        $list = $this->order('id ASC')->select();
        return $list ? $list : array();
    }
}