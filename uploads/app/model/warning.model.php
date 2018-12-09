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
class warning_model extends model{
	function warning($type){
		$time=strtotime(date("Y-m-d"));
		if($type==1){
			$num=$this->DB_select_num("company_job","`uid`='".$this->uid."' and `sdate`>'".$time."'");
			if($num>=$this->config['warning_addjob']){
				$this->send_warning($type);
			}
		}elseif($type==2){
			$num=$this->DB_select_num("down_resume","`comid`='".$this->uid."' and `downtime`>'".$time."'");
			if($num>=$this->config['warning_downresume']){
				$this->send_warning($type);
			}
		}elseif($type==3){
			$num=$this->DB_select_num("resume_expect","`uid`='".$this->uid."' and `ctime`>'".$time."'");
			if($num>=$this->config['warning_addresume']){
				$this->send_warning($type);
			}
		}elseif($type==4){
			$this->send_warning($type);
		}else if($type==5&&$this->config['sy_hour_msgnum']>0){
			$ip=fun_ip_get();
			$time=time()-3600; 
			$num=$this->DB_select_num("moblie_msg","`ctime`>'".$time."'"); 
			$msg="系统一小时内已发送".$num."条短信！";
			if($num>=$this->config['sy_hour_msgnum']){
				$this->send_warning($type,$msg);
			} 
		}
	}
	
	function send_warning($type,$emailcoment=''){
		$time=strtotime(date("Y-m-d"));
		$row=$this->DB_select_once("warning","`type`='".$type."' and `uid`='".$this->uid."' and `ctime`>='".$time."'");
		if(empty($row)){
			$this->DB_insert_once("warning","`type`='".$type."',`uid`='".$this->uid."',`ctime`='".time()."'");
			$member=$this->DB_select_once("member","`uid`='".$this->uid."'","email");
      
      require_once('notice.model.php');
      $notice = new notice_model($this->db,$this->def,array('uid'=>$this->uid,'username'=>$this->username,'usertype'=>$this->usertype));
      require_once('cookie.model.php');
      $cookie = new cookie_model($this->db,$this->def,array('uid'=>$this->uid,'username'=>$this->username,'usertype'=>$this->usertype));
      
			if($type=="1"){
				$emailcoment="用户：【".$this->username."】发布职位超出规定数目，请检查是否有问题";
				if($this->config['warning_addjob_type']=="1"){
					$this->DB_update_all("company_job","`r_status`='2'","`uid`='".$this->uid."'");
					$this->DB_update_all("company","`r_status`='2'","`uid`='".$this->uid."'");
					$this->DB_update_all("member","`status`='2',`lock_info`='发布职位超出规定数目'","`uid`='".$this->uid."'");
					$notice->sendEmailType(array("email"=>$member['email'],"uid"=>$this->uid,"name"=>$this->username,"lock_info"=>'发布职位超出规定数目',"type"=>"lock"));
					$cookie->unset_cookie();
				}
			}elseif($type=="2"){
				$emailcoment="用户：【".$this->username."】下载简历超出规定数目，请检查是否有问题";
				if($this->config['warning_downresume_type']=="1"){
					$this->DB_update_all("company_job","`r_status`='2'","`uid`='".$this->uid."'");
					$this->DB_update_all("company","`r_status`='2'","`uid`='".$this->uid."'");
					$this->DB_update_all("member","`status`='2',`lock_info`='下载简历超出规定数目'","`uid`='".$this->uid."'");
					$notice->sendEmailType(array("email"=>$member['email'],"uid"=>$this->uid,"name"=>$this->username,"lock_info"=>'下载简历超出规定数目',"type"=>"lock"));
					$cookie->unset_cookie();
				}
			}elseif($type=="3"){
				$emailcoment="用户：【".$this->username."】简历发布超出规定数目，请检查是否有问题";
				if($this->config['warning_addresume_type']=="1"){
			 		$this->DB_update_all("member","`status`='2',`lock_info`='简历发布超出规定数目'","`uid`='".$this->uid."'");
			 		$this->DB_update_all("resume","`r_status`='2'","`uid`='".$this->uid."' ");
			 		$this->DB_update_all("resume_expect","`r_status`='2'","`uid`='".$this->uid."' ");
					$notice->sendEmailType(array("email"=>$member['email'],'uid'=>$this->uid,'name'=>$this->username,"lock_info"=>'简历发布超出规定数目',"type"=>"lock"));
					$cookie->unset_cookie();
				}
			}elseif($type=="4"){
				$emailcoment="用户：【".$this->username."】充值超出规定金额，请检查是否有问题";
				if($this->config['warning_recharge_type']=="1"){
					$this->DB_update_all("company_job","`r_status`='2'","`uid`='".$this->uid."'");
					$this->DB_update_all("company","`r_status`='2'","`uid`='".$this->uid."'");
					$this->DB_update_all("member","`status`='2',`lock_info`='充值超出规定金额'","`uid`='".$this->uid."'");
					$notice->sendEmailType(array("email"=>$member['email'],"uid"=>$this->uid,"name"=>$this->username,"lock_info"=>'充值超出规定金额',"type"=>"lock"));
					$cookie->unset_cookie();
				}
			}else if($type=='5'&&$this->config['warning_close_msg']==1){ 
				$this->DB_update_all("admin_config","`config`='2'","`name`='sy_msg_isopen'");
				
			}
			
			$emailData['email'] = $this->config['sy_webemail'];
			$emailData['subject'] = "预警提醒";
			$emailData['content'] = $emailcoment;
			$notice->sendEmail($emailData);

		}
	}
}
?>