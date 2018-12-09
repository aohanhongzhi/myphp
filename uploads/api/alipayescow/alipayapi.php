<!DOCTYPE html PUBLIC "-
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<title>支付宝纯担保交易接口接口</title>
</head>
<?php


require_once("alipay.config.php");
require_once("lib/alipay_submit.class.php");



        
        $payment_type = "1";
        
        
        $notify_url = "http://商户网关地址/create_partner_trade_by_buyer-PHP-utf-8/notify_url.php";
        
        
        $return_url = "http://商户网关地址/create_partner_trade_by_buyer-PHP-utf-8/return_url.php";
        
        
        $out_trade_no = $_POST['WIDout_trade_no'];
        
        
        $subject = $_POST['WIDsubject'];
        
        
        $price = $_POST['WIDprice'];
        
        
        $quantity = "1";
        
        
        $logistics_fee = "0.00";
        
        
        $logistics_type = "EXPRESS";
        
        
        $logistics_payment = "SELLER_PAY";
        
        
        $body = $_POST['WIDbody'];
        
        $show_url = $_POST['WIDshow_url'];
        
        
        $receive_name = $_POST['WIDreceive_name'];
        
        
        $receive_address = $_POST['WIDreceive_address'];
        
        
        $receive_zip = $_POST['WIDreceive_zip'];
        
        
        $receive_phone = $_POST['WIDreceive_phone'];
        
        
        $receive_mobile = $_POST['WIDreceive_mobile'];
        





$parameter = array(
		"service" => "create_partner_trade_by_buyer",
		"partner" => trim($alipay_config['partner']),
		"seller_email" => trim($alipay_config['seller_email']),
		"payment_type"	=> $payment_type,
		"notify_url"	=> $notify_url,
		"return_url"	=> $return_url,
		"out_trade_no"	=> $out_trade_no,
		"subject"	=> $subject,
		"price"	=> $price,
		"quantity"	=> $quantity,
		"logistics_fee"	=> $logistics_fee,
		"logistics_type"	=> $logistics_type,
		"logistics_payment"	=> $logistics_payment,
		"body"	=> $body,
		"show_url"	=> $show_url,
		"receive_name"	=> $receive_name,
		"receive_address"	=> $receive_address,
		"receive_zip"	=> $receive_zip,
		"receive_phone"	=> $receive_phone,
		"receive_mobile"	=> $receive_mobile,
		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
);


$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
echo $html_text;

?>
</body>
</html>