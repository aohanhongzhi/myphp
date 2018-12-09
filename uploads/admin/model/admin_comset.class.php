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
class admin_comset_controller extends adminCommon{	 
	function index_action(){
		$qy_rows=$this->obj->DB_select_all("company_rating","`category`=1 order by sort desc");
		$this->yunset("qy_rows",$qy_rows);
		$this->yuntpl(array('admin/admin_com_config'));
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
		 $this->ACT_layer_msg("配置修改成功！",9,1,2,1);
		}
	}
	function logo_action(){
		if($_POST['submit']){
		    $this->logo_reset("sy_unit_icon",$_POST['sy_unit_icon']);
		    $this->logo_reset("sy_banner",$_POST['sy_banner']);
		    $this->logo_reset("sy_guwen",$_POST['sy_guwen']);
		    $this->logo_reset("sy_member_ewm",$_POST['sy_member_ewm']);
			$this->web_config();
			$this->ACT_layer_msg("会员头像配置设置成功！",9,$_SERVER['HTTP_REFERER'],2,1);
		}
		$this->yuntpl(array('admin/admin_comlogo'));
	}
	function set_action(){
		
		$this->yuntpl(array('admin/admin_integral_com'));
	}
	function rating_action(){
		$qy_rows=$this->obj->DB_select_all("company_rating","`category`=1 order by sort desc");
		$this->yunset("qy_rows",$qy_rows);
		$this->yunset("com_look",@explode(',',$this->config['com_look']));
		$this->yuntpl(array('admin/admin_rating_config'));
	}
	function comspend_action(){
		$this->yuntpl(array('admin/admin_integral_comspend'));
	}
}
?>