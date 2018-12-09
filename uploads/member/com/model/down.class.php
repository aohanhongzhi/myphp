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
class down_controller extends company{
	function index_action(){ 
		$where="`comid`='".$this->uid."'";
		if(trim($_GET['keyword'])){
			$resume=$this->obj->DB_select_alls("resume","resume_expect","a.uid=b.uid and a.`r_status`<>'2' and a.`name` like '%".trim($_GET['keyword'])."%'","a.`name`,a.`uid`,a.`sex`,a.`edu`,b.`job_classid`,a.`exp`,b.`minsalary`,b.`maxsalary`");
			
			if(is_array($resume)){
				foreach($resume as $v){
					$uid[]=$v['uid'];
				}
			}
			$where.=" and `uid` in (".pylode(',',$uid).")";
			$urlarr['keyword']=trim($_GET['keyword']);
		}
		$this->public_action();
		$urlarr['c']='down';
		$urlarr["page"]="{{page}}";
		$pageurl=Url('member',$urlarr);
		$rows=$this->get_page("down_resume","$where order by id desc",$pageurl,"10");
		if(is_array($rows)&&$rows){ 

				foreach($rows as $v){
					$uid[]=$v['uid'];
					$eid[]=$v['eid'];
				}
			$resume=$this->obj->DB_select_alls("resume","resume_expect","a.uid=b.uid and a.`r_status`<>'2' and a.uid in (".@implode(",",$uid).") and b.id in (".@implode(",",$eid).")","a.`name`,a.`uid`,a.`exp`,b.`id`,b.`minsalary`,b.`maxsalary`,b.`job_classid`,b.`height_status`");
			$userid_msg=$this->obj->DB_select_all("userid_msg","`fid`='".$this->uid."' and `uid` in (".pylode(",",$uid).")","uid");
			if(is_array($resume)){
				include(PLUS_PATH."user.cache.php");
				include(PLUS_PATH."job.cache.php"); 
				foreach($rows as $key=>$val){
					foreach($resume as $va){
						if($val['eid']==$va['id']){ 
							$rows[$key]['name']=$va['name'];
							$rows[$key]['exp']=$userclass_name[$va['exp']];
							$rows[$key]['minsalary']=$va['minsalary'];
							$rows[$key]['maxsalary']=$va['maxsalary'];
							if($va['job_classid']!=""){
								$job_classid=@explode(",",$va['job_classid']);
								$rows[$key]['jobname']=$job_name[$job_classid[0]];
							}
						}
					}
					foreach($userid_msg as $va){
						if($val['uid']==$va['uid']){
							$rows[$key]['userid_msg']=1;
						}
					}
				}
			}
		}
		$JobM=$this->MODEL("job");
		$company = $this->obj->DB_select_once("company","`uid`='".$this->uid."'","`linktel`,`linkphone`,`linkman`,`address`");
		$company_job=$JobM->GetComjobList(array("uid"=>$this->uid,"state"=>1,"r_status"=>1,"status"=>0),array("field"=>"`name`,`id`,`is_link`,`link_type`"));
		if(is_array($company_job)){
			foreach($company_job as $k=>$v){
				$ids[]=$v['id'];
			}
			$ids = pylode(',',$ids);
			$joblink = $this->obj->DB_select_all("company_job_link","`jobid` in ($ids)","`uid`,`jobid`,`link_type`,`link_man`,`link_moblie`");
				foreach($company_job as $k=>$v){
					if($company_job[$k]['is_link']=='1'){
						if($company_job[$k]['link_type']=='1'){
 							$company_job[$k]['link_man']=$company['linkman']; 
							$company_job[$k]['link_moblie']=$company['linktel']?$company['linktel']:$company['linkphone']; 
						}else if($company_job[$k]['link_type']=='2'){
							foreach($joblink as $val){
								if($v['id']==$val['jobid']){
									$company_job[$k]['link_man']=$val['link_man']; 
									$company_job[$k]['link_moblie']=$val['link_moblie']; 
								}
							}
						}
					}else if($joblist[$k]['is_link']=='0'){
						$joblist[$k]['link_man']=""; 
						$joblist[$k]['link_moblie']=""; 
					}
				}
		}
		$this->yunset("company_job",$company_job);
		$this->company_satic();
		$this->yunset("rows",$rows); 
		$this->yunset("js_def",5);
		$this->com_tpl('down');
	}
	function del_action(){
		if($_POST['delid'] || $_GET['id']){
			if($_GET['id']){
				$id=(int)$_GET['id'];
				$layer_type='0';
			}else{
				$id=pylode(",",$_POST['delid']);
				$layer_type='1';
			}
			$nid=$this->obj->DB_delete_all("down_resume","`comid`='".$this->uid."' and `id` in (".$id.")"," ");
			if($nid){
				$this->obj->member_log("删除已下载简历人才",3,3);
				$this->layer_msg('删除成功！',9,$layer_type,"index.php?c=down");
			}else{
				$this->layer_msg('删除失败！',8,$layer_type,"index.php?c=down");
			}
		}
	}
	function report_action(){
		if($_POST['submit']){
			if($_POST['reason']==""){
				$this->ACT_layer_msg("举报内容不能为空！",8,$_SERVER['HTTP_REFERER']);
			}
			$data['c_uid']=(int)$_POST['r_uid'];
			$data['inputtime']=mktime();
			$data['p_uid']=$this->uid;
			$data['did']=$this->userid;
			$data['usertype']=(int)$this->usertype;
			$data['eid']=(int)$_POST['eid'];
			$data['r_name']=$_POST['r_name'];
			$data['username']=$this->username;
			$data['r_reason']=@implode(',',$_POST['reason']);
            
			$haves=$this->obj->DB_select_once("report","`p_uid`='".$data['p_uid']."' and `c_uid`='".$data['c_uid']."' and `usertype`='".$data['usertype']."'");
			if(is_array($haves)){
				$this->ACT_layer_msg("您已经举报过该用户！",8,$_SERVER['HTTP_REFERER']);
			}else{
				$nid=$this->obj->insert_into("report",$data);
				if($nid){
					$this->obj->member_log("举报用户".$_POST['r_name']);
					$this->ACT_layer_msg("举报成功！",9,$_SERVER['HTTP_REFERER']);
				}else{
					$this->ACT_layer_msg("举报失败！",8,$_SERVER['HTTP_REFERER']);
				}
			}
		}
	}
	function xls_action(){
		if($_POST['delid']){
			$ids=@implode(",",$_POST['delid']);
			include(CONFIG_PATH."db.data.php");
			unset($arr_data['sex'][3]);
			$CacheArr=$this->MODEL('cache')->GetCache(array('hy','job','user','city'));
			$this->yunset($CacheArr);
			$down=$this->obj->DB_select_all("down_resume","`comid`='".$this->uid."' and `id` in (".$ids.")","`eid`");
			if(is_array($down)){
				foreach($down as $v){
					$eid[]=$v['eid'];
				}
			}
			$list=$this->obj->DB_select_all("resume_expect","`id` in (".@implode(",",$eid).")");
			$user_edu=$this->obj->DB_select_all("resume_edu","`eid` in (".@implode(",",$eid).")");
			$user_training=$this->obj->DB_select_all("resume_training","`eid` in (".@implode(",",$eid).")");
			$user_skill=$this->obj->DB_select_all("resume_skill","`eid` in (".@implode(",",$eid).")");
			$user_work=$this->obj->DB_select_all("resume_work","`eid` in (".@implode(",",$eid).")");
			$user_project=$this->obj->DB_select_all("resume_project","`eid` in (".@implode(",",$eid).")");
			$user_other=$this->obj->DB_select_all("resume_other","`eid` in (".@implode(",",$eid).")");
			if(is_array($user_edu)){
				foreach($user_edu as $v){
					$time=date("Y-m",$v['sdate'])."-".date("Y-m",$v['edate']);
					$useredu[$v['eid']][]=$v['name']."##".$time."##".$v['specialty']."##".$v['title']."##".$v['content']."##".$CacheArr['userclass_name'][$v['education']];
				}
			}
			if(is_array($user_training)){
				foreach($user_training as $v){
					$time=date("Y-m",$v['sdate'])."-".date("Y-m",$v['edate']);
					$usertraining[$v['eid']][]=$v['name']."##".$time."##".$v['title']."##".$v['content'];
				}
			}
			if(is_array($user_skill)){
				include PLUS_PATH."/user.cache.php";
				foreach($user_skill as $v){
					$userskill[$v['eid']][]=$v['name']."##".$v['longtime'].'年';
				}
			}
			if(is_array($user_work)){
				foreach($user_work as $v){
					if($v['edate']>0){
						$time=date("Y-m",$v['sdate'])."-".date("Y-m",$v['edate']);
					}else{
						$time=date("Y-m",$v['sdate'])."-至今 ";
					}
					$userwork[$v['eid']][]=$v['name']."##".$time."##".$v['department']."##".$v['title']."##".$v['content'];
				}
			}
			if(is_array($user_project)){
				foreach($user_project as $v){
					$time=date("Y-m",$v['sdate'])."-".date("Y-m",$v['edate']);
					$userproject[$v['eid']][]=$v['name']."##".$time."##".$v['sys']."##".$v['title']."##".$v['content'];
				}
			}
			if(is_array($user_other)){
				foreach($user_other as $v){
					$userother[$v['eid']][]=$v['name']."##".$v['content'];
				}
			}
			if(!empty($list)){
				foreach($list as $v){
					$uid[]=$v['uid'];
				}
				$resume=$this->obj->DB_select_all("resume","`uid` in (".@implode(",",$uid).")");
				foreach($list as $k=>$v){
					$list[$k]['sex']=$arr_data['sex'][$v['sex']];
					if($v['minsalary']){
						if($v['maxsalary']){
							$list[$k]['msalary']='￥'.$v['minsalary'].'-'.$v['maxsalary'];
						}else{
							$list[$k]['msalary']='￥'.$v['minsalary'].'元以上';
						}
					}else{
						$list[$k]['msalary']='面议';
					}
					foreach($resume as $val){
						if($v['uid']==$val['uid']){
							$list[$k]['resume']=$val;
						}
					}
					foreach($useredu as $key=>$val){
						if($v['id']==$key){
							$list[$k]['user_edu']=@implode("||",$val);
						}
					}
					foreach($usertraining as $key=>$val){
						if($v['id']==$key){
							$list[$k]['user_training']=@implode("||",$val);
						}
					}
					foreach($userskill as $key=>$val){
						if($v['id']==$key){
							$list[$k]['user_skill']=@implode("||",$val);
						}
					}
					foreach($userwork as $key=>$val){
						if($v['id']==$key){
							$list[$k]['user_work']=@implode("||",$val);
						}
					}
					foreach($userproject as $key=>$val){
						if($v['id']==$key){
							$list[$k]['user_project']=@implode("||",$val);
						}
					}
					foreach($userother as $key=>$val){
						if($v['id']==$key){
							$list[$k]['user_other']=@implode("||",$val);
						}
					}
					if($v['job_classid']!=""){
						include PLUS_PATH."/job.cache.php";
						$job_classid=@explode(",",$v['job_classid']);
						$jobs=array();
						foreach($job_classid as $val){
							$jobs[]=$job_name[$val];
						}
						$list[$k]['job_classid']=@implode(",",$jobs);
					}
				}
				$this->yunset("list",$list);
				$this->obj->member_log("导出简历信息");
				header("Content-Type: application/vnd.ms-excel");
				header("Content-Disposition: attachment; filename=已下载的简历.xls");
				$this->com_tpl('resume_xls');
			}
		}
	}
	function remark_action(){
		if($_POST['remark']==""){
			$this->ACT_layer_msg("备注内容不能为空！",8,$_SERVER['HTTP_REFERER']);
		}else{
			$id=$this->obj->DB_update_all("down_resume","`remark`='".$_POST['remark']."'","`id`='".(int)$_POST['remarkid']."'");
			if($id)
			{
				$this->obj->member_log("备注人才".$_POST['rname']);
				$this->ACT_layer_msg("备注成功！",9,$_SERVER['HTTP_REFERER']);
			}else{
				$this->ACT_layer_msg("备注失败！",8,$_SERVER['HTTP_REFERER']);
			}
		}
	}
}
?>