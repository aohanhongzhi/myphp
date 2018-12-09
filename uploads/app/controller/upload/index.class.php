<?php
/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2018 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class index_controller extends common{
  
  private $tokenSalt = 'phpyun';

  
  private function generateToken($type, $uid)
  {
    
    $row = $this->obj->DB_select_once('member', "`uid`={$uid}", '`password`');
    $password = isset($row['password']) ? $row['password'] : '';
    $password = substr($password, 0, 8);
    
    $this->tokenSalt = $this->config['sy_safekey'];
    return encrypt("{$type}|{$uid}|{$password}", $this->tokenSalt);
  }

  
  public function qrcode_action()
  {
    if(!$this->uid){
      exit('请先登录');
    }
    
    
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    if($type == ''){
      exit('扫码上传图片可选类型type：1企业营业执照上传，2个人身份证上传，3个人头像');
    }

    $token = $this->generateToken($type, $this->uid);
    $token = urlencode($token);
    $url = Url('wap',array('c'=> 'upload', 'a' => 'p', 't' => $token) );

    include_once LIB_PATH."yunqrcode.class.php";
    YunQrcode::generatePng2($url, 4);
  }

}
?>