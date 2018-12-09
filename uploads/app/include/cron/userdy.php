<?php
/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2017 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
/************
* 计划任务：个人订阅职位提醒
* 
*/
global $db_config,$db;
	//查询个人用户订阅职位提醒短信和邮件
	//查询个人订阅信息
	//判断邮箱和短信有没有认证
	$query=$db->query("SELECT * FROM $db_config[def]subscribe  WHERE  `type`=1  AND  `status`=1 ");
	if($query!=""){
		while($rs = $db->fetch_array($query))
			{		//根据查到邮件里面信息uid去查member里面的uid
			include PLUS_PATH."/com.cache.php";
			$data['jobname']=$comclass_name[$rs['jop_post']];
			$ereg=$db->query("SELECT * FROM $db_config[def]member WHERE `uid`='".$rs['uid']."' AND `usertype`=1 AND `status`=1 ");
	
			while($er=$db>fetch_array($ereg)){
               //根据获取到已知的信息发布短信和号码
                $data['email']=$er['email'];	
                $data['moblie']=$er['moblie'];
				 $noticeM = $this->MODEL('notice');
				 //判断短信是否开通
				if($this->config['sy_email_userdy']=='1'){
					$noticeM->sendEmailType(array("jobname"=>$data['jobname'],"email"=>$er['email'],"type"=>"dy",'uid'=>$er['uid']));
				}
				if($this->config['sy_msg_userdy']=='1'){
					$noticeM->sendSMSType(array("jobname"=>$data['jobname'],"moblie"=>$er['moblie'],"type"=>"dy",'uid'=>$er['uid']));
				}
			}
		
		}
	}else{
		$this->ACT_layer_msg( "暂时没有订阅信息！",8,$_SERVER['HTTP_REFERER']);
	}
	
	
?>