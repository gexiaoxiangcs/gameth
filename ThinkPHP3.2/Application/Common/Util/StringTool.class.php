<?php
namespace Common\Util;

class StringTool {

    //通过接口查询是否有违规词
    public static function checkBadwords($string, $level = 2, $pinyin = 'true'){
        $url = 'http://115.182.52.10:8082/test/matchService.do';
        $data = sprintf('toCheck=%s&level=%s&byPinyin=%s&simpleMatch=true', urlencode($string), $level, $pinyin);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        curl_close($curl);

        $res = @json_decode(trim($res), 1);
        if(!is_array($res)){
            $res = array();
        }
        return $res;
    }
}
