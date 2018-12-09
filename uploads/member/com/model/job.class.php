<?php
/* *
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2018 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class job_controller extends company{
	function index_action(){
		$this->public_action();
		$this->company_satic();
		$urlarr=array("c"=>"job","page"=>"{{page}}");
		$where="`uid`='".$this->uid."' ";
		
		if($_GET['keyword']){
			$where .= " and `name` like '%".trim($_GET['keyword'])."%'";
			$urlarr['keyword']=$_GET['keyword'];
		}
		
		if($_GET['w']==4){
			$where .= " and `status`='1'";
			$urlarr['w']=$_GET['w'];
		}elseif($_GET["w"]==1){
			$where .= "  and `status`='0' and `state`='1'";
			$urlarr['w']=1;
		}elseif($_GET["w"]==5){
			$where .= "  and 1";
			$urlarr['w']=5;
		}else{
			$where .= " and `state`='".$_GET['w']."'";
			$urlarr['w']=$_GET['w'];
		}
		$pageurl=Url('member',$urlarr);
		$rows=$this->get_page("company_job",$where." ORDER BY `lastupdate` DESC",$pageurl,'10');
		
		if(is_array($rows) && !empty($rows)){
			$jobids=array();
			foreach($rows as $v){
				$jobids[]=$v['id'];
			}
			$jobnum=$this->obj->DB_select_all("userid_job","`job_id` in(".pylode(',',$jobids).") and `com_id`='".$this->uid."' GROUP BY `job_id`","`job_id`,count(`id`) as `num`");
			
			foreach($rows as $k=>$v){
				if($v['autotime']>time()){
					$rows[$k]['autodate']=date("Y-m-d",$v['autotime']);
				}
				if($v['xsdate']>time()){
					$rows[$k]['xs']=1;
				}
				$rows[$k]['jobnum']=0;
				foreach($jobnum as $val){
					if($v['id']==$val['job_id']){
						$rows[$k]['jobnum']=$val['num'];
					}
				}
				$rows[$k]['type']=1;
			}
		}
		
		$this->yunset("rows",$rows);


		
		
		$audit=$this->obj->DB_select_all("company_job","`uid`='".$this->uid."'","`status`,`state`");
		$w0 = $w1 = $w2 =$w3 =$w4=$w5=0;
		if(is_array($audit)){
			foreach($audit as $value){
				
				if($value['state']=='0'){
					$w0 +=1;
				}
				if($value['status']=='0' && $value['state']=='1'){
					$w1 +=1;
				}
				
				if($value['state']=='3'){
					$w3 +=1;
				}
				if($value['status']=='1'){
					$w4 +=1;
				}
				$w5 +=1;
				
				
			}
		}
		$this->yunset(array('w0'=>$w0,'w1'=>$w1,'w3'=>$w3,'w4'=>$w4,'w5'=>$w5));
		$this->yunset("audit",$w0);
		
		$this->yunset("js_def",3);
		if(intval($_GET['w'])==1){
			$this->com_tpl('joblist');
		}else{
			$this->com_tpl('job');
		}
	}
	
	function opera_action(){
		$this->job();
	}

	function buyJob_action(){
 		if($_POST){
  			$M=$this->MODEL('compay');
			if ($_POST['jobautoids']){
				$return = $M->buyAutoJob($_POST);
			}elseif ($_POST['zdjobid']){
				$return = $M->buyZdJob($_POST);
			}elseif ($_POST['recjobid']){
				$return = $M->buyRecJob($_POST);
			}elseif ($_POST['recpartid']){
				$return = $M->buyRecPart($_POST);
			}elseif ($_POST['ujobid']){
				$return = $M->buyUrgentJob($_POST);
			}elseif ($_POST['sxjobid']){
				$return = $M->buyRefreshJob($_POST);
			}elseif ($_POST['sxpartid']){
				$return = $M->buyRefreshPart($_POST);
			}elseif ($_POST['issuejob']){
				$return = $M->buyIssueJob($_POST);
			}elseif ($_POST['issuepart']){
				$return = $M->buyIssuePart($_POST);
			}elseif ($_POST['invite']){
				$return = $M->buyInviteResume($_POST);
			}   

			if($return['order']['order_id'] && $return['order']['id']){
				
				echo json_encode(array('error'=>0,'orderid'=>$return['order']['order_id'],'id'=>$return['order']['id']));
			}else{
				
				echo json_encode(array('error'=>1,'msg'=>$return['error']));
			}
		}else{
			echo json_encode(array('error'=>1,'msg'=>'参数错误，请重试！'));
		}
	}

	function dkBuy_action(){
 		if($_POST){
   			$M=$this->MODEL('jfdk');
			if ($_POST['jobautoids']){
				$return = $M->buyAutoJob($_POST);
			}elseif($_POST['zdjobid']){		
				$return = $M->buyZdJob($_POST);
			}elseif ($_POST['recjobid']){
				$return = $M->buyRecJob($_POST);
			}elseif ($_POST['recpartid']){
				$return = $M->buyRecPart($_POST);
			}elseif ($_POST['ujobid']){
				$return = $M->buyUrgentJob($_POST);
			}elseif ($_POST['sxjobid']){
				$return = $M->buyRefreshJob($_POST);
			}elseif ($_POST['sxpartid']){
				$return = $M->buyRefreshPart($_POST);
			}elseif ($_POST['issuejob']){
				$return = $M->buyIssueJob($_POST);
			}elseif ($_POST['issuepart']){
				$return = $M->buyIssuePart($_POST);
			}elseif ($_POST['invite']){
				$return = $M->buyInviteResume($_POST);
			}    
			if($return['status']==1){
				
				echo json_encode(array('error'=>0,'msg'=>$return['msg']));
			}else{
				
				echo json_encode(array('error'=>1,'msg'=>$return['error'],'url'=>$return['url']));
			}
		}else{
			echo json_encode(array('error'=>1,'msg'=>'参数错误，请重试！'));
		}
	}

	function refresh_action(){
		
		$nid=$this->obj->DB_update_all("company_job","`lastupdate`='".time()."'","`uid`='".$this->uid."' and `id`='".(int)$_POST['id']."'");
		if($nid){
			$this->obj->DB_update_all("company","`lastupdate`='".time()."'","`uid`='".$this->uid."'");
			echo 1;
		}
	}
	
	
	function refresh_job_action(){
		if($_POST){
			$M=$this->MODEL('comtc');
 			$return = $M->refresh_job($_POST);
 			if($return['status']==1){
				
				echo json_encode(array('error'=>1,'msg'=>$return['msg']));
			}else if($return['status']==2){
				
				echo json_encode(array('error'=>2,'msg'=>$return['msg']));
			}else{
				
				echo json_encode(array('error'=>3,'msg'=>$return['msg'],'url'=>$return['url']));
			}
		}else{
			echo json_encode(array('error'=>3,'msg'=>'参数错误，请重试！'));
		}
	}

	 
}
?>