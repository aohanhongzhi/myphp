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
class admin_turl_controller extends adminCommon{
	function index_action()
	{
		$this->yuntpl(array('admin/admin_turl'));
	}


	function set_action(){
		if($_POST['url']){
			
			$ch=curl_init();
			curl_setopt($ch,CURLOPT_URL,"http://dwz.cn/create.php");
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			$data=array('url'=>$_POST['url']);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
			$strRes=curl_exec($ch);
			curl_close($ch);
			$arrResponse=json_decode($strRes,true);
			
			if($arrResponse['status']!=0)
			{
				
				echo $arrResponse['err_msg']."\n";
			}else{
				
				echo $arrResponse['tinyurl'];
			}
		
		}else{
			
			echo '请输入需要生成的网址';
		}
	
	}
	function save_action()
	{
 		if($_POST['config'])
 		{
		    unset($_POST['config']);
		    foreach($_POST as $key=>$v)
		    {
		        $config=$this->obj->DB_select_num("admin_config","`name`='".$key."'");
			    if($config==false)
			    {
				    $this->obj->DB_insert_once("admin_config","`name`='".$key."',`config`='".$v."'");
			  	}else{
					$this->obj->DB_update_all("admin_config","`config`='".$v."'","`name`='".$key."'");
				}
			}
			$this->web_config();
			$this->ACT_layer_msg("短网址APPKEY设置成功！",9,1,2,1);
		}
	}
	
}
?>