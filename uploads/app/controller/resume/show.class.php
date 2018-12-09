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
class show_controller extends resume_controller{
	function index_action(){ 
	    include(CONFIG_PATH."db.data.php");
		unset($arr_data['sex'][3]);
		$this->yunset("arr_data",$arr_data);
		$JobM=$this->MODEL("job");
		
		
		if($this->usertype==2){
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
		}
		
		if(($this->uid==''||$this->username=='')&&$this->config['sy_resume_visitors']>0){ 
			if($_COOKIE['resumevisitors']>=$this->config['sy_resume_visitors']){
				$this->ACT_msg(Url('login'),"游客用户，每天只能访问".$this->config['sy_resume_visitors']."份简历，请登录后继续访问！");
			}else{
				if ($_COOKIE['resumevisitors']==''){
					$resumevisitors=1;
				}else{
					$resumevisitors=$_COOKIE['resumevisitors']+1;
				}
				$this->cookie->SetCookie("resumevisitors",$resumevisitors,time()+86400);
			}
		}
		
		
		if($this->usertype==2){
			$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'");
		}
		
		if ($statis){
			$rating=$statis['rating'];
			$discount=$this->obj->DB_select_once("company_rating","`id`=$rating");
			$this->yunset("discount",$discount);
			$this->yunset("statis",$statis);
		}
		
		if($this->usertype==2){
			$packs=$this->obj->DB_select_all("company_service","`display`='1'");
		}
		$pid=intval($_POST['pid']);
		if($pid){
			$packinfo=$this->obj->DB_select_all("company_service_detail","`type` = '$pid' order by `service_price` asc");
			$this->yunset("pid",$pid);
		}else{
			$pack=$this->obj->DB_select_once("company_service","`display`='1'","id");
			$packinfo=$this->obj->DB_select_all("company_service_detail","`type` = '".$pack['id']."' order by `service_price` asc");
		}
		$this->yunset("packs",$packs);
		$this->yunset("packinfo",$packinfo);
		
		
		$rating_1=$this->obj->DB_select_all("company_rating","`category`='1' and `service_price` > 0 and `display`='1' and `type`='1' order by `type` asc,`sort` desc");
		$this->yunset("rating_1",$rating_1);
		
		$rating_2=$this->obj->DB_select_all("company_rating","`category`='1' and `service_price` > 0 and `display`='1' and `type`='2' order by `type` asc,`sort` desc");
		$this->yunset("rating_2",$rating_2);
		
		$added=$this->obj->DB_select_all("company_service","`display`='1' order by sort desc");
		if(is_array($added)){
			foreach($added as $val){
				$types[] = $val['id'];
			}
			$service_detail = $this->obj->DB_select_all("company_service_detail"," `type` in(".pylode(',',$types).") order by sort desc");
			
			foreach($added as $key=>$val){
				foreach($service_detail as $v){
					if($val['id'] == $v['type']){
						$added[$key]['service_price'][]= $v['service_price'];
						$added[$key]['resume'][]= $v['resume'];
						$added[$key]['interview'][]= $v['interview'];
						$added[$key]['job_num'][]= $v['job_num'];
						$added[$key]['breakjob_num'][]= $v['breakjob_num'];
						$added[$key]['part_num'][]= $v['part_num'];
						$added[$key]['breakpart_num'][]= $v['breakpart_num'];
						
 					} 
				}
			}
 		}
		$this->yunset("added",$added);
		
		$M=$this->MODEL('resume');
		if((int)$_GET['uid']){
			
				$def_job=$M->SelectResume(array("uid"=>(int)$_GET['uid'],"`r_status`<>'2'"),"def_job");
				if(!is_array($def_job)){
	    			$this->ACT_msg($this->config['sy_weburl'],"没有找到该人才！");
	    		}else if($def_job['def_job']<'1'){
					$this->ACT_msg($this->config['sy_weburl'].'/member',"还没有创建简历！");
	    		}else if($def_job['def_job']){
					$id=(int)$def_job['def_job'];
				}
		}else if((int)$_GET['id']){
			$id=(int)$_GET['id'];
			$expect=$M->SelectExpectOne(array("id"=>$id),"r_status,uid");
			if($expect['uid']!=$this->uid && $_GET['look']!="admin"){
				if($expect['r_status']<'1'){
					$this->ACT_msg($this->config['sy_weburl'].'/member',"简历正在审核中！");
				}elseif($expect['r_status']=='2'){
					$this->ACT_msg($this->config['sy_weburl'].'/member',"简历暂被锁定，请稍后查看！");
				}elseif($expect['r_status']=='3'){
					$this->ACT_msg($this->config['sy_weburl'].'/member',"简历审核暂未通过！");
				}
			}
		} 
		$resume_expect=$M->SelectExpectOne(array("id"=>$id));
		if($resume_expect['id']){
			
			$UserinfoM=$this->MODEL('userinfo');
			$UserMember=$UserinfoM->GetMemberOne(array("uid"=>$resume_expect['uid']),array("field"=>"`source`,`email`,`claim`"));
			$this->yunset("UserMember",$UserMember);
			
			$time=strtotime("-14 day");
			$allnum=$M->SelectUserIdMsgNum(array("uid"=>$resume_expect['uid'],"`datetime`>'".$time."'"));
			$replynum=$M->SelectUserIdMsgNum(array("uid"=>$resume_expect['uid'],"`datetime`>'".$time."' and `is_browse`>'2'"));
			$pre=round(($replynum/$allnum)*100); 
			$this->yunset("pre",$pre); 
			if($this->usertype==2){
				$JobM=$this->MODEL('job');
				$jobnum=$JobM->GetComjobNum(array("uid"=>$this->uid));
				$this->yunset("jobnum",$jobnum);
			}
			$user=$M->resume_select($id,$resume_expect);
			if($user['sex']==152){
				$user['sex']='2';
			}elseif ($user['sex']==153){
				$user['sex']='1';
			}
			$user['sex']=$arr_data['sex'][$user['sex']];
			$data['resume_username']=$user['username_n'];
			$data['resume_city']=$user['cityname'];
			$data['resume_job']=$user['customjob'];
			$this->data=$data;
			$this->seo("resume");
			$this->yunset("Info",$user);
			
			
			if(is_array($user)&&$user&&$this->uid){
				$usermsgnum=$M->SelectUserIdMsgNum(array('fid'=>$this->uid,'uid'=>$user['uid']));
				$this->yunset("usermsgnum",$usermsgnum);
			}
			

			if($_GET['type']=="word"){ 
				if($user['uid']==$this->uid){
					$this->yuntpl(array('resume/wordresume'));
					die;
				}else{
					$resume=$M->SelectDownResumeOne(array("eid"=>(int)$_GET['id'],"downtime"=>$_GET['downtime']));
					if(is_array($resume) && !empty($resume)){ 
						$this->yuntpl(array('resume/wordresume'));
					}
					die;
				}
			}

			if($this->usertype=="2" ){
				$this->yunset("uid",$this->uid);
				$this->obj->DB_update_all("userid_job","`is_browse`=2","`com_id`='".$this->uid."' and `eid`='".(int)$_GET['id']."' and `is_browse`=1");

				if($this->usertype=="2"){
					$talent_pool=$M->SelectTalentPool(array("eid"=>$id,"cuid"=>$this->uid));
					$this->yunset("talent_pool",$talent_pool);
					
				}else{
					
				}
				

				$look_resume=$M->SelectLookResumeOne(array("com_id"=>$this->uid,"resume_id"=>$id));

				if(!empty($look_resume)){
					$M->SaveLookResume(array("datetime"=>time()),array("resume_id"=>$id,"com_id"=>$this->uid));
				}else{
					$data['uid']=$resume_expect['uid'];
					$data['resume_id']=$id;
					$data['com_id']=$this->uid;
					$data['did']=$this->userdid;
					$data['datetime']=time();
					$M->SaveLookResume($data);
					$historyM = $this->MODEL('history');
					$historyM->addHistory('lookresume',$id);
				}
			}
			

			
				$this->yunset(array("resumestyle"=>$this->config['sy_weburl']."/app/template/resume"));  
				$tmp=intval($_GET['tmp']); 
				$statis=$this->MODEL('userinfo')->GetUserstatisOne(array('uid'=>$user['uid']),array('field'=>'`tpl`,`paytpls`','usertype'=>1));
				if($statis['paytpls']){
					$paytpls=@explode(',',$statis['paytpls']);
					$this->yunset("paytpls",$paytpls);
				}  
				if($tmp){ 
					$url=$this->MODEL('resume')->SelectResumeTpl(array('id'=>$tmp)); 
					if($this->uid!=$user['uid']&&in_array($tmp,$paytpls)==false){
						unset($tmp);
					} 
				} else{
					$tmp=1;
					$url=$this->MODEL('resume')->SelectResumeTpl(array('id'=>$statis['tpl'])); 
				}   
				if($url['url']==''){
					unset($tmp);
				} 
				if($tmp){ 
					$this->yunset("tplurl",$url);
					$this->yuntpl(array('resume/'.$url['url'].'/index')); 
				}else{

					$this->yuntpl(array('resume/resume'));
				} 
			
		}else{
			$this->ACT_msg($this->config['sy_weburl'],"没有找到该人才！");
		}
    } 
    function GetHits_action() {
        if((int)$_GET['id']){
            $ResumeM=$this->MODEL('resume');
            $ResumeM->AddExpectHits((int)$_GET['id']);
            $hits=$ResumeM->SelectExpectOne(array('id'=>(int)$_GET['id']),'hits');
            echo 'document.write('.$hits['hits'].')';
        }
    }

