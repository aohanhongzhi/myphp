<?php


error_reporting(0);
require_once("class/alipay_notify.php");
require_once("alipay_config.php");
require_once(dirname(dirname(dirname(__FILE__)))."/global.php");



?>
<!DOCTYPE HTML>
<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<?php

$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyReturn();
if($verify_result) {
	
	

	
    

	

	$out_trade_no = $_GET['out_trade_no'];

	

	$trade_no = $_GET['trade_no'];

	if(!preg_match('/^[0-9]+$/', $_GET['out_trade_no'])){
		die;
	}
	
	$trade_status	  = $_GET['trade_status'];

	$dingdan          = $_GET['out_trade_no'];    

    if($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {

		
		require_once(APP_PATH.'app/public/common.php');
		require_once(LIB_PATH.'ApiPay.class.php');

		$apiPay = new apipay($phpyun,$db,$db_config['def'],'index');
		
		$apiPay->payAll($dingdan,$total_fee,'alipay');
		header("Location:".$config['sy_weburl']."/member/index.php?c=paylog");
    }else {
      echo "trade_status=".$_GET['trade_status'];
    }
	echo "支付成功";
}else {

    echo "支付失败";
}
?>
        <title>支付宝纯担保交易接口</title>
	</head>
    <body>
    </body>
</html>