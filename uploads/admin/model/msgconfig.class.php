<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2018 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class msgconfig_controller extends adminCommon{
	function index_action(){
		$this->yuntpl(array('admin/admin_msg_config'));
	}
	
	function save_action(){
 		if($_POST['config']){
			unset($_POST['config']);
		    foreach($_POST as $key=>$v){
		    	$config=$this->obj->DB_select_num("admin_config","`name`='$key'");
			    if($config==false){
					$this->obj->DB_insert_once("admin_config","`name`='$key',`config`='".$v."'");
			    }else{
					$this->obj->DB_update_all("admin_config","`config`='".$v."'","`name`='$key'");
			    }
		 	}
			$this->web_config();
			$this->ACT_layer_msg( "短信配置设置成功！",9,1,2,1);
		 }
	}
	
	function tpl_action(){
		$this->yuntpl(array('admin/admin_msg_tpl'));
	}
	
	function settpl_action(){
		extract($_POST);
		if($config){
		    $config=$this->obj->DB_select_num("templates","`name`='$name'");
		    if($config==false){
				$this->obj->DB_insert_once("templates","name='$name',`title`='$title',`content`='".$content."'");
		    }else{
				$this->obj->DB_update_all("templates","`title`='$title',`content`='".$content."'","`name`='$name'");
		    }
			$this->ACT_layer_msg( "短信模版配置设置成功！",9,$_SERVER['HTTP_REFERER'],2,1);
		}
		include(CONFIG_PATH."db.tpl.php");
		$this->yunset("arr_tpl",$arr_tpl);
		$name=$_GET['name'];
		$row=$this->obj->DB_select_once("templates","`name`='$name'");
		$this->yunset("row",$row);
		$this->yuntpl(array('admin/admin_settpl'));
	}
	
	function get_restnum_action(){
	    $msguser=trim($_POST['msguser']);
	    $url='http://msg.phpyun.com/restnumnew.php';
	    $url.='?msguser='.iconv('utf-8','gbk',$msguser).'';
	    if(function_exists('file_get_contents')){
	        $file_contents = file_get_contents($url);
	    }else{
	        $ch = curl_init();
	        $timeout = 5;
	        curl_setopt ($ch, CURLOPT_URL, $url);
	        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	        $file_contents = curl_exec($ch);
	        curl_close($ch);
	    }
	    echo $file_contents;
	}
}

?>