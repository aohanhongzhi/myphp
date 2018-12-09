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


class log_model extends model{
  function admin_log($content, $opera = '', $type = '', $opera_id=''){
    if($_SESSION['auid'] && $_SESSION['ausername']&&$content){
      $value="`uid`='".$_SESSION['auid']."',";
      $value.="`username`='".$_SESSION['ausername']."',";
      $value.="`content`='".$content."',";
      $value.="`did`='".$this->config['did']."',";
      $value.="`ctime`='".time()."'";

      $value.=",`ip`='".fun_ip_get()."',";
      $value .= "`opera`='".$opera."',`type`='".$type."',`opera_id`='".$opera_id."'";
		
      $this->DB_insert_once("admin_log",$value);
    }
  }

  function member_log($content,$opera='',$type=''){
    if($_COOKIE['uid']){
      $value="`uid`='".(int)$_COOKIE['uid']."',";
      $value.="`usertype`='".(int)$_COOKIE['usertype']."',";
      $value.="`content`='".$content."',";
      $value.="`opera`='".$opera."',";
      $value.="`type`='".$type."',";
      $value.="`ip`='".fun_ip_get()."',";
      $value.="`ctime`='".time()."'";
      $this->DB_insert_once("member_log",$value);
    }
  }
}
