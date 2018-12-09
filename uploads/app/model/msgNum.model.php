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
class msgNum_model extends model{
	function getmsgNum(){
		
		$msgNum = 0;
		$arr=array();
		if($this->uid){
			
			$sysmsgNum = $this->DB_select_num('sysmsg', "`fa_uid`='".$this->uid."' and `remind_status`='0'");
			if($sysmsgNum > 0){
				$msgNum += $sysmsgNum;
				$arr['sysmsgNum']=$sysmsgNum;
			}
			if($this->usertype == 1){
				
				$userid_msg=$this->DB_select_num("userid_msg","`uid`='".$this->uid."' and `is_browse`='1'");
				if($userid_msg > 0){
					$msgNum += $userid_msg;
					$arr['userid_msgNum']=$userid_msg;
				}
				
				$usermsg=$this->DB_select_num("msg","`uid`='".$this->uid."' and `user_remind_status`='0'");
				if($usermsg > 0){
					$msgNum += $usermsg;
					$arr['usermsgNum']=$usermsg;
				}
			}elseif($this->usertype == 2){
				
				$jobApplyNum = $this->DB_select_num('userid_job', "`com_id`=".$this->uid." and `is_browse`= 1 ");
				if($jobApplyNum > 0){
					$msgNum += $jobApplyNum;
					$arr['jobApplyNum']=$jobApplyNum;
				}
				
				$jobAskNum = $this->DB_select_num('msg', "`job_uid`=".$this->uid." and `com_remind_status` = 0");
				if($jobAskNum > 0){
					$msgNum += $jobAskNum;
					$arr['jobAskNum']=$jobAskNum;
				}
			}
		}
		$arr['usertype']=$this->usertype;
		$arr['msgNum']=$msgNum;
		echo json_encode($arr);
	}
	
	function msgNum(){
		$msgNum = 0;
		$arr=array();
		
		$company_job=$this->DB_select_num("company_job","`state`=0");
		if($company_job > 0){
			$msgNum += $company_job;
			$arr['company_job']=$company_job;
		}
		
		$username=$this->DB_select_all("member","`status`=0 and `usertype`='2'","`uid`");
		$uids=array();
		foreach($username as $val){
			$uids[]=$val['uid'];
		}
		$company=$this->DB_select_num("company","`uid` in (".@implode(',',$uids).")");
		if($company > 0){
			$msgNum += $company;
			$arr['company']=$company;
		}
		
		$resume_expect=$this->DB_select_num('resume_expect', "`r_status` = 0");
		if($resume_expect > 0){
			$msgNum += $resume_expect;
			$arr['resume_expect']=$resume_expect;
		}
		
		$appealnum=$this->DB_select_num('member', "`appealtime` > 0 and `appealstate` = 1");
		if($appealnum > 0){
			if(!$this->config['did']||$this->config['did']==0){
				$msgNum += $appealnum;
			}
			$arr['appealnum']=$appealnum;
		}
		
		$company_cert=$this->DB_select_num("company_cert","`status`=0 and type=3");
		if($company_cert > 0){
			$msgNum += $company_cert;
			$arr['company_cert']=$company_cert;
		}
		
		$once_job=$this->DB_select_num("once_job","`status`='0' and `edate`>'".time()."'");
		if($once_job > 0){
			$msgNum += $once_job;
			$arr['once_job']=$once_job;
		}
		
		$company_product=$this->DB_select_num("company_product","`status`=0");
		if($company_product > 0){
			$msgNum += $company_product;
			$arr['company_product']=$company_product;
		}
		$company_news=$this->DB_select_num("company_news","`status`=0");
		if($company_news > 0){
			$msgNum += $company_news;
			$arr['company_news']=$company_news;
		}
		$arr['msgNum']=$msgNum;
		return json_encode($arr);
	}
}
?>