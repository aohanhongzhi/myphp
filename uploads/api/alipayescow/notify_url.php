<?php


error_reporting(0);
require_once("class/alipay_notify.php");
require_once("alipay_config.php");
require_once(dirname(dirname(dirname(__FILE__)))."/global.php");

$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if($verify_result) {
	if(!preg_match('/^[0-9]+$/', $_POST['out_trade_no'])){
		die;
	}
    
    
    $dingdan           = $_POST['out_trade_no'];	    
	if($_POST['trade_status'] == 'WAIT_BUYER_PAY') {
	

        echo "success";

    }else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {

		
		
		require_once(APP_PATH.'app/public/common.php');
		require_once(LIB_PATH.'ApiPay.class.php');

		$apiPay = new apipay($phpyun,$db,$db_config['def'],'index');
		
		$apiPay->payAll($dingdan,$total_fee,'alipay');

    }
	else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS') {

        echo "success";
    }
	else if($_POST['trade_status'] == 'TRADE_FINISHED') {




        echo "success";


    }
    else {
		
        echo "success";

    }


}
else {

    echo "fail";

}
?>