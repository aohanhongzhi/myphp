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
class comapply_controller extends job_controller{
	function index_action(){
		include(CONFIG_PATH."db.data.php");		
		$this->yunset("arr_data",$arr_data);
		$id=(int)$_GET['id'];
		$M=$this->MODEL('job');
        $UserinfoM=$this->MODEL('userinfo');
		$ResumeM=$this->MODEL('resume');
        $CacheM=$this->MODEL('cache');
        $CacheList=$CacheM->GetCache(array('job','city','com','user','hy'));
        $this->yunset($CacheList);		
        $JobInfo=$M->GetComjobOne(array('id'=>$id, '`r_status`<>2'));

		if($this->uid==$JobInfo['uid']){
			if($JobInfo['state']=="2"){
				$this->yunset('entype','1');
			}
		}else{
			if($JobInfo['state']=="0"){
				session_start();
				if($_SESSION['auid']==''){
					$this->ACT_msg($_SERVER['HTTP_REFERER'],"职位审核中！");
				}	
				
			}elseif($JobInfo['state']=="2"){
				$this->yunset('entype','1');
			}elseif($JobInfo['state']=="3"){
				session_start();
				if($_SESSION['auid']==''){
					$this->ACT_msg($_SERVER['HTTP_REFERER'],"该职位未通过审核！");
				}	
			}
			if($JobInfo['r_status']=='2'){
				session_start();
				if($_SESSION['auid']==''){
					$this->ACT_msg($this->config['sy_weburl'],"企业已被锁定！");
				}
			}
			if($JobInfo['status']=="1"){
				$this->yunset('stop','1');
				
			}
		}

		if($JobInfo['id']==''){
			$this->ACT_msg($this->config['sy_weburl'],"没有该职位！");
		}
		if($_SESSION['auid']==""){
		    if($JobInfo['r_status']=='2'){
		        $this->ACT_msg($this->config['sy_weburl'],"企业已被锁定！");
		    }
		}else{
		    $row = $this->obj->DB_select_once("admin_user","`uid`='".$_SESSION['auid']."'");
		    if(!empty($row) && $_SESSION['ashell'] == md5($row['username'].$row['password'].$this->md)){
		        $JobInfo['state']=1;
		    }
		}
		if($this->uid==$JobInfo['uid']){
		    if($JobInfo['state']=="2"){
		        $this->yunset('entype','1');
		    }
		}else{
		    if($JobInfo['state']=="0"){
		        $this->ACT_msg($_SERVER['HTTP_REFERER'],"职位审核中！");
		    }elseif($JobInfo['state']=="2"){
		        $this->yunset('entype','1');
		    }elseif($JobInfo['state']=="3"){
		        $this->ACT_msg($_SERVER['HTTP_REFERER'],"该职位未通过审核！");
		    }elseif($JobInfo['status']=="1"){
		        $this->yunset('stop','1');
		        
		    }
		}

		if($this->usertype=="1"&&$this->uid){
			$useridjobnum=$this->obj->DB_select_num("userid_job","`uid`='".$this->uid."' and  `job_id`='".$id."'");
			if($useridjobnum>0){
				$this->yunset("useridjobnum",$useridjobnum);
			}
			
			$resume=$ResumeM->GetResumeExpectNum(array('uid'=>$this->uid,"`r_status`<>'2' and `job_classid`<>''","status"=>'1'));
			if($resume!='0'){ 
				$this->yunset("resumenum",$resume);
				$look_job=$M->GetLookJobOne(array('uid'=>$this->uid,'jobid'=>$JobInfo['id']));
				if(!empty($look_job)){
					$M->UpdateLookJob(array('datetime'=>time()),array('uid'=>$this->uid,'jobid'=>$JobInfo['id']));
				}else{
					
					$M->AddLookJob(array('uid'=>$this->uid,'jobid'=>$JobInfo['id'],'com_id'=>$JobInfo['uid'],'datetime'=>time(),'did'=>$this->userdid)); 
					$historyM = $this->MODEL('history');
					$historyM->addHistory('lookjob',$JobInfo['id']);
				}
			}
		}
 
		if($this->uid!=$JobInfo['uid']){
			
			if($this->usertype=='1'){
				$favjob=$M->GetFavJobOne(array("uid"=>$this->uid,"type"=>1,"job_id"=>$JobInfo['id']));
				$userid_job=$M->GetUseridJobOne(array('uid'=>$this->uid,'job_id'=>$JobInfo['id']),array('field'=>'id'));
				$this->yunset(array('userid_job'=>$userid_job['id'],'usertype'=>1,"favjob"=>$favjob));
			}
		}
		$ComInfo=$UserinfoM->GetUserinfoOne(array('uid'=>$JobInfo['uid']),array('field'=>"`shortname`,`ant_num`,`linkqq`,`logo`,`address`,`busstops`,`linktel`,`linkman`,`linkphone`,`email_status`,`moblie_status`,`yyzz_status`,`content`,`x`,`y`,`welfare`,`infostatus`",'usertype'=>2));
		if(!$ComInfo['logo'] || !file_exists(str_replace($this->config['sy_weburl'],APP_PATH,'.'.$ComInfo['logo']))){
		    $ComInfo['logo']=$this->config['sy_weburl']."/".$this->config['sy_unit_icon'];
		}else{
		    $ComInfo['logo']=str_replace("./",$this->config['sy_weburl']."/",$ComInfo['logo']);
		}
		$JobInfo['jobname']=$JobInfo['name'];unset($JobInfo['name']);
		$JobInfo['jobrec']=$JobInfo['rec'];unset($JobInfo['rec']);
        $Info=array_merge_recursive($JobInfo,$ComInfo); 
		$Info['welfare'] = $ComInfo['welfare'];
		$Info['x'] = $ComInfo['x'];
		$Info['y'] = $ComInfo['y'];
		if($Info['linkman']){
			$operatime=time()-$Info['operatime'];
			if($Info['operatime']==''){
				$Info['operatime']='0';
			}else if($operatime<3600){
				$Info['operatime']='一小时以内';
			}else if($operatime>=3600&&$operatime<86400){
				$Info['operatime']=floor($operatime/3600).'小时';
			}else if($operatime>=86400){
				$Info['operatime']=floor($operatime/86400).'天';
			}  
			$allnum=$M->GetUserJobNum(array("com_id"=>$Info['uid'],"job_id"=>$Info['id']));
			$replynum=$M->GetUserJobNum(array("com_id"=>$Info['uid'],"job_id"=>$Info['id'],"`is_browse`>'1'")); 
			if($allnum==0){
 				$this->yunset("pre",0);
 			}else{
 				$pre=round(($replynum / $allnum)*100); 
				$this->yunset("pre",$pre);
 			}
		}
		$rows=$this->obj->DB_select_once("resume_expect","`uid`='$this->uid'");
		$this->yunset("rows",$rows);

		if(is_array($Info)){
            $cache_array = $this->db->cacheget();
			$Job = $this->db->array_action($Info,$cache_array);
			if($Info['shortname']){
			    $Job['all_name'] = $Info['com_name'];
				$Job['com_name']=$Info['shortname'];
			}
				
			if($Job['link_type']==1){
				
				$Job['linkman']=$Info['linkman'];
				if ($Info['linktel']){
				    $Job['linktel']=substr_replace($Info['linktel'],'****',4,4);
				}elseif ($Info['linkphone']){
				    $Job['linktel']=substr_replace($Info['linkphone'],'****',4,4);
				}
				
			}else{
				
				$link=$M->GetComjoblinkOne(array('jobid'=>$Job['id']),array('field'=>'`link_man`,`link_moblie`'));
				$Job['linkman']=$link['link_man'];
				$Job['linktel']=substr_replace($link['link_moblie'],'****',4,4);
			}
		}
		
		if($this->uid&&$this->usertype&&$this->usertype!=1){
			$typename=array('2'=>'企业账户');
			$this->yunset("usertypemsg",'您当前帐号名为：<span class="job_user_name_s">'.$this->username.'</span>，属于'.$typename[$this->usertype].'。');
		}
		$com=$UserinfoM->GetMemberOne(array('uid'=>$Job['uid']),array('field'=>'`username`'));
		$Job['content']=str_replace(array("\r","\n"), array("<br/>","<br/>"), strip_tags($Job['content'],"\r,\n"));
		$Job['cert_n'] = @explode(",",$Job['cert']);
		$Job['uid'] = $Job['uid'];
		$Job['com_url'] = Url("company",array("c"=>"show","id"=>$Job[uid]));
		$Job['username'] = $com['username'];
		$Job['sex']=$arr_data['sex'][$Job['sex']];
		$Job['description']=str_replace(array("ti<x>tle","“","”"),array("title"," "," "),$Job['description']);
		$data['job_name']=$Job['jobname'];
		$data['company_name']=$Job['com_name'];
		$data['industry_class']=$Job['job_hy'];
		if($Job['minsalary'] && $Job['maxsalary']){
			$data['job_salary'] = "￥".$Job['minsalary']."-".$Job['maxsalary'];
		}else if($Job['minsalary']){
			$data['job_salary'] = "￥".$Job['minsalary']."以上";
		}else{
			$data['job_salary'] = "面议";
		}
		$data['job_class']=$Job['job_class_one'].",".$Job['job_class_two'].",".$Job['job_class_three'];
		$data['job_desc']=$this->GET_content_desc($Job['description']);
		$this->data=$data;		
		$this->yunset("Info",$Job);
 
		
		if($this->uid && isset($this->config['sy_recommend_day_num']) 
			&& $this->config['sy_recommend_day_num'] > 0){
			$num = $this->obj->DB_select_num('recommend', "`uid`={$this->uid}");
			if($num >= $this->config['sy_recommend_day_num']){
				$this->yunset('sy_recommend_day_num', $this->config['sy_recommend_day_num']);
			}
		}
		
		$recommendInterval = isset($this->config['sy_recommend_interval']) ? $this->config['sy_recommend_interval'] : 0;
		$this->yunset('recommendInterval', $recommendInterval);

		
		$isAuthcodeCheck = ($this->config['sy_msg_isopen']==1 && $this->config['reg_real_name_check']==1);
		$this->yunset('isAuthcodeCheck', $isAuthcodeCheck);
		
		$this->seo("comapply");
		$this->yun_tpl(array('comapply'));
	}
	
