<?php
namespace Index;

require_once 'client.inc.php';
define ('LOCAL_FMS_KEY', 'WWWHOUDONG');
define ('LOCAL_FMS_SECRET', '82b4ed1064c2b68b9051');
define ('LOCAL_FMS_URLPREV', 'http://p.qq494.cn/hd/');

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author Administrator
 */
class LocalFmsClient extends \fmsClientFile{

    function __construct($key = LOCAL_FMS_KEY, $secret = LOCAL_FMS_SECRET){
        parent::__construct($key, $secret);
    }


}
?>