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


include(dirname(__FILE__).'/global.php');

//伪静态操作statr
if(isset($_GET['yunurl']) && $_GET['yunurl']){

	$var=@explode('-',str_replace('/','-',$_GET['yunurl']));
	foreach($var as $p){
		$param=@explode('_',$p);
		$_GET[$param[0]]=$param[1];
	}
	unset($_GET['yunurl']);

}

//伪静态操作end

if(isset($_GET['m']) && $_GET['m'] && !preg_match('/^[0-9a-zA-Z\_]*$/',$_GET['c'])){
	$_GET['m'] = 'index';
}

$ModuleName = isset($_GET['m']) ? $_GET['m'] : '';
if($ModuleName=='')	$ModuleName='index';
//默认情况下，调用app/controller下与当前目录名相同的模块
//如当前目录名为ask，则默认调用的是app/controller/ask/下的控制器
//若需要调用与当前目录名不同的模块，则请修改此处的$ModuleName
//$ModuleName='job'，则此时将调用app/controller/job/下的控制器

include(LIB_PATH.'init.php');


?>