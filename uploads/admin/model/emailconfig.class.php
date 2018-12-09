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
class emailconfig_controller extends adminCommon{
	function index_action(){
		$emailconfig = $this->obj->DB_select_all("admin_email");
		$this->yunset('emailconfig',$emailconfig);
		$this->yuntpl(array('admin/admin_email_config'));
	}
	
	function save_action(){
 		if($_POST['config']){
			
			if($_POST['smtpserver']){
				
				for($i=0;$i<count($_POST['smtpserver']);$i++){
					$smtpserver = $_POST['smtpserver'][$i];
					$smtpuser = $_POST['smtpuser'][$i];
					$smtppass = $_POST['smtppass'][$i];
					$smtpport = $_POST['smtpport'][$i];
					$smtpnick = $_POST['smtpnick'][$i];
					
					if($smtpserver && $smtpuser && $smtppass){
						
						$this->obj->DB_insert_once("admin_email","`smtpserver`='".$smtpserver."',`smtpuser`='".$smtpuser."',`smtppass`='".$smtppass."',`smtpport`='".$smtpport."',`smtpnick`='".$smtpnick."',`default`='1'");
					}
				}
			}
			if($_POST['emailid']){
				foreach($_POST['emailid'] as $value){
					$smtpserver = $_POST['smtpserver_'.$value];
					$smtpuser = $_POST['smtpuser_'.$value];
					$smtppass = $_POST['smtppass_'.$value];
					$smtpnick = $_POST['smtpnick_'.$value];
					$smtpnum = $_POST['smtpnum_'.$value];
					$smtpport = $_POST['smtpport_'.$value];
					$default = $_POST['default_'.$value];

					if($smtpserver && $smtpuser && $smtppass){

						$this->obj->DB_update_all("admin_email","`smtpserver`='".$smtpserver."',`smtpuser`='".$smtpuser."',`smtppass`='".$smtppass."',`smtpport`='".$smtpport."',`smtpnick`='".$smtpnick."',`default`='".$default."'","`id`='".$value."'");
					}
				}
			}
			$this->get_cache();
			$this->ACT_layer_msg("邮件服务器设置成功！",9,1,2,1);
		
		}
	}
	
	function tpl_action(){
		$this->yuntpl(array('admin/admin_email_tpl'));
	}
	
	function tplsave_action(){
	    if($_POST['config']){
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
	        $this->ACT_layer_msg( "邮箱模板配置设置成功！",9,1,2,1);	    
	    }
	}
	
	function settpl_action(){
		extract($_POST);
		 if($config){
		    $config=$this->obj->DB_select_num("templates","`name`='$name'");
		    $content = str_replace("amp;nbsp;","nbsp;",$content);
		   if($config==false){
				$this->obj->DB_insert_once("templates","name='$name',`title`='$title',`content`='".$content."'");
		   }else{
				$this->obj->DB_update_all("templates","`title`='$title',`content`='".$content."'","`name`='$name'");
		   }
			$this->ACT_layer_msg( "模版配置设置成功！",9,$_SERVER['HTTP_REFERER'],2,1);
		 }
		include(CONFIG_PATH."db.tpl.php");
		$this->yunset("arr_tpl",$arr_tpl);
		$name=$_GET["name"];
		$row=$this->obj->DB_select_once("templates","`name`='$name'");
		$this->yunset("row",$row);
		$this->yuntpl(array('admin/admin_esettpl'));
	}
	function ceshi_action(){
 		if($_POST["ceshi_email"]){
			
			
			$emailData['email'] = $_POST["ceshi_email"];
			$emailData['subject'] = $this->config[sy_webname]." - 测试邮件";
			$emailData['content'] = "恭喜你，该邮件帐户可以正常使用<br> ".$this->config['sy_webname']."- Powered by PHPYun.";
      $notice = $this->MODEL('notice');
			$sendid = $notice->sendEmail($emailData);

			if($sendid['status'] != -1){
				$data['msg']='测试发送成功！';
				$data['type']='9';
			}else{
				$data['msg']='测试发送失败！' . $sendid['msg'];
				$data['type']='8';
			}
			echo json_encode($data);
		 }
	}
	function delconfig_action(){
 		if($_POST["id"]){
			
			$emailConfig = $this->obj->DB_select_once("admin_email","`id`='".(int)$_POST["id"]."'");
			
			$num = $this->obj->DB_select_num("admin_email","`default`='1'");

			if($emailConfig['default']=='1' && $num<2){
				
				$data['msg']='请至少保留一组可用邮箱！';
				$data['type']='8';
				
			}else{
				$this->obj->DB_delete_all("admin_email","`id`='".(int)$_POST["id"]."'");
				$data['msg']='删除成功！';
				$data['type']='9';
				$this->get_cache();
			}
			echo json_encode($data);
		 }
	}
	function get_cache(){
		include(LIB_PATH."cache.class.php");
		$cacheclass= new cache(PLUS_PATH,$this->obj);
		$makecache=$cacheclass->emailconfig_cache("emailconfig.cache.php");
	}
}

?>