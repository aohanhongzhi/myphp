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



error_reporting(0);
require_once ("./classes/PayResponseHandler.class.php");
require_once(dirname(dirname(dirname(__FILE__)))."/data/api/tenpay/tenpay_data.php");
require_once(dirname(dirname(dirname(__FILE__)))."/global.php");

$key =$tenpaydata[sy_tenpaycode];


$resHandler = new PayResponseHandler();
$resHandler->setKey($key);


if($resHandler->isTenpaySign()) {

	
	$transaction_id = $resHandler->getParameter("transaction_id");

	
	$sp_billno = $resHandler->getParameter("sp_billno");

	
	$total_fee = $resHandler->getParameter("total_fee");

	
	$pay_result = $resHandler->getParameter("pay_result");
	
	$attach = $resHandler->getParameter("attach");

	if( "0" == $pay_result ) {

		
		
		

		
		

	if(!preg_match('/^[0-9]+$/',$sp_billno))
	{
		die;
	}
	require_once(APP_PATH.'app/public/common.php');
	require_once(LIB_PATH.'ApiPay.class.php');

	$apiPay = new apipay($phpyun,$db,$db_config['def'],'index');
	
	$apiPay->payAll($sp_billno,$total_fee,'tenpay');
	






		
		
		

		
		
		
		header("Location:".$config['sy_weburl']."/member/index.php?c=paylog");
	} else {
		
		echo "<br/>" . "支付失败" . "<br/>";
	}

} else {
	echo "<br/>" . "认证签名失败" . "<br/>";
}



?>