	function qrcode_action(){
		$wapUrl = Url('job');
		if( isset($_GET['id']) && $_GET['id'] != '')
			$wapUrl = Url('job',array('c'=>'comapply','id'=>(int)$_GET['id']));
		include_once LIB_PATH."yunqrcode.class.php";
		YunQrcode::generatePng2($wapUrl,4);
	}

	function msg_action(){
		if($_POST['submit']){
			if($this->uid==''||$this->username==''){
				$this->ACT_layer_msg("请先登录！",8,$_SERVER['HTTP_REFERER']);
			}
			if($this->usertype!="1"){
				$this->ACT_layer_msg("只有个人用户才可以留言！",8,$_SERVER['HTTP_REFERER']);
			}
			$M=$this->MODEL("job");
			$black=$M->GetBlackOne(array('p_uid'=>$this->uid,'c_uid'=>(int)$_POST['job_uid']));
			if(!empty($black)){
				$this->ACT_layer_msg("该企业暂不接受相关咨询！",8,$_SERVER['HTTP_REFERER']);
			}
			if(trim($_POST['content'])==""){
				$this->ACT_layer_msg( "留言内容不能为空！",8,$_SERVER['HTTP_REFERER']);
			}
			if(trim($_POST['authcode'])==""){
				$this->ACT_layer_msg( "验证码不能为空！",8,$_SERVER['HTTP_REFERER']);
			}
			session_start();
			if(md5(strtolower($_POST['authcode']))!=$_SESSION['authcode'] || empty($_SESSION['authcode'])){
				$this->ACT_layer_msg("验证码错误！", 8);
			}
			$id=$M->AddMsg(array('uid'=>$this->uid,'username'=>$this->username,'jobid'=>trim($_POST['jobid']),'job_uid'=>trim($_POST['job_uid']),'content'=>trim($_POST['content']),'com_name'=>trim($_POST['com_name']),'job_name'=>trim($_POST['job_name']),'type'=>'1','datetime'=>time()));
			isset($id)?$this->ACT_layer_msg( "留言成功！",9,$_SERVER['HTTP_REFERER']):$this->ACT_layer_msg("留言失败！",8,$_SERVER['HTTP_REFERER']);
		}
	}
	function GetHits_action() {
	    if(intval($_GET['id'])){
	        $JobM=$this->MODEL('job');
			if($this->config['sy_job_hits']>100 || !$this->config['sy_job_hits']){
				$this->config['sy_job_hits']=1;
			}
			$hits=rand(1,$this->config['sy_job_hits']);
	        $JobM->UpdateComjob(array("`jobhits`=`jobhits`+".$hits),array("id"=>(int)$_GET['id']));
	        $hits=$JobM->GetComjobOne(array('id'=>intval($_GET['id'])),array('field'=>'jobhits'));
	        echo 'document.write('.$hits['jobhits'].')';
	    }
	}
	function gettel_action(){
		
		$id=intval($_POST['id']);
		
		if($id){
			$JobM=$this->MODEL('job');
			$JobInfo=$JobM->GetComjobOne(array('id'=>$id,'r_status<>2','state'=>1),array('field'=>'`is_link`,`link_type`,`uid`'));
	
			$companyM=$this->MODEL('company');
			$cominfo=$companyM->GetCompanyInfo(array('uid'=>$JobInfo['uid']),array('field'=>'`linktel`,`linkphone`,`linkqq`,`linkman`,`busstops`'));
			
			$JobInfo['linkqq']=$cominfo['linkqq'];
			$JobInfo['busstops']=$cominfo['busstops'];
			
			if($JobInfo['link_type']=='2'){
				
				
				$link=$JobM->GetComjoblinkOne(array('jobid'=>$id),array('field'=>'link_moblie,link_man'));
				if(!empty($link)){
					if ($cominfo['linkphone']){
						$JobInfo['linkphone']=$cominfo['linkphone'];
					}
					$JobInfo['linktel']=$link['link_moblie'];
					$JobInfo['linkman']=$cominfo['linkman'];
				}else{
					if ($cominfo['linktel']&&!$cominfo['linkphone']){
						$JobInfo['linktel']=$cominfo['linktel'];
					}elseif (!$cominfo['linktel']&&$cominfo['linkphone']){
						$JobInfo['linktel']=$cominfo['linkphone'];
					}else{
						$JobInfo['linktel']=$cominfo['linktel'];
						$JobInfo['linkphone']=$cominfo['linkphone'];
					}
					$JobInfo['linkman']=$cominfo['linkman'];
				}
				
			}else{if ($cominfo['linktel']&&!$cominfo['linkphone']){
					$JobInfo['linktel']=$cominfo['linktel'];
				}elseif (!$cominfo['linktel']&&$cominfo['linkphone']){
					$JobInfo['linktel']=$cominfo['linkphone'];
				}else{
					$JobInfo['linktel']=$cominfo['linktel'];
					$JobInfo['linkphone']=$cominfo['linkphone'];
				}
				$JobInfo['linkman']=$cominfo['linkman'];
			}
		}		
		
		if($this->uid!=$JobInfo['uid']){
			if ($this->config['com_login_link']=="1"){
				if($JobInfo['is_link']="1"){
					echo json_encode(array('linktel'=>$JobInfo['linktel'],'linkphone'=>$JobInfo['linkphone'],'linkqq'=>$JobInfo['linkqq'],'linkman'=>$JobInfo['linkman'],'busstops'=>$JobInfo['busstops']));
				}else{
					echo "1";die;
				}
			}elseif($this->config['com_login_link']=="2"){
				echo 2;die;
			}elseif($this->uid==''){
				echo 3;die;
			}elseif($this->usertype!='1'){
				echo 31;die;
			}elseif ($this->config['com_login_link']=="3"){
				echo json_encode(array('linktel'=>$JobInfo['linktel'],'linkphone'=>$JobInfo['linkphone'],'linkqq'=>$JobInfo['linkqq'],'linkman'=>$JobInfo['linkman'],'busstops'=>$JobInfo['busstops']));
			}elseif ($this->config['com_login_link']=="4"){
				$ResumeM=$this->MODEL('resume');
				$resume=$ResumeM->GetResumeExpectNum(array('uid'=>$this->uid));
				if($resume < 1){
					echo 4;die;
				}else{
					echo json_encode(array('linktel'=>$JobInfo['linktel'],'linkphone'=>$JobInfo['linkphone'],'linkqq'=>$JobInfo['linkqq'],'linkman'=>$JobInfo['linkman'],'busstops'=>$JobInfo['busstops']));
				}				
			}elseif ($this->config['com_login_link']=="5"){
				
				$sendresume=$this->obj->DB_select_num("userid_job","`uid`='".$this->uid."' and  `job_id`='".$id."'");
				if($sendresume>0){
					echo json_encode(array('linktel'=>$JobInfo['linktel'],'linkphone'=>$JobInfo['linkphone'],'linkqq'=>$JobInfo['linkqq'],'linkman'=>$JobInfo['linkman'],'busstops'=>$JobInfo['busstops']));
					
				}else{
					echo 5;die;
				}
			}
		}else{
			echo json_encode(array('linktel'=>$JobInfo['linktel'],'linkphone'=>$JobInfo['linkphone'],'linkqq'=>$JobInfo['linkqq'],'linkman'=>$JobInfo['linkman'],'busstops'=>$JobInfo['busstops']));
		}
	}
}
?>