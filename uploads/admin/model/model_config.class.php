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
class model_config_controller extends adminCommon{
	function index_action(){
		include(CONFIG_PATH."db.data.php");
		$modelconfig = $arr_data['modelconfig'];
		$config=$this->obj->DB_select_all('admin_config');
        foreach($config as $v){
            $config_new[$v[0]]=$v[1];
        }
		foreach($modelconfig as $key=>$value){
			$newModel[$key]['value'] = $value;
			$newModel[$key]['web'] = $config_new['sy_'.$key.'_web'];
			$newModel[$key]['domain'] = $config_new['sy_'.$key.'domain'];
			$newModel[$key]['dir'] = $config_new['sy_'.$key.'dir'];
			$newModel[$key]['ssl'] = $config_new['sy_'.$key.'ssl'];
		}
		
    $this->yunset('newModel',$newModel);
    $this->yunset('config',$config_new);
		$this->yuntpl(array('admin/admin_model_config'));
	}
	function save_action(){
 		if($_POST["config"]){
		    unset($_POST["config"]);
			
			include(CONFIG_PATH."db.data.php");
			$modelKey  =  array_keys($arr_data['modelconfig']);
			foreach($modelKey as $key=>$value){
				
				if($_POST['sy_'.$value.'_web']=='1'){
					
					$setSql = "`display`='1'";
				}else{
					$setSql = "`display`='0'";
				}
				$this->obj->DB_update_all("navigation",$setSql,"`config`='".$value."'");
					
				
				if(!$_POST['sy_'.$value.'ssl'] || $_POST['sy_'.$value.'domain']==''){
					$_POST['sy_'.$value.'ssl'] = '0';
				}
			}
			
		    foreach($_POST as $key=>$v){
				
		        $config=$this->obj->DB_select_num("admin_config","`name`='$key'");
			    if($config>0){
					$this->obj->DB_update_all("admin_config","`config`='".$v."'","`name`='$key'");
				    
			  	}else{
					$this->obj->DB_insert_once("admin_config","`name`='$key',`config`='".$v."'");
				}
				
				
			}
			$this->navcache();
			$this->web_config();
			$this->ACT_layer_msg("模块设置修改成功！",9,"index.php?m=model_config",2,1);
		}
	}
	function setnav_action(){
 		if($_GET["config"]){

			$type=$this->obj->DB_select_all("navigation_type");
	    
		    $nav = $this->obj->DB_select_once("navigation", " `config`='".$_GET["config"]."'");
			if(!$nav){
			
				$nav = array('name'=>$_GET['name'],'config'=>$_GET["config"],'nid'=>'1');
			}
			$this->yunset("type",$type);
			$this->yunset('types',$nav);
			$this->yuntpl(array('admin/admin_model_config_nav'));
		}
		if($_POST['config']){
			
			$value.="`nid`='".$_POST['nid']."',";
			$value.="`eject`='".$_POST['eject']."',";
			$value.="`display`='".$_POST['display']."',";
			$value.="`name`='".$_POST['name']."',";
			$value.="`url`='".$this->config['sy_'.$_POST['config'].'dir']."',";
			$value.="`sort`='".$_POST['sort']."',";
			$value.="`model`='".$_POST['model']."',";
			$value.="`bold`='".$_POST['bold']."',";
			$value.="`type`='1',";
			$value.="`config`='".$_POST['config']."'";

			if($_POST['id']){
				

				$nbid=$this->obj->DB_update_all("navigation",$value,"`id`='".$_POST['id']."'");
				$this->navcache();
			}else{
				$nbid=$this->obj->DB_insert_once("navigation",$value);
				$this->navcache();
			}
			$this->layer_msg('导航设置成功！',9);
			
		}
	}

	function setseo_action(){
 		if($_GET["config"]){
			include(CONFIG_PATH."db.data.php"); 
			$this->yunset("arr_data",$arr_data);
			
			include PLUS_PATH."/domain_cache.php";
			$Dname[0] = '总站';
			if(is_array($site_domain)){
				foreach($site_domain as $key=>$value){
					$Dname[$value['id']]  =  $value['webname'];
				}
			}
			$this->yunset("Dname", $Dname);
			
		    $seo = $this->obj->DB_select_all("seo","`seomodel`='".$_GET["config"]."'");
			
			$this->yunset('seo',$seo);
			$this->yuntpl(array('admin/admin_model_config_seo'));
		}
		if($_POST['id']){
			
			$value.="`seoname`='".$_POST['seoname']."',";
			$value.="`ident`='".$_POST['ident']."',";
			$value.="`did`='".$_POST['did']."',";
			$value.="`title`='".$_POST['title']."',";
			$value.="`keywords`='".$_POST['keywords']."',";
			$value.="`description`='".$_POST['description']."',";
			$value.="`php_url`='".$_POST['php_url']."',";
			$value.="`rewrite_url`='".$_POST['rewrite_url']."'";

			$nbid=$this->obj->DB_update_all("seo",$value,"`id`='".$_POST['id']."'");
			$this->seocache();
			
			$this->layer_msg('SEO设置成功！',9);
		}
	}
	function getseo_action(){
		if($_POST['id']){
			
			include PLUS_PATH."/domain_cache.php";
			$Dname[0] = '总站';
			if(is_array($site_domain)){
				foreach($site_domain as $key=>$value){
					$Dname[$value['id']]  =  $value['webname'];
				}
			}
			$this->yunset("Dname", $Dname);
			
		    $seo = $this->obj->DB_select_once("seo","`id`='".$_POST["id"]."'");
			$data['seoname'] = $seo['seoname'];
			$data['ident'] = $seo['ident'];
			$data['rewrite_url'] = $seo['rewrite_url'];
			$data['php_url'] = $seo['php_url'];
			$data['title'] = $seo['title'];
			$data['keywords'] = $seo['keywords'];
			$data['description'] = $seo['description'];
			$data['did'] = $seo['did'];
			if($data['did']){
				$data['didname'] = $Dname[$seo['did']];
			}else{
				$data['didname'] = '总站';
			}
			
			echo json_encode($data);
		}
	
	}
	function  navcache(){
		include(LIB_PATH."cache.class.php");
		$cacheclass= new cache(PLUS_PATH,$this->obj);
		$makecache=$cacheclass->menu_cache("menu.cache.php");
	}
	function  seocache(){
		include(LIB_PATH."cache.class.php");
		$cacheclass= new cache(PLUS_PATH,$this->obj);
		$makecache=$cacheclass->seo_cache("seo.cache.php");
	}
}

?>