	function report_action(){
		
		if($_POST['submit']){
 			if($_POST['reason']==""){
				$this->ACT_layer_msg("举报内容不能为空！",8,$_SERVER['HTTP_REFERER']);
			}
			if($this->uid =='' ){
				$this->ACT_layer_msg("请先登录！",8,$_SERVER['HTTP_REFERER']);
			}

			$data['r_reason']=@implode(',',$_POST['reason']);

			$data['c_uid']=(int)$_POST['r_uid'];
			$data['r_name']=$_POST['r_name'];
			$data['eid']=(int)$_POST['r_eid'];
			
			$data['inputtime']=mktime();

			$data['p_uid']=$this->uid;
			$data['did']=$this->userdid;
			$data['usertype']=(int)$this->usertype;
			$data['username']=$this->username;

 
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
    
	
	function getPrice_action(){
		if($_POST['comservinceid']){
			$packinfo=$this->obj->DB_select_once("company_service_detail","`id` = '".$_POST['comservinceid']."'","`service_price`");
			
			$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'");
			if ($statis){
				$rating=$statis['rating'];
				$discount=$this->obj->DB_select_once("company_rating","`id`=$rating");
			}
			if ($discount['service_discount']){
				$price=$packinfo['service_price']*$discount['service_discount']*0.01;
			}else{
				$price=$packinfo['service_price'];
			}
			 
		}
		echo $price;
	}
    function pay_action(){
	
		if($_POST){
			
			$M=$this->MODEL('compay');
			if($_POST['eid']){
				if($this->usertype==2){
					$return = $M->buyDownresume($_POST);
				}
			}elseif($_POST['tid']){
				$return = $M->buyPackOrder($_POST);
			}elseif($_POST['invite']){
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
			if ($_POST['eid']){
				if($this->usertype==2){
					$return = $M->downresume($_POST);
				}
			}elseif($_POST['tcid']){
				$return = $M->buyPackOrder($_POST);
			}elseif($_POST['invite']){
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
    
}
?>