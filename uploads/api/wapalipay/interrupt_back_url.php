<?php
error_reporting(0);

require_once(dirname(dirname(dirname(__FILE__)))."/data/plus/config.php");
if(!($config['sy_wapdomain'])){
	$wapdomain=$config['sy_weburl'].'/'.$config['sy_wapdir'];
}else{
	$wapdomain='http://'.$config['sy_wapdomain'];
}
$Loaction=$wapdomain."/member/index.php?c=paylog";
header("Location: $Loaction\n");exit;
?>