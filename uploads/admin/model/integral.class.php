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
class integral_controller extends adminCommon{
	function index_action()
	{
		$this->yuntpl(array('admin/admin_integral_config'));
	}
	function user_action()
	{
		$this->yuntpl(array('admin/admin_integral_user'));
	}
	function com_action()
	{
		$this->yuntpl(array('admin/admin_integral_com'));
	}
	function save_action(){
		if($_POST["config"]){
		 unset($_POST["config"]);
		 foreach($_POST as $key=>$v){
		 	$config=$this->obj->DB_select_num("admin_config","`name`='$key'");
		 	if($config==false){
		 		$this->obj->DB_insert_once("admin_config","`name`='$key',`config`='".$v."'");
		 	}else{
					$this->obj->DB_update_all("admin_config","`config`='".$v."'","`name`='$key'");

		 	}
		 }
		 $this->web_config();
		 $this->ACT_layer_msg($this->config['integral_pricename']."配置修改成功！",9,1,2,1);
		}
	}
	function class_action(){
		
		$list=$this->obj->DB_select_all("admin_integralclass","1 order by integral asc");
		$this->yunset("list",$list);
		$this->yuntpl(array('admin/admin_integral_class'));
	}
	
	function add_action(){
	    $_POST=$this->post_trim($_POST);
	    $integralclass=$this->obj->DB_select_all("admin_integralclass","`integral`=".(int)$_POST['integral']."");
	    if(empty($integralclass)){ 
			$data="`integral`='".(int)$_POST['integral']."',`discount`='".$_POST['discount']."',`state`='".(int)$_POST['state']."'";				
			$add=$this->obj->DB_insert_once("admin_integralclass",$data);     
	        $this->cache_action();
	        $add?$msg=2:$msg=3;
	        $this->MODEL('log')->admin_log($this->config['integral_pricename']."类型(ID:".$add.")添加成功！");
	    }else{
	        $msg=1;
	    }
	    echo $msg;die;
	}

	
	function del_action()
	{
		if((int)$_GET['delid'])
		{
			$this->check_token();
			$id=$this->obj->DB_delete_all("admin_integralclass","`id`='".$_GET['delid']."'");
			$this->cache_action();
			$id?$this->layer_msg($this->config['integral_pricename'].'类型(ID:'.$_GET['delid'].')删除成功！',9,0,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,0,$_SERVER['HTTP_REFERER']);
		}
		if($_POST['del'])
		{
			$del=@implode(",",$_POST['del']);
			$id=$this->obj->DB_delete_all("admin_integralclass","`id` in (".$del.")","");
			$this->cache_action();
			isset($id)?$this->layer_msg($this->config['integral_pricename'].'类型(ID:'.$del.')删除成功！',9,1,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,1,$_SERVER['HTTP_REFERER']);
		}
		$this->yuntpl(array('admin/admin_integral_class'));
	}
	function cache_action()
	{
		include(LIB_PATH."cache.class.php");
		$cacheclass= new cache(PLUS_PATH,$this->obj);
		$makecache=$cacheclass->integralclass_cache("integralclass.cache.php");
	}
	function ajax_action(){
		if((int)$_GET['id']&&$_GET['type']){
			if((int)$_GET['rec']==1){
				$state=1;
			}else{
				$state=2;
			}
			$nid=$this->obj->DB_update_all("admin_integralclass","`".$_GET['type']."`='".$state."'","`id`='".(int)$_GET['id']."'");
			$this->MODEL('log')->admin_log($this->config['integral_pricename']."类型(ID:".$_POST['id'].")修改状态！");
			
		}
		if($_POST['integral']){
			$integralclass=$this->obj->DB_select_all("admin_integralclass","`integral`=".(int)$_POST['integral']." and `id`<>'".$_POST['id']."'");
			if($integralclass){
				echo 2;die;
			}
			$nid=$this->obj->DB_update_all("admin_integralclass","`integral`='".$_POST['integral']."'","`id`='".$_POST['id']."'");
			$this->MODEL('log')->admin_log($this->config['integral_pricename']."充值类型(ID:".$_POST['id'].")修改数量！");
		}
		if($_POST['discount']>=0){
			$nid=$this->obj->DB_update_all("admin_integralclass","`discount`='".$_POST['discount']."'","`id`='".$_POST['id']."'");
			$this->MODEL('log')->admin_log($this->config['integral_pricename']."充值类型(ID:".$_POST['id'].")修改折扣！");
		}
		$this->cache_action();
		echo $nid?1:0;
	}
}

?>