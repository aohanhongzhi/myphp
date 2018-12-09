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
class config_controller extends adminCommon{
	function index_action(){
		if(strpos($this->config['sy_weburl'],'https')!==false){

			$this->config['mapurl'] = 'https://api.map.baidu.com/api?v=2.0&ak='.$this->config['map_key'].'&s=1';

		}else{
			$this->config['mapurl'] = 'http://api.map.baidu.com/api?v=2.0&ak='.$this->config['map_key'];
		}
		$this->yunset("config",$this->config);
		$this->yuntpl(array('admin/admin_web_config'));
	}
	function save_logo_action(){
	    $this->logo_reset("sy_logo",$_POST['sy_logo']);
	    
	    $this->logo_reset("sy_member_logo",$_POST['sy_member_logo']);
	    $this->logo_reset("sy_unit_logo",$_POST['sy_unit_logo']);
	    $this->logo_reset("sy_wap_logo",$_POST['sy_wap_logo']);
	    $this->logo_reset("sy_wap_qcode",$_POST['sy_wap_qcode']);
	    $this->logo_reset("sy_androidu_qcode",$_POST['sy_androidu_qcode']);
	    $this->logo_reset("sy_iosu_qcode",$_POST['sy_iosu_qcode']);
	    
		$this->web_config();
		$this->ACT_layer_msg("网站LOGO配置设置成功！",9,$_SERVER['HTTP_REFERER'],2,1);
	}
	
	function save_action(){
 		if($_POST['config']){
			unset($_POST['config']);
			if($_POST['map_key']){
				if(strpos($this->config['sy_weburl'],'https')!==false){

					$_POST['mapurl'] = 'https://api.map.baidu.com/api?v=2.0&ak='.$_POST['map_key'].'&s=1';

				}else{
					$_POST['mapurl'] = 'http://api.map.baidu.com/api?v=2.0&ak='.$_POST['map_key'];
				}
			}
			foreach($_POST as $key=>$v){
		    	$config=$this->obj->DB_select_num("admin_config","`name`='$key'");
			   if($config==false){
				$this->obj->DB_insert_once("admin_config","`name`='$key',`config`='".$v."'");
			   }else{
				$this->obj->DB_update_all("admin_config","`config`='".$v."'","`name`='$key'");
			   }
		 	}
			
			if($_POST['code_strlength']<5){
			    $this->web_config();
			    $this->layer_msg("网站配置设置成功！",9,1);
			}else{
				$this->layer_msg("验证码字符数不要大于4！",8,1,'');
			}
		 }
	}
	
  function settplcache_action()
  {
    include(CONFIG_PATH."db.data.php");
    include(PLUS_PATH."cache.config.php");
		$modelconfig = $arr_data['modelconfig'];
		
		foreach($modelconfig as $key=>$value){
			$newModel[$key]['value'] = $value;
			$newModel[$key]['cache'] = $cache_config['sy_'.$key.'_cache'];
		}
    $this->yunset('newModel',$newModel);
    $this->yunset('cache_config',$cache_config);
      
		$this->yuntpl(array('admin/admin_tplcache'));
  }
  
  function savetplcache_action()
  {
		if($_POST["config"]){
		    unset($_POST["config"]);
			include(CONFIG_PATH."db.data.php");
			$modelconfig  =  array_keys($arr_data['modelconfig']);
			$config_new = array();
		    foreach($_POST as $key=>$v){
		        $model = explode('_', $key);
		        if (in_array($model[1], $modelconfig) || $model[1]=='index') {
                    $config_new[$key] = $v;
                }
			}
			
			made_web(PLUS_PATH.'cache.config.php',ArrayToString($config_new),'cache_config');
			$this->ACT_layer_msg("模块缓存设置修改成功！",9,"index.php?m=config&c=settplcache",2,1);
		}
  }
}
?>