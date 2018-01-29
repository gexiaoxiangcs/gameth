<?php
namespace Home\Controller;

use Think\Controller;

/**
 * 后台管理员操作
 */
class GameCenterController extends BaseController{

    public function __construct(){
        parent::__construct();
    }

    public function gamelist(){
        //访问控制
        $model = new \Home\Model\GameCenterModel();
        $data = $model->gamelist();
        $this->assign('data',$data);
        $this->display('gamelist');
    }

    protected function ajaxErrorReturn($text){
        header('HTTP/1.1 400 Bad Request');
        exit($text);
    }

    public function uploadImg(){
        $filename = I('get.uploadfilename/s', 'htmlspecialchars');
        $ImgTool = new \Common\Util\ImgTool();
        $picdata = $ImgTool->uploadStream();
        if(!$picdata){
            $this->ajaxErrorReturn("{$filename}上传失败，请检查文件大小和文件格式");
        }
        //水印图
//        $water_name='';
//        $waterpic = APP_PATH . 'Common/Img/water/'.$_GET['water'].'.png';
//        if(file_exists_case($waterpic)){
//            $water_name = $ImgTool->water($picname, $waterpic, 9);
//        }


        header('Content-type: application/json; charset=utf-8');
        if($picdata){
            exit(json_encode(array(
                'url' => $picdata,

                //'pid' => $picid
            ))); //图片地址
//            }
        }
        $this->ajaxErrorReturn("文件：{$filename} cdn同步失败，重新上传试试，如果多次上传失败请联系开发人员。");
    }

    public function add() {
        $this->display('add');
    }
}