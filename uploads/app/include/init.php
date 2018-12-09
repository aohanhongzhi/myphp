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



if(isset($_GET['yunurl']) && $_GET['yunurl']){
	$var=@explode('-',str_replace('/','-',$_GET['yunurl']));
	foreach($var as $p){
	
		$param=@explode('_',$p);
		$_GET[$param[0]]=$param[1];
	}
	unset($_GET['yunurl']);
}



if(isset($_GET['c']) && $_GET['c'] && !preg_match('/^[a-zA-Z0-9_]+$/',$_GET['c'])){
	$_GET['c'] = 'index';
}
if(isset($_GET['a']) && $_GET['a'] && !preg_match('/^[a-zA-Z0-9_]+$/',$_GET['c'])){
	$_GET['a'] = 'index';
}



global $ModuleName,$DirName;

$Loaction = wapJump($config);
if (!empty($Loaction)){

	header('Location: '.$Loaction);exit;
}



include(PLUS_PATH.'cache.config.php');       

if($config['webcache']=='1'){
	
	if(isMobileUser()){
		if($cache_config['sy_'.$_GET['c'].'_cache']=='1'){
			include_once(LIB_PATH.'web.cache.php');
			$cache=new Phpyun_Cache('./cache',DATA_PATH,$config['webcachetime']);
			$cache->read_cache();
		}
		
	}else{
		
		if($cache_config['sy_'.$ModuleName.'_cache']=='1' && $_GET['c']!='clickhits'){
			include_once(LIB_PATH.'web.cache.php');
			$cache=new Phpyun_Cache('./cache',DATA_PATH,$config['webcachetime']);
			$cache->read_cache();
		}
	}
	
}


$ControllerName = isset($_GET['c']) ? $_GET['c'] : '';

$ActionName = isset($_GET['a']) ? $_GET['a'] : '';

if($ControllerName=='')	$ControllerName='index';

if($ActionName=='')	$ActionName = 'index';







if(isset($config['sy_'.$ModuleName.'_web']) && $config['sy_'.$ModuleName.'_web']==2){
    
    header('Location: '.Url("error"));exit;
}



$ControllerPath=APP_PATH.'app/controller/'.$ModuleName.'/';
require(APP_PATH.'app/public/common.php');


if(in_array(strtolower($ModuleName) , array('siteadmin','wapadmin'))){	
    include(PLUS_PATH."/admindir.php");
    if($admindir){
        require(APP_PATH.$admindir.'/adminCommon.class.php');
    }else{
        require(APP_PATH.'admin/adminCommon.class.php');
    }
}

if(file_exists($ControllerPath.$ModuleName.'.controller.php')){	
	require($ControllerPath.$ModuleName.'.controller.php');
}


if(file_exists($ControllerPath.$ControllerName.'.class.php')){
	
    require($ControllerPath.$ControllerName.'.class.php');
}else{
	
	
    $ActionName=$ControllerName;$ControllerName='index';
    if(!file_exists($ControllerPath.$ControllerName.'.class.php')){
        
        header('Location: '.Url("error"));exit;
    }else{

        require($ControllerPath.'index.class.php');
    }
}

if($ModuleName=='siteadmin'){$model='admin';}elseif($ModuleName=='wap'){$model='wap';}elseif($ModuleName=='wapadmin'){$model='wapadmin';}else{$model='index';}


$conclass=$ControllerName.'_controller';

$actfunc=$ActionName.'_action';

$views=new $conclass($phpyun,$db,$db_config['def'],$model,$ModuleName);
$views->m=$ModuleName;
if(!method_exists($views,$actfunc)){
	$views->DoException();
}
$views->$actfunc();

if($cache){

	$cache->CacheCreate();
}

?>