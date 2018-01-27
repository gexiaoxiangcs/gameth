<?php
namespace Home\Model;

use Think\Model;

class UserModel extends Model{
    /**
     * 更新
     * @param $data
     */
    public function update($data){
        $Meta = M('user');
        $info['id'] = $data['id'];
        $info['vip'] = htmlspecialchars(strip_tags($data['vip']));
        $info['remark'] = htmlspecialchars(strip_tags($data['remark']));
        $info['snippet'] = I('post.snippet', '', '');
        $info['visit'] = intval($data['visit']);
        $info['qq'] = htmlspecialchars($data['qq']);
        $info['status'] = intval($data['status']);
        $affected = $Meta->save($info);
        return $affected;
    }

    public function getUsers($dryhumorlist) {
        foreach($dryhumorlist as $d) {
            $arr[] = $d['uid'];
        }
        $arr = array_unique($arr);
        $str = '';
        foreach($arr as $a) {
            $str .= $a;
            $str .= ',';
        }
        $str = rtrim($str,',');
        $map['uid'] = array('in',$str);
        $rows = $this->field('uid,nickname')->table('gx_user')->where($map)->select();
        foreach($rows as $r) {
            $arr[] = array(
                'uid' => $r['uid'],
                'nickname' => $r['nickname'],
            );
        }
        $a = array(

        );
        foreach($rows as $r) {
            $b = array(
                $r['uid'] => $r['nickname']
            );
            $a = $b + $a;
        }
        return $a;
    }
}