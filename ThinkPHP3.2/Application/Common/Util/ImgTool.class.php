<?php
namespace Common\Util;

use Think\Upload;
class ImgTool {

    protected $configs;
    protected $uploadhl;
    protected static $imagehandler = NULL;

    public function __construct($configs = array()){
        $default = array(
            'maxSize'    =>    4194304,
            'rootPath'   =>    APP_PATH . 'Home/Uploads/',
            'savePath'   =>    '',
            'saveName'   =>    array('uniqid',''),
            'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),
            'autoSub'    =>    false
        );
        $this->configs = $configs = $default;
        $this->uploadhl = new Upload($configs);
        $this->createImageHandler();    //创建图片处理实例
    }

    /**
     * 删除服务器上的图片
     * @param array|mixed $filepath
     * @return mixed|void
     */
    public function delete($picname){
        $picpath = $this->configs['rootPath'] . $picname;
        @unlink($picpath);
    }

    /**
     * 删除图片cdn
     */
    public function deleteCDN($picname){
        $fms = new \Common\Util\Fmsclient\LocalFmsClient();
        $res = $fms->delete(array($picname));
    }

    /**
     * 图片上传到cdn上。
     * @param $filepath
     * @param $filename
     * @return bool
     */
    public function uploadCDN($filename){
        $fms = new \Common\Util\Fmsclient\LocalFmsClient();
        $filepath = $this->configs['rootPath'] . $filename;
        $res = $fms->upload($filepath, $filename);
        if($res->succ()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 流上传
     */
    public function uploadStream(){
        $filedata = file_get_contents("php://input");
        if(strlen($filedata) > $this->configs['maxSize'] * 8){ //不能大于4M
            return false;
        }
        $suffix = strtolower(pathinfo(I('get.uploadfilename/s'), PATHINFO_EXTENSION));
        if(!in_array($suffix, $this->configs['exts'])){
            return false;
        }
        $picname = uniqid() . '.' . $suffix;
        $tmppath = $this->configs['rootPath'] . $picname;
        $writesucc = file_put_contents($tmppath, $filedata);
        if(!$writesucc){
            return false;
        }else{
            return $picname;
        }
    }
    
    public function getImgSize($picname){
        $tmppath = $this->configs['rootPath'] . $picname;
        if(file_exists($tmppath)){
            $size = getimagesize($tmppath);
            return '#width_'.$size[0].'-height_'.$size[1];
        }
        return '';
    }

    /**
     * 上传单个文件
     */
    public function uploadOne($fileinfo = NULL){
        $fileinfo = $fileinfo ? $fileinfo : array_pop($_FILES);
        if(!$fileinfo){
            return false;
        }
        $uploadinfo = $this->uploadhl->uploadOne($fileinfo);
        if(!$uploadinfo){
            return false;
        }else{
            return $uploadinfo['savename'];
        }
    }

    /**
     * 多文件上传
     * @return array|bool
     */
    public function upload($files = NULL){
        $files = $files ? $files : $_FILES;
        $info = $this->uploadhl->upload($files);
        $result = array();
        if(!$info) {// 上传错误提示错误信息
//            $this->error($this->uploadhl->getError());
            return false;
        }else{// 上传成功 获取上传文件信息
            foreach($info as $file){
                $result[] = $file['savename'];
            }
        }
        return $result;
    }

    /**
     * 批量创建图片缩略图
     * @param $width
     * @param $height
     * @param $sourcenames
     * @param array $dstnames
     * @return array
     */
    public function createThumbs($width, $height, $sourcenames, $dstnames = array()){
        $thumbnames = array();
        foreach($sourcenames as $k => $v){
            $thumbnames[] = $picname = $dstnames[$k] ? $dstnames[$k] : $width . "x". $height . "_" .$v;
            $this->openImg($v)->thumb($width, $height)->save($this->configs['rootPath'] . $picname);
        }

        return $thumbnames;
    }

    /**
     * 生成缩略图
     * @param $width
     * @param $height
     * @param $sourcepic
     * @param string $dstname
     * @return mixed
     */
    public function createOneThumb($width, $height, $sourcepic, $dstname = ''){
        $picname = $dstname ? $dstname : $width."x".$height . "_" .$sourcepic;
        $this->openImg($sourcepic)->thumb($width, $height)->save($this->configs['rootPath'] . $picname);
        return $picname;
    }
    
    /**
     * 生成缩略图,最佳裁剪
     * @param $width
     * @param $height
     * @param $sourcepic
     * @param string $dstname
     * @return mixed
     */
    public function createOneThumb2($width, $height, $sourcepic, $dstname = ''){
        $picname = $dstname ? $dstname : $width."x".$height . "_" .$sourcepic;
        $this->openImg($sourcepic)->thumb($width, $height, 7)->save($this->configs['rootPath'] . $picname);
        return $picname;
    }

    /**
     * 保存gif图片的一个帧
     * @param $picname
     */
    public function saveGifOneFrame($picname, $dstname = false){
        $imgpath = $dstname ? $this->configs['rootPath'] . $dstname : $this->configs['rootPath'] . $picname;
        $img = new \Think\Image\Driver\GIF($this->configs['rootPath'] . $picname);
        file_put_contents($imgpath, $img->image());
        return $dstname ? $dstname : $picname;
    }
    
    /**
     * 添加水印
     * @param string $sourcepic 图片路径
     * @param  string  $source 水印图片路径
     * @param  integer $locate 水印位置
     * @param  integer $alpha  水印透明度
     * @dstname string 水印存储路径
     * @return Object          当前图片处理库对象
     */
    public function water($sourcepic, $waterpic, $locate = self::IMAGE_WATER_SOUTHEAST, $alpha=80, $dstname = '' ){
        $picname = $dstname ? $dstname : "water" . "_" .$sourcepic;
        $this->openImg($sourcepic)->water($waterpic, $locate,$alpha)->save($this->configs['rootPath'] . $picname);
        return $picname;
    }

    /**
     * 加载图片
     * @param $picname
     * @return mixed
     */
    public function openImg($picname){
        self::$imagehandler->open($this->configs['rootPath'] . $picname);
        return self::$imagehandler;
    }

    protected function createImageHandler(){
        if(!self::$imagehandler){
            self::$imagehandler = new \Think\Image();
        }
    }

}

