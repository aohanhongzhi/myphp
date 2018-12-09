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
class resume_controller extends common{
	function index_action(){
		$this->rightinfo();
		$this->get_moblie();
		$CacheM=$this->MODEL('cache');
        $CacheArr=$CacheM->GetCache(array('user','job','city','hy'));
        $uptime=array(1=>'今天',3=>'最近3天',7=>'最近7天',30=>'最近一个月',90=>'最近三个月');
        $this->yunset("uptime",$uptime);
		include(CONFIG_PATH."db.data.php");
		unset($arr_data['sex'][3]);
		$this->yunset("arr_data",$arr_data);
		if($_GET['jobin']){
			$job_classid=@explode(',',$_GET['jobin']);
			$jobname=$CacheArr['job_name'][$job_classid[0]];
			$this->yunset("jobname",mb_substr($jobname,0,6,'utf-8'));
		}
		foreach($_GET as $k=>$v){
			if($k!=""){
				$searchurl[]=$k."=".$v;
			}
		}
		
		$down=$this->obj->DB_select_all('down_resume','comid='.$this->uid.'','eid');
		foreach ($down as $v){
		    $eid[]=$v['eid'];
		}
		$this->yunset('eid',$eid);
		$searchurl=@implode("&",$searchurl);
		$this->yunset("searchurl",$searchurl);
		$this->yunset('backurl',Url('wap'));
		
		$this->yunset($CacheArr);
		
		$this->yunset("headertitle","找人才");
		$this->yunset("topplaceholder","请输入简历关键字,如：服务员...");
		$this->seo("user_search");
		$this->yuntpl(array('wap/resume'));
	}
	function search_action(){
		$this->index_action();
	}
	function show_action(){
		$this->rightinfo();
		$this->get_moblie();
		$ResumeM=$this->MODEL('resume');
		include(CONFIG_PATH."db.data.php");
		unset($arr_data['sex'][3]);
		$this->yunset("arr_data",$arr_data);
		if((int)$_GET['uid']){
			$def_job=$ResumeM->SelectResume(array("uid"=>(int)$_GET['uid'],"`r_status`='1'"));
				$id=$def_job['def_job'];
			
 		}else{
			$id=(int)$_GET['id'];
			$expect=$ResumeM->SelectExpectOne(array("id"=>$id),"r_status,uid");
			if($expect['uid']!=$this->uid&&$_GET['look']!="admin"){
				if($expect['r_status']<'1'){
					$data['msg']='简历正在审核中！';
					$data['url']=$_SERVER['HTTP_REFERER'];
					$this->yunset("layer",$data);
					$this->yuntpl(array('wap/resume_show'));
				}elseif($expect['r_status']=='2'){
					$data['msg']='简历暂被锁定，请稍后查看！';
					$data['url']=$_SERVER['HTTP_REFERER'];
					$this->yunset("layer",$data);
					$this->yuntpl(array('wap/resume_show'));
				}elseif($expect['r_status']=='3'){
					$data['msg']='简历审核暂未通过！';
					$data['url']=$_SERVER['HTTP_REFERER'];
					$this->yunset("layer",$data);
					$this->yuntpl(array('wap/resume_show'));
				}
			}
			
		}
		$user=$ResumeM->resume_select((int)$_GET['id']);
 		$euid=$this->obj->DB_select_once("resume_expect","`id`='".(int)$_GET['id']."'","`uid`,`rec_resume`");
		$talent_pool=$this->obj->DB_select_num("talent_pool","`eid`='".(int)$_GET['id']."'and `cuid`='".$this->uid."'");
		$userid_msg=$this->obj->DB_select_num("userid_msg","`uid`='".$euid['uid']."'and `fid`='".$this->uid."'");
		$user['talent_pool']=$talent_pool;
		$user['userid_msg']=$userid_msg;
		$user['euid']=$euid['uid'];
		$user['rec_resume']=$euid['rec_resume'];
        if($this->usertype=="2" ){
			$this->yunset("uid",$this->uid);
 			$this->obj->DB_update_all("userid_job",array("is_browse"=>"2"),array("com_id"=>$this->uid,"eid"=>(int)$_GET['id'], "is_browse"=>"1"));
			
			$look_resume=$ResumeM->SelectLookResumeOne(array("com_id"=>$this->uid,"resume_id"=>$id));
			if(!empty($look_resume)){
				$ResumeM->SaveLookResume(array("datetime"=>time()),array("resume_id"=>$id,"com_id"=>$this->uid));
			}else{
				$ResumeM->AddExpectHits($id);
				$data['uid']=$user['uid'];
				$data['resume_id']=$id;
				$data['com_id']=$this->uid;
				$data['did']=$this->userdid;
				$data['datetime']=time();
				$ResumeM->SaveLookResume($data);
			}
        }
		$data['resume_username']=$user['username_n'];
		$data['resume_city']=$user['cityname'];
		$data['resume_job']=$user['customjob'];
		$this->data=$data;
		$this->seo("resume");
 		$this->yunset("Info",$user);
  		$this->yunset("headertitle","个人简历");
		$this->yunset("shareurl",Url('wap',array('c'=>'resume','a'=>'share','id'=>(int)$_GET['id'])));
		$this->yuntpl(array('wap/resume_show'));
	}
	function share_action(){
		$this->get_moblie();
		include(CONFIG_PATH."db.data.php");
		unset($arr_data['sex'][3]);
		$this->yunset("arr_data",$arr_data);
		$ResumeM=$this->MODEL('resume');
		$user=$ResumeM->resume_select((int)$_GET['id']);
		$user['sex']=$arr_data['sex'][$user['sex']];
		$this->yunset("Info",$user);
		$data['resume_username']=$user['username_n'];
		$data['resume_city']=$user['city_one'].",".$user['city_two'];
		$this->data=$data;
		$this->seo("resume_share");
		$this->yunset("resume_style",$this->config['sy_weburl']."/app/template/wap/resume");
		$this->yuntpl(array('wap/resume/index'));
	}
	
	
	function invite_action(){
		$this->get_moblie();
		
		if($_GET['uid']){
			include(PLUS_PATH."job.cache.php");
			$uid=$_GET['uid'];
			
			$userrows=$this->obj->DB_select_once("resume","`uid` ='".$uid."' and `r_status`<>'2'","`name`,`sex`,`uid`,`photo`");
			$data['name']=$userrows['name'];
			$data['uid']=$userrows['uid'];
			if(!$userrows['photo'] || !file_exists(str_replace('./',APP_PATH,$userrows['photo']))){
			    if ($userrows['sex']==1){
			        $data['photo']=$this->config['sy_weburl']."/".$this->config['sy_member_icon'];
			    }else{
			        $data['photo']=$this->config['sy_weburl']."/".$this->config['sy_member_iconv'];
			    }
			}else{
			    $data['photo']=str_replace("./",$this->config['sy_weburl']."/",$userrows['photo']);
			}
			
			$expect=$this->obj->DB_select_once("resume_expect","`uid` = '".$uid."'","`id`,`job_classid`,`salary`");
			$jobids=@explode(',',$expect['job_classid']);
			foreach($jobids as $key=>$value){
				if($value){
					$jobname[]=$job_name[$value];
				}
			}
			$data['id']=$expect['id'];
			$data['jobname']=$jobname;
			$this->yunset("usermsg",$data);

			
			$company = $this->obj->DB_select_once("company","`uid`='".$this->uid."'","`linktel`,`linkphone`,`linkman`,`address`");
			$this->yunset("company",$company);
			
			if($_GET['jobid']){
				$job=$this->obj->DB_select_once("company_job","`uid`='".$this->uid."' and `status`='0' and `state`='1' and `id`='".$_GET['jobid']."'");
				
				if(is_array($job)){
					$job_link=$this->obj->DB_select_once("company_job_link","`uid`='".$this->uid."' and  `jobid`='".$_GET['jobid']."'");
					
					if($job['is_link']=='1'){
						if($job['link_type']=='1'){
							$job['link_man'] = $company['linkman'];
							$job['link_moblie'] = $company['linktel']?$company['linktel']:$company['linkphone'];
						}else{
							$job['link_man'] = $job_link['link_man'];
							$job['link_moblie'] = $job_link['link_moblie'];
						}
					}else if($job['is_link']=='0'){
						$job['link_man'] = "";
						$job['link_moblie'] = "";
					}	
				}
				$this->yunset("job",$job);
			}
				
			
			$joblist=$this->obj->DB_select_all("company_job","`uid`='".$this->uid."' and `status`='0' and `state`='1' and `r_status`='1' ");
			if(is_array($joblist)){
				
				foreach($joblist as $k=>$v){
					$jids[]=$v['id'];
 				}
 				$jids = pylode(",",$jids);
				
				$joblink = $this->obj->DB_select_all("company_job_link","`jobid` in ($jids)","`uid`,`jobid`,`link_type`,`link_man`,`link_moblie`");
				foreach($joblist as $k=>$v){
					if($joblist[$k]['is_link']=='1'){
						if($joblist[$k]['link_type']=='1'){
 							$joblist[$k]['link_man']=$company['linkman']; 
							$joblist[$k]['link_moblie']=$company['linktel']?$company['linktel']:$company['linkphone']; 
						}else if($joblist[$k]['link_type']=='2'){
							foreach($joblink as $val){
								if($v['id']==$val['jobid']){
									$joblist[$k]['link_man']=$val['link_man']; 
									$joblist[$k]['link_moblie']=$val['link_moblie']; 
								}
							}
						}
					}else if($joblist[$k]['is_link']=='0'){
						$joblist[$k]['link_man']=""; 
						$joblist[$k]['link_moblie']=""; 
					}
				}
			}
			$this->yunset("joblist",$joblist);
			
			
 			$this->seo('invite_resume');
		}
		
		$backurl=Url('wap',array('c'=>'resumecolumn'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset("headertitle","面试邀请");
		$this->yuntpl(array('wap/invite'));
	}
}
?>