<?php

error_reporting(0);
require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");
require_once(dirname(dirname(dirname(__FILE__)))."/global.php");

//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if($verify_result) {//验证成功
	$doc = new DOMDocument();
	if ($alipay_config['sign_type'] == 'MD5') {
		$doc->loadXML($_POST['notify_data']);
	}

	if ($alipay_config['sign_type'] == '0001') {
		$doc->loadXML($alipayNotify->decrypt($_POST['notify_data']));
	}

	if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
		//商户订单号
		$out_trade_no = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
		//支付宝交易号
		$trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
		//交易状态
		$trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
		//交易金额
		$total_fee = $doc->getElementsByTagName( "total_fee" )->item(0)->nodeValue;

		if(!preg_match('/^[0-9]+$/', $out_trade_no)){
			die;
		}
		//验证成功
		//获取支付宝的反馈参数
		
		/*假设：
		sOld_trade_status="0";表示订单未处理；
		sOld_trade_status="1";表示交易成功（TRADE_FINISHED/TRADE_SUCCESS）；
		*/
		if(($trade_status == 'TRADE_FINISHED') ||($trade_status == 'TRADE_SUCCESS') || ($result == 'success') ) {    //交易成功结束
			 //放入订单交易完成后的数据库更新程序代码，请务必保证echo出来的信息只有success
			//为了保证不被重复调用，或重复执行数据库更新程序，请判断该笔交易状态是否是订单未处理状态

			require_once(APP_PATH.'app/public/common.php');
			require_once(LIB_PATH.'ApiPay.class.php');

			$apiPay = new apipay($phpyun,$db,$db_config['def'],'index');
			
			$apiPay->payAll($out_trade_no,$total_fee,'wapalipay');

		}else{
			echo "success";		//其他状态判断。普通即时到帐中，其他状态不用判断，直接打印success。

			//调试用，写文本函数记录程序运行情况是否正常
			//log_result ("这里写入想要调试的代码变量值，或其他运行的结果记录");
		}
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
else {
    echo "fail";
}
?>