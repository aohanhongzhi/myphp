<?php

include_once (dirname(dirname(dirname(__FILE__)))."/data/api/alipayescow/alipay_data.php");



$alipay_config['partner']		= $alipaydata[sy_alipayid];


$alipay_config['seller_email']	= $alipaydata[sy_alipayemail];


$alipay_config['key']			= $alipaydata[sy_alipaycode];






$alipay_config['sign_type']    = strtoupper('MD5');


$alipay_config['input_charset']= strtolower('utf-8');



$alipay_config['cacert']    = getcwd().'\\cacert.pem';


$alipay_config['transport']    = 'http';
?>