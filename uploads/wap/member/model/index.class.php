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
class index_controller extends wap_controller{
	function waptpl($tpname){
		$this->yuntpl(array('wap/member/user/'.$tpname));
	}
	function get_user(){
		$isresume=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'");
		if($isresume['name']==''){			
			$data['msg']='请先完善个人资料！';
		    $data['url']='index.php?c=info';
			$this->yunset("layer",$data);	
		}
	}
	function index_action(){
		$this->rightinfo();
		$expect=$this->obj->DB_select_once("resume_expect","`uid`='".$this->uid."' and `defaults`='1'","integrity,id,lastupdate");
		$this->yunset("expect",$expect);
		
		$user=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'");
		$this->yunset("user",$user);
		
		$date=date("Ymd"); 
		$reg=$this->obj->DB_select_once("member_reg","`uid`='".$this->uid."' and `usertype`='".$this->usertype."' and `date`='".$date."'"); 
		if($reg['id']){
			$signstate=1;
		}else{
			$signstate=0;
		}
		$this->yunset("signstate",$signstate);
		if($this->config['resume_sx']==1){
			if($user['def_job']){
				$this->obj->DB_update_all("resume_expect","`lastupdate`='".time()."'","`uid`='".$this->uid."' and `id`='".$user['def_job']."'");
				$this->obj->DB_update_all("resume","`lastupdate`='".time()."'","`uid`='".$this->uid."'");							
			}
		}	
		$time=strtotime(date("Y-m-d 00:00:00"));
		$this->cookie->SetCookie("exprefresh",'1',time() + 86400);
		$this->yunset("time",$time);
		
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('index');
	}

	function photo_action(){
		if($_POST['submit']){
			$pic=$this->wap_up_pic($_POST['uimage'],'user');
			if($pic['errormsg']){echo 2;die;}
			if($pic['re']){
				$user=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'","`photo`,`resume_photo`");
 				if(!$user['photo']){
					$this->MODEL('integral')->get_integral_action($this->uid,"integral_avatar","上传头像");
				}
				unlink_pic(APP_PATH.$user['photo']);
				$photo="./data/upload/user/".date('Ymd')."/".$pic['new_file'];
				$this->obj->DB_update_all("resume","`resume_photo`='".$photo."',`photo`='".$photo."'","`uid`='".$this->uid."'");
				$this->obj->DB_update_all("resume_expect","`photo`='".$photo."'","`uid`='".$this->uid."'");
				$this->obj->DB_update_all("answer","`pic`='".$photo."'","`uid`='".$this->uid."'");
				$this->obj->DB_update_all("question","`pic`='".$photo."'","`uid`='".$this->uid."'");
				echo 1;die;
			}else{
				unlink_pic(APP_PATH."data/upload/user/".date('Ymd')."/".$pic['new_file']);
				echo 2;die;
			}
		}else{
		    	
		   	$user=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'","sex,`resume_photo`,`photo`,`phototype`");
			
			if(!$user['photo'] || !file_exists(str_replace('./',APP_PATH,$user['resume_photo']))){
				if ($user['sex']==1){
					$user['photo']=$this->config['sy_weburl']."/".$this->config['sy_member_icon'];
				}else{
					$user['photo']=$this->config['sy_weburl']."/".$this->config['sy_member_iconv'];
				}
			}else{
				$user['photo']=str_replace("./",$this->config['sy_weburl']."/",$user['photo']);
			}
			
			$this->yunset("user",$user);
			$backurl=Url('wap',array(),'member');
			$this->yunset('backurl',$backurl);
			$this->yunset('headertitle',"个人头像");
			$this->get_user();
			$this->waptpl('photo');
		    	
		}
 	} 
		
	function phototype_action(){
	    $this->obj->DB_update_all("resume","`phototype`='".intval($_POST['phototype'])."'","uid='".$this->uid."'");
	    echo $_POST['phototype'];die();
	}
	function sq_action(){
		$this->yunset('headertitle',"申请的职位");
		$this->rightinfo();
		$urlarr=array("c"=>"sq","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("userid_job","`uid`='".$this->uid."' order by id desc",$pageurl,"10");
		if(is_array($rows)){
			foreach($rows as $v){
				$com_id[]=$v['com_id'];
			}
			$company=$this->obj->DB_select_all("company","`uid` in (".pylode(",",$com_id).")","cityid,uid,name");
			include PLUS_PATH."/city.cache.php";
			foreach($rows as $k=>$v){
				foreach($company as $val){
					if($v['com_id']==$val['uid']){
						$rows[$k]['city']=$city_name[$val['cityid']];
                        $rows[$k]['com_name']=$val['name'];
					}
				}
			}
		}
		$this->yunset("rows",$rows);
		if($_GET['back']){
			$backurl=Url('wap',array(),'member');
		}else{
			$backurl=Url('wap',array('c'=>'jobcolumn'),'member');
		}
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('sq');
	}
	
	function partcollect_action(){
		$this->yunset('headertitle',"兼职管理");
		$this->rightinfo();
		if($_GET['del']){
			$id=$this->obj->DB_delete_all("part_collect","`id`='".(int)$_GET['del']."' and `uid`='".$this->uid."'");
			if($id){
				$data['msg']="删除成功!";
				$this->member_log("删除收藏的兼职");
			}else{
				$data['msg']="删除失败！";
			}
			$data['url']='index.php?c=partcollect';
			$this->yunset("layer",$data);
		}
		$urlarr=array("c"=>"partcollect","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("part_collect","`uid`='".$this->uid."' order by id desc",$pageurl,"10");
		if($rows&&is_array($rows)){
			foreach($rows as $val){
				$jobids[]=$val['jobid'];
			}
			$joblist=$this->obj->DB_select_all("partjob","`id` in(".pylode(',',$jobids).")","`id`,`name`,`com_name`");
			foreach($rows as $key=>$val){
				foreach($joblist as $v){
					if($val['jobid']==$v['id']){
						$rows[$key]['job_name']=$v['name'];
						$rows[$key]['com_name']=$v['com_name'];

					}
				}
			}
		}
		$this->yunset("rows",$rows);
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('partcollect');
	}
	
	function partapply_action(){
		$this->yunset('headertitle',"兼职管理");
		$this->rightinfo();
		if($_GET['del']){
			$nid=$this->obj->DB_delete_all("part_apply","`id`='".(int)$_GET['del']."' and `uid`='".$this->uid."'");
			if($nid){
				$data['msg']="删除成功!";
				$this->member_log("删除报名的兼职");
			}else{
				$data['msg']="删除失败！";
			}
			$data['url']='index.php?c=partapply';
			$this->yunset("layer",$data);
		}
		$urlarr=array("c"=>"partapply","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("part_apply","`uid`='".$this->uid."' order by id desc",$pageurl,"10");
		if($rows&&is_array($rows)){
			include PLUS_PATH."/city.cache.php";
			foreach($rows as $val){
				$jobids[]=$val['jobid'];
			}
			$joblist=$this->obj->DB_select_all("partjob","`id` in(".pylode(',',$jobids).")","`id`,`name`,`cityid`,`com_name`,`linktel`");
			foreach($rows as $key=>$val){
				foreach($joblist as $v){
					if($val['jobid']==$v['id']){
						$rows[$key]['job_name']=$v['name'];
						$rows[$key]['city']=$city_name[$v['cityid']];
						$rows[$key]['com_name']=$v['com_name'];
						$rows[$key]['linktel']=$v['linktel'];
					}
				}
			}
		}

		$this->yunset("rows",$rows);
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('partapply');
	}
	function delsq_action(){
		if($_GET['id']){
			$userid_job=$this->obj->DB_select_once("userid_job","`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'");
			$id=$this->obj->DB_delete_all("userid_job","`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'");
			if($id){
				$this->obj->DB_update_all('company_statis',"`sq_job`=`sq_job`-1","`uid`='".$userid_job['com_id']."'");
				$this->obj->DB_update_all('member_statis',"`sq_jobnum`=`sq_jobnum`-1","`uid`='".$userid_job['uid']."'");
				$this->member_log("删除申请的职位");
				$this->waplayer_msg('删除成功！');
			}else{
				$this->waplayer_msg('删除失败！');
			}
		}
	}

	function collect_action(){
		$this->yunset('headertitle',"收藏的职位");
		$this->rightinfo();
		if($_GET['del']){
			$id=$this->obj->DB_delete_all("fav_job","`id`='".$_GET['del']."' and `uid`='".$this->uid."'");
			if($id){
				$data['msg']="删除成功!";
				$this->obj->DB_update_all("member_statis","`fav_jobnum`=`fav_jobnum`-1","uid='".$this->uid."'");
				$this->member_log("删除收藏的职位");
			}else{
				$data['msg']="删除失败！";
			}
			$data['url']='index.php?c=collect';
			$this->yunset("layer",$data);
		}
		$urlarr=array("c"=>"collect","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$this->get_page("fav_job","`uid`='".$this->uid."' order by id desc",$pageurl,"10");
		
		
		$this->get_user();
		$this->waptpl('collect');
	}

	function password_action(){
		$this->yunset('headertitle',"密码设置");
		
		$this->rightinfo();
		$this->yunset('backurl',Url('wap',array('c'=>'set'),'member'));
		$this->get_user();
		$this->waptpl('password');
	}
	function invitecont_action(){
		$this->yunset('headertitle',"通知详情");
		$this->rightinfo();
		$id=(int)$_GET['id'];
		$info=$this->obj->DB_select_once("userid_msg","`id`='".$id."' and `uid`='".$this->uid."'");
		if($info['is_browse']==1){
			$this->obj->update_once("userid_msg",array('is_browse'=>2),array("id"=>$info['id']));
		}
		$this->yunset("info",$info);
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('invitecont');
	}
	function inviteset_action(){
		$id=(int)$_GET['id'];
		$browse=(int)$_GET['browse'];
		if($id){
			$nid=$this->obj->update_once("userid_msg",array('is_browse'=>$browse),array("id"=>$id,"uid"=>$this->uid));
			
			$comuid=$this->obj->DB_select_once("userid_msg","`id`='".$id."'","`fid`,`jobid`,`linktel`,`linkman`");
			$comarr=$this->obj->DB_select_once("company_job","`id`='".$comuid['jobid']."' and `r_status`<>'2' and `status`<>'1'");
			$uid=$this->obj->DB_select_once("company","`uid`='".$comuid['fid']."'","`linkmail`,`linkman`");
			
			$name=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'","name");
			$data['uid']=$comuid['fid'];
			$data['cname']=$this->username;
			$data['type']="yqmshf";
			$data['cuid']=$this->uid;
			$data['cusername']=$name['name'];
			
			if($browse==3){
				$data['typemsg']='同意';
				$msg_content = "用户 ".$this->username." 同意了您的邀请面试！";
				$this->automsg($msg_content,$comuid['fid']);
			}elseif($browse==4){
				$data['typemsg']='拒绝';
			}
			if($this->config['sy_msg_yqmshf']=='1'&&$comuid["linktel"]&&$this->config["sy_msguser"]&&$this->config["sy_msgpw"]&&$this->config["sy_msgkey"]&&$this->config['sy_msg_isopen']=='1'){$data["moblie"]=$comuid["linktel"]; }
 			if($this->config['sy_email_yqmshf']=='1'&&$uid["linkmail"]&&$this->config['sy_email_set']=="1"){$data["email"]=$uid["linkmail"]; }
			if($data["email"]||$data['moblie']){
				$data['name']=$comuid['linkman'];
        $notice = $this->MODEL('notice');
        $notice->sendEmailType($data);
        $notice->sendSMSType($data);
			}
			$nid?$this->waplayer_msg("操作成功！"):$this->waplayer_msg("操作失败！");
		}
	}
  function delinvite_action(){
		if($_GET['id']){
			$info=$this->obj->DB_select_once("userid_msg","`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'");
			$data['p_uid']=$info['fid'];
			$data['inputtime']=mktime();
			$data['c_uid']=$this->uid;
			$data['usertype']=1;
			$data['com_name']=$info['fname'];
			$nid=$this->obj->DB_delete_all("userid_msg","`uid`='".$this->uid."' and `fid`='".$info['fid']."'"," ");
			if($nid){
					$this->layer_msg('操作成功！',9,0,"index.php?c=invite");
			}else{
          $this->layer_msg('操作失败！',8,0,"index.php?c=invite");
			}
			$data['url']='index.php?c=invite';
			$this->yunset("layer",$data);
		}
	}
	function invite_action(){
		$this->yunset('headertitle',"面试通知");
		$this->rightinfo();
		if($_GET['id']){
			$info=$this->obj->DB_select_once("userid_msg","`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'");
			$data['p_uid']=$info['fid'];
			$data['inputtime']=mktime();
			$data['c_uid']=$this->uid;
			$data['usertype']=1;
			$data['com_name']=$info['fname'];
			$haves=$this->obj->DB_select_once("blacklist","`c_uid`='".$this->uid."' and `p_uid`='".$info['fid']."'  and `usertype`='1'");
			if(is_array($haves)){
				$this->ACT_layer_msg("该用户已在您黑名单中！",8,$_SERVER['HTTP_REFERER']);
			}else{
				$nid=$this->obj->insert_into("blacklist",$data);
				$this->obj->DB_delete_all("userid_msg","`uid`='".$this->uid."' and `fid`='".$info['fid']."'"," ");
				if($nid){
					$this->obj->member_log("屏蔽公司 <".$info['fname']."> ，并删除邀请信息");
					$this->layer_msg('操作成功！',9,0,"index.php?c=invite");
				}else{
					$this->layer_msg('操作失败！',8,0,"index.php?c=invite");
				}
			}
			$data['url']='index.php?c=invite';
			$this->yunset("layer",$data);
		}
		$urlarr=array("c"=>"invite","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("userid_msg","`uid`='".$this->uid."' order by id desc",$pageurl,"10");
		foreach($rows as $key=>$value){
			$logo=$this->obj->DB_select_once("company","`uid` = '".$value['fid']."'","`logo`");
			$rows[$key]['logo']=$logo['logo'];
		}
		$this->yunset('rows',$rows);
		if($_GET['back']){
			$backurl=Url('wap',array(),'member');
		}else{
			$backurl=Url('wap',array('c'=>'jobcolumn'),'member');
		}
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('invite');
	}
	function look_action(){
		$this->yunset('headertitle',"谁看过我的简历");
		$this->rightinfo();
		if($_GET['del']){
			$id=$this->obj->DB_delete_all("look_resume","`id`='".(int)$_GET['del']."' and `uid`='".$this->uid."'");
			if($id){
				$data['msg']="删除成功!";
				$this->member_log("删除简历浏览记录");
			}else{
				$data['msg']="删除失败!";
			}
			$data['url']='index.php?c=look';
			$this->yunset("layer",$data);
		}
		$urlarr=array("c"=>"look","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("look_resume","`uid`='".$this->uid."' order by id desc",$pageurl,"10");
		if(is_array($rows)){
			foreach($rows as $v){
				$uid[]=$v['com_id'];
				$eid[]=$v['resume_id'];
			}
			$type=$this->obj->DB_select_all("member","`uid`IN  (".pylode(",",$uid).")","uid,usertype");
			foreach($type as  $v){
				if($v['usertype']==2){
					$com_uid[]=$v['uid'];
				}
			}
			$company=$this->obj->DB_select_all("company","`uid` IN (".pylode(",",$com_uid).")","uid,name");
			$resume=$this->obj->DB_select_all("resume_expect","`id` in (".pylode(",",$eid).")","`id`,`name`");
			foreach($rows as $k=>$v){
				foreach($company as $val){
					if($v['com_id']==$val['uid']){
						$rows[$k]['com_name']=$val['name'];
						$rows[$k]['type']=2;
					}
				}
				
				foreach($resume as $val){
					if($v['resume_id']==$val['id']){
						$rows[$k]['resume_name']=$val['name'];
					}
				}
			}
		}
		$this->yunset("rows",$rows);
		$backurl=Url('wap',array('c'=>'jobcolumn'),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('look');
	}

	function addresume_action(){
		$this->yunset('headertitle',"创建简历");
		include(CONFIG_PATH."db.data.php");
		unset($arr_data['sex'][3]);
		$this->yunset("arr_data",$arr_data);
		$row=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'");
		
		if($row['idcard_status']!='1' && $this->config['user_enforce_identitycert']=="1"){
			$data['msg']='请先完成身份认证！';
		    $data['url']='index.php?c=idcard';
			$this->yunset("layer",$data);	
		}
		
		$this->rightinfo();
		$resume=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'");
		$arr_data1=$arr_data['sex'][$resume['sex']];
		$this->yunset("arr_data1",$arr_data1);
		$this->yunset("resume",$resume);
		$this->yunset($this->MODEL('cache')->GetCache(array('city','user','hy','job')));
		$this->waptpl('addresume');
	}
	function kresume_action(){
		$row=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'");
		if(!$_POST['name']||!$_POST['hy']||!$_POST['job_classid']||!$_POST['city_classid']||!$_POST['type']||!$_POST['report']||!$_POST['jobstatus']||!$_POST['uname']||!$_POST['sex']||!$_POST['birthday']||!$_POST['edu']||!$_POST['exp']||!$_POST['telphone']||!$_POST['living']){
			$data['msg']='请将信息填写完整！';
			echo json_encode($data);die;
		}
		$min = (int)$_POST['minsalary'];$max= (int)$_POST['maxsalary'];
		if($min>$max && $max>0){
			$data['msg']='最高薪资必须大于最低薪资！';
			echo json_encode($data);die;
		}
		if($this->config['user_enforce_identitycert']=="1"){
			if($row['idcard_status']!="1"){
				$data['msg']='请先完成身份认证！';
				echo json_encode($data);die;
			}
		}
		$integrity = 55;
		
		if($this->config['resume_create_exp']=='1'){
			if(!$_POST['workname'] || !$_POST['worksdate'] || !$_POST['worktitle']){
				$data['msg']='请将信息填写完整！';
				echo json_encode($data);die;
			}
			if($_POST['workedate']&&$_POST['totoday']!=1){
				if(strtotime($_POST['workedate'])<strtotime($_POST['worksdate'])){
					$data['msg']='工作经历离职时间不能低于入职时间！';
					echo json_encode($data);die;
				}else{
					$expData['edate']  = strtotime($_POST['workedate']);
				}
			}else{
				$expData['edate']  = 0;
			}
			$expData['uid'] = $this->uid;
			$expData['name']  = $_POST['workname'];
			$expData['sdate'] = strtotime($_POST['worksdate']);
			$expData['title']  = $_POST['worktitle'];
			$expData['content']  = $_POST['workcontent'];
			$integrity += 10;
			if ($expData['edate']>0){
				$whour = ceil(($expData['edate']-$expData['sdate'])/(30*86400));
			}else{
				$whour = ceil((time()-$expData['sdate'])/(30*86400));
			}
		}
		
		if($this->config['resume_create_edu']=='1'){
			if(!$_POST['eduname'] || !$_POST['edusdate'] || !$_POST['eduedate'] || !$_POST['education'] || !$_POST['eduspec']){
				$data['msg']='请将信息填写完整！';
				echo json_encode($data);die;
			}
			if(strtotime($_POST['eduedate'])<strtotime($_POST['edusdate'])){
				$data['msg']='教育经历离校时间不能低于入学时间！';
				echo json_encode($data);die;
			}else{
				$eduData['edate']  = strtotime($_POST['eduedate']);
			}
			$eduData['uid'] = $this->uid;
			$eduData['name']  = $_POST['eduname'];
			$eduData['sdate'] = strtotime($_POST['edusdate']);
			$eduData['specialty']  = $_POST['eduspec'];
			$eduData['education']  = $_POST['education'];
			$integrity += 10;
		}
		
		if($this->config['resume_create_project']=='1'){
			if(!$_POST['proname'] || !$_POST['prosdate'] || !$_POST['protitle'] || !$_POST['proedate']){
				$data['msg']='请将信息填写完整！';
				echo json_encode($data);die;
			}
			if(strtotime($_POST['proedate'])<strtotime($_POST['prosdate'])){
				$data['msg']='项目经历结束时间不能低于开始时间！';
				echo json_encode($data);die;
			}else{
				$proData['edate']  = strtotime($_POST['proedate']);
			}
			$proData['uid'] = $this->uid;
			$proData['name']  = $_POST['proname'];
			$proData['sdate'] = strtotime($_POST['prosdate']);
			$proData['title']  = $_POST['protitle'];
			$proData['content']  = $_POST['procontent'];
			$integrity += 8;
		}
		$num=$this->obj->DB_select_num("resume_expect","`uid`='".$this->uid."'");
		if($num>=$this->config['user_number']&&$this->config['user_number']!=''){
			$data['msg']='你的简历数已经超过系统设置的简历数了！';
			$data['url']="index.php?c=resume";
			echo json_encode($data);die;
		}
		if($_POST['email']!=""){
			$email=$this->obj->DB_select_num("member","`uid`<>'".$this->uid."' and `email`='".$_POST['email']."'","`uid`");
			if($email){
				$data['msg']='邮箱已存在！';
				echo json_encode($data);die;
			}
		}
		
		$mobile=$this->obj->DB_select_once("member","`uid`<>'".$this->uid."' and `moblie`='".$_POST['telphone']."'","`uid`");
		if($mobile){
			$data['msg']='手机已存在！';
			echo json_encode($data);die;
		}
		delfiledir("../data/upload/tel/".$this->uid);
		$where['uid']=$this->uid;
		$data['edu']=$_POST['edu'];
		$data['exp']=$_POST['exp'];
		$data['name']=$_POST['uname'];
		$data['sex']=$_POST['sex'];
		$data['birthday']=$_POST['birthday'];
		$data['living']=$_POST['living'];
		if($row['moblie_status']==0){
			$data['telphone']=$_POST['telphone'];
			$mvalue['moblie']=$_POST['telphone'];
		}
		if($row['email_status']==0){
			$data['email']=$_POST['email'];
			$mvalue['email']=$_POST['email'];
		}
		$data['lastupdate']=time();
		$nid=$this->obj->update_once("resume",$data,$where);
		if($nid){
			if(!empty($mvalue)){
				$this->obj->update_once('member',$mvalue,$where);
			}
			if($row['name']==""||$row['living']==""){
				$this->MODEL('integral')->company_invtal($this->uid,$this->config['integral_userinfo'],true,"首次填写基本资料",true,2,'integral',25);
			}
			$edata=array();
			$edata['idcard_status']=$row['idcard_status'];
			$edata['status']=$row['status'];
			$edata['r_status']=$this->config['resume_status'];
			$edata['photo']=$row['photo'];
			$edata['edu']=$data['edu'];
			$edata['exp']=$data['exp'];
			$edata['uname']=$data['name'];
			$edata['sex']=$data['sex'];
			$edata['birthday']=trim($data['birthday']);
			$edata['name']=trim($_POST['name']);
			$edata['jobstatus']=(int)$_POST['jobstatus'];
			$edata['report']=(int)$_POST['report'];
			$edata['hy']=(int)$_POST['hy'];
			$edata['type']=(int)$_POST['type'];
			$edata['job_classid']=$_POST['job_classid'];
      if($_POST['minsalary']==0 && $_POST['maxsalary']){
        $edata['minsalary']=(int)$_POST['maxsalary'];
        $edata['maxsalary']=0;
      }else{
        $edata['minsalary']=(int)$_POST['minsalary'];
        $edata['maxsalary']=(int)$_POST['maxsalary'];
      }
      
			
			$edata['city_classid']=$_POST['city_classid'];



			$edata['uid']=$this->uid;
			$edata['did']=$this->userdid;
			$edata['integrity']=$integrity;
			$edata['lastupdate']=time();
			$edata['ctime']=time();
			$edata['source']=2;
			$edata['whour']=$whour;
			$edata['avghour']=$whour;
			$edata['defaults']=$num<=0?1:0;
			$eid=$this->obj->insert_into("resume_expect",$edata);
			if($eid){
			    
			    $this->morecity_insert($eid,$_POST['city_classid'],$this->uid);
			    $this->morejob_insert($eid,$_POST['job_classid'],$this->uid);
				if($num==0){
					$this->obj->update_once('resume',array('def_job'=>$eid,'resumetime'=>time()),array('uid'=>$this->uid));
					
				}else{
					$this->obj->update_once('resume',array('resumetime'=>time()),array('uid'=>$this->uid));
				}
				$userdata = array("eid"=>$eid,"uid"=>$this->uid,"expect"=>1);
				
				if(!empty($expData)){
					$expData['eid'] = $eid;
					$userdata['work'] ='1';
					$this->obj->insert_into("resume_work",$expData);
				}
				if(!empty($eduData)){
					$eduData['eid'] = $eid;
					$userdata['edu'] ='1';
					$this->obj->insert_into("resume_edu",$eduData);
				}
				if(!empty($proData)){
					$proData['eid'] = $eid;
					$userdata['project'] ='1';
					$this->obj->insert_into("resume_project",$proData);
				}
				$this->obj->insert_into("user_resume",$userdata);
				$resume_num=$num+1;
				$this->obj->DB_update_all('member_statis',"`resume_num`='".$resume_num."'","`uid`='".$this->uid."'");
				$resume_url=Url("resume",array("c"=>"show","id"=>$eid));
				$state_content = "发布了 <a href=\"".$resume_url."\" target=\"_blank\">新简历</a>。";
				$fdata['uid']	  = $this->uid;
				$fdata['content'] = $state_content;
				$fdata['ctime']   = time();
				$fdata['type']   = 2;
				$this->obj->insert_into("friend_state",$fdata);
				$this->obj->member_log("创建一份简历",2,1);
				$num=$this->obj->DB_select_num("company_pay","`com_id`='".$this->uid."' AND `pay_remark`='发布简历'");
				if($num<1){
					$this->MODEL('integral')->get_integral_action($this->uid,"integral_add_resume","发布简历");
				}
				$Warning=$this->MODEL("warning");
				$Warning->warning("3");
				$data['msg']='保存成功！';
				$data['url']="index.php?c=resume&eid=".$eid;
				echo json_encode($data);die;
			}else{
				$data['msg']='保存失败！';
				echo json_encode($data);die;
			}
		}
	}
	function addresumeson_action(){
      switch($_GET['type']){
        case 'work':$headertitle="工作经历";break;
        case 'edu':$headertitle="教育经历";break;
        case 'project':$headertitle="项目经历";break;
        case 'training':$headertitle="培训经历";break;
        case 'skill':$headertitle="职业技能";break;
        case 'other':$headertitle="其他信息";break;
        case 'desc':$headertitle="自我评价";break;
        case 'show':$headertitle="作品案例"; break;
		}
		$this->yunset('headertitle',$headertitle);
		$this->rightinfo();
		if(!in_array($_GET['type'],array('expect','desc','cert','doc','edu','other','project','show','skill','tiny','training','work'))){
			unset($_GET['type']);
		}
		if($_GET['type']=="desc"){
			$desc=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'","`description`,`tag`");
			$this->yunset("tagid",$desc['tag']);
			if($desc['tag']){
				$tag = @explode(',',$desc['tag']);
			}
			$this->yunset("arrayTag",$tag);
			$this->yunset("description",$desc['description']);
		}if($_GET['type']=="doc"){
			$row=$this->obj->DB_select_once("resume_".$_GET['type'],"`uid`='".$this->uid."' and `eid`='".$_GET['eid']."'");
			$this->yunset("row",$row);
		}if($_GET['id'] && $_GET['type']){
			$row=$this->obj->DB_select_once("resume_".$_GET['type'],"`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'");
			if($row['pic'] &&file_exists(str_replace('./',APP_PATH,$row['pic']))){
				$row['pic']=str_replace("./",$this->config['sy_weburl']."/",$row['pic']);
			}if($row['picurl'] &&file_exists(str_replace('./',APP_PATH,$row['picurl']))){
				$row['pic']=str_replace("./",$this->config['sy_weburl']."/",$row['picurl']);
			}
			$this->yunset("row",$row);
		}
		$this->yunset($this->MODEL('cache')->GetCache(array('user')));
    if($_POST['submit']){
		   $_POST=$this->post_trim($_POST);
		   if($_POST['eid']>0){
		       if($_POST['table']=='skill'){
		          $id=(int)$_POST['id'];
		          $url=$_POST['table'];
              if(is_uploaded_file($_FILES['file']['tmp_name'])){
                  $UploadM=$this->MODEL('upload');
                   $resume=$this->obj->DB_select_once("resume_skill","`id`='".$id."',","pic");
                   $upload=$UploadM->Upload_pic(APP_PATH."data/upload/user/",false);
                   $pictures=$upload->picture($_FILES['file']);
                   $picmsg = $UploadM->picmsg($pictures,$_SERVER['HTTP_REFERER']);
                   if($picmsg['status']==$pictures){
                   $data['msg']=$picmsg['msg'];
               }else{
                  $pictures = str_replace(APP_PATH."data/upload/user","./data/upload/user",$pictures);
               }
		         }
		       if(mb_strlen($pictures)!=1){  
		          if($id){
		              if($pictures==''){
		                    $nid=$this->obj->DB_update_all("resume_skill", "`uid`='".$this->uid."',`eid`='".$_POST['eid']."',`name`='".$_POST['name']."',`longtime`='".$_POST['longtime']."'","`id`='".$id."'");
		              }else{
		                   $nid=$this->obj->DB_update_all("resume_skill", "`uid`='".$this->uid."',`eid`='".$_POST['eid']."',`name`='".$_POST['name']."',`longtime`='".$_POST['longtime']."',`pic`='".$pictures."'","`id`='".$id."'");
		              }
		          }else{
		              $nid=$this->obj->DB_insert_once("resume_skill", "`uid`='".$this->uid."',`eid`='".$_POST['eid']."',`name`='".$_POST['name']."',`longtime`='".$_POST['longtime']."',`pic`='".$pictures."'");
		              $this->obj->DB_update_all("user_resume","`$url`=`$url`+1","`eid`='".(int)$_POST['eid']."' and `uid`='".$this->uid."'");
		              $resume_row=$this->obj->DB_select_once("user_resume","`eid`='".(int)$_POST['eid']."'");
		              $this->MODEL('resume')->complete($resume_row);
		           }
		           $nid?$data['msg']='保存成功！':$data['msg']='保存失败！';
		           $data['url']="index.php?c=rinfo&eid=".$_POST['eid']."&type=".$url;
		           $this->yunset("layer",$data);
		      }else{
             $data['msg']='保存成功！';
		        $data['url']="index.php?c=rinfo&eid=".$_POST['eid']."&type=".$url;
		        $this->yunset("layer",$data);
				  }
		    }
		  }
		}

		$this->get_user();
		$this->waptpl('addresumeson');
	}
	function saveresumeson_action(){
		if($_POST['submit']){
		    $_POST=$this->post_trim($_POST);
			if($_POST['table']=="resume"){
				if($_POST['tag']){
						$tag = array_unique(@explode(',',$_POST['tag']));
						foreach($tag as $value){
							$tagLen = mb_strlen($value);
							if($tagLen>=2 && $tagLen<=8){
								$tagList[] = $value;
							}
							if(count($tagList)>=5){
								break;
							}
						}
						$tagStr = implode(',',$tagList);
				}
				$this->obj->DB_update_all("resume","`tag`='".$tagStr."',`description`='".$_POST['description']."' , `lastupdate`='".time()."'","`uid`='".$this->uid."'");
				$data['url']="index.php?c=resume&eid=".$_POST['eid'];
				$data['msg']="保存成功！";
				echo json_encode($data);die;
			}
			if($_POST['table']=="doc"){
				$table="resume_".$_POST['table'];
				$this->obj->DB_update_all($table,"`doc`='".$_POST['doc']."'","`uid`='".$this->uid."' and `eid`='".$_POST['eid']."'");
				$data['url']="index.php?c=resume&eid=".$_POST['eid'];
				$data['msg']="保存成功！";
				echo json_encode($data);die;
			}
			if($_POST['eid']>0){
				
				$table="resume_".$_POST['table'];
				$id=(int)$_POST['id'];
				$url=$_POST['table'];
				unset($_POST['submit']);
				unset($_POST['table']);
				unset($_POST['id']);
				$_POST['sdate']=strtotime($_POST['sdate']);
				
				if(intval($_POST['totoday'])=='1'){
					unset($_POST['totoday']);
					$_POST['edate']='';
				}else{
					$_POST['edate']=strtotime($_POST['edate']);
				}
				
				if($table=='resume_skill'){
					
					$resume=$this->obj->DB_select_once("resume_skill","`id`='".$id."' and `eid`='".$_POST['eid']."'","`pic`");
					
					
 					if($_POST['preview']){
						$UploadM = $this->MODEL('upload');
						$upload  = $UploadM->Upload_pic(APP_PATH."/data/upload/cert/",false);
						$pic     = $upload->imageBase($_POST['preview']);
						$picmsg  = $UploadM->picmsg($pic,$_SERVER['HTTP_REFERER']);
	
						if($picmsg['status']==$pic){
							$data['msg']=$picmsg['msg'];
	 					}else{
							$_POST['pic']=str_replace(APP_PATH."/data/upload/cert/","./data/upload/cert/",$pic);
							if($resume['pic']){
								unlink_pic(APP_PATH.$resume['pic']);
							}
						}
					}else{ 
						$_POST['pic']=$resume['pic'];
					}
				}
				if($table=='resume_show'){
					$resume=$this->obj->DB_select_once("resume_show","`id`='".$id."'","`picurl`");
		       		
 		       		if($_POST['preview']){
						$UploadM = $this->MODEL('upload');
						$upload  = $UploadM->Upload_pic(APP_PATH."/data/upload/show/",false);
						$pic     = $upload->imageBase($_POST['preview']);
						$picmsg  = $UploadM->picmsg($pic,$_SERVER['HTTP_REFERER']);
	
						if($picmsg['status']==$pic){
							$data['msg']=$picmsg['msg'];
	 					}else{
							$_POST['picurl']=str_replace(APP_PATH."/data/upload/show/","./data/upload/show/",$pic);
							if($resume['picurl']){
								unlink_pic(APP_PATH.$resume['picurl']);
							}
						}
					}else{ 
						$_POST['picurl']=$resume['picurl'];
					}
		       		 
				}
				if($id){
			        $where['id']=$id;
				    $where['uid']=$this->uid;
				    $nid=$this->obj->update_once($table,$_POST,$where);
				}else{
			        $_POST['uid']=$this->uid;
				    $nid=$this->obj->insert_into($table,$_POST);
					$this->obj->DB_update_all("user_resume","`$url`=`$url`+1","`eid`='".(int)$_POST['eid']."' and `uid`='".$this->uid."'");
					$resume_row=$this->obj->DB_select_once("user_resume","`eid`='".(int)$_POST['eid']."'");
					$this->MODEL('resume')->complete($resume_row);
				}
				if($table=='resume_work'){
					
					$workList = $this->obj->DB_select_all("resume_work","`eid`='".(int)$_POST['eid']."' AND `uid`='".$this->uid."'","`sdate`,`edate`");
					$whour = 0;$hour=array();
					foreach($workList as $value){
						
						if ($value['edate']){
							$workTime = ceil(($value['edate']-$value['sdate'])/(30*86400));
						}else{
							$workTime = ceil((time()-$value['sdate'])/(30*86400));
						}
						$hour[] = $workTime;
						$whour += $workTime;
					}
					
					$avghour = ceil($whour/count($hour));
					
					$this->obj->DB_update_all("resume_expect","`whour`='".$whour."',`avghour`='".$avghour."'","`id`='".(int)$_POST['eid']."' AND `uid`='".$this->uid."'");
	            }
				
				$nid?$data['msg']='保存成功！':$data['msg']='保存失败！';
				$data['url']="index.php?c=rinfo&eid=".$_POST['eid']."&type=".$url;
			    $data['msg']=$data['msg'];
			    echo json_encode($data);die;
			}
		}
	}

	function get_email_moblie_action(){
		$row=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'","`email_status`,`moblie_status`");
		$mail=$_POST['email'];	 
		$data=array('msg'=>1);
		if($row['email_status']!=1&&!empty($mail)){
			$email=$this->obj->DB_select_num("member","`uid`<>'".$this->uid."' and `email`='".$_POST['email']."'","`uid`");
			if($email){
				$data['msg']='邮箱已存在！';
			}
		}
		if($row['moblie_status']!=1){
			$mobile=$this->obj->DB_select_once("member","`uid`<>'".$this->uid."' and `moblie`='".$_POST['moblie']."'","`uid`");
			if($mobile){
				$data['msg']='手机已存在！';
			}
		} 
		$data['msg']=$data['msg'];
		echo json_encode($data);die;
	}
	function info_action(){
		$this->yunset('headertitle',"基本信息");
		$this->rightinfo();
		include(CONFIG_PATH."db.data.php");
		unset($arr_data['sex'][3]);
		$this->yunset("arr_data",$arr_data);
		$nametype=array('1'=>'完全公开','2'=>'显示编号','3'=>'隐藏名字');
		$this->yunset("nametype",$nametype);
		if($_POST['submit']){
			$row=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'","`email_status`,`moblie_status`,`name`,`living`,`wxewm`"); 
			if($row['email_status']!='1'&&!empty($_POST['email'])){
				$email=$this->obj->DB_select_num("member","`uid`<>'".$this->uid."' and `email`='".$_POST['email']."'");
				if($email>0){
					$data['msg']='邮箱已存在！';
				}elseif(CheckRegEmail($_POST['email'])==false){
					$data['msg']='邮箱格式错误！';
				}else{
					$mvalue['email']=$_POST['email'];
				}
			}else{
				$mvalue['email']=$_POST['email'];
			}
			if($row['moblie_status']!='1'){
				 $mobile=$this->obj->DB_select_num("member","`uid`<>'".$this->uid."' and `moblie`='".$_POST['telphone']."'");
				
				if($mobile>0 && $data['msg']==""){
					$data['msg']='手机已存在！';
				}else if(!CheckMoblie($_POST['telphone'])){
					$data['msg']='手机格式不正确！';
				}else{
					$mvalue['moblie']=$_POST['telphone'];
				}
			}

			if($_POST['name']=="" && $data['msg']==""){
				$data['msg']='姓名不能为空！';
			}
			if(($_POST['birthday']=="") && $data['msg']==""){
				$data['msg']='出生年月不能为空！';
			}
			if($_POST['living']=="" && $data['msg']==""){
				$data['msg']='现居住地不能为空！';
			}
			if($data['msg']==""){
				unset($_POST['submit']);
				delfiledir("../data/upload/tel/".$this->uid);
				
				
				if($_POST['photo']){
					$pic=$this->wap_up_pic($_POST['photo'],'user');
					if($pic['errormsg']){
						$data['msg']=$pic['errormsg'];
					}
					if($pic['re']){
						$user=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'","`photo`,`resume_photo`");
						unlink_pic(APP_PATH.$user['photo']);
						$photo="./data/upload/user/".date('Ymd')."/".$pic['new_file'];
						$_POST['photo']=$_POST['resume_photo']=$photo;
					}else{
						unlink_pic(APP_PATH."data/upload/user/".date('Ymd')."/".$pic['new_file']);
						$data['msg']='请重新上传';
					}
				}
				
				if($_POST['preview']){
					
					$UploadM =$this->MODEL('upload');
					$upload  =$UploadM->Upload_pic(APP_PATH."/data/upload/user/",false);
					
					$pic     =$upload->imageBase($_POST['preview']);
					
					$picmsg  = $UploadM->picmsg($pic,$_SERVER['HTTP_REFERER']);
					if($picmsg['status']==$pic){
						$data['msg']=$picmsg['msg'];
	 				}else{
						$_POST['wxewm']=str_replace(APP_PATH."/data/upload/user/","./data/upload/user/",$pic);
						if($row['wxewm']){
							unlink_pic(APP_PATH.$row['wxewm']);
						}
					}
				} 
				  
				if($data['msg']==""){
					$_POST['lastupdate']=time();
					$where['uid']=$this->uid;  
					$nid=$this->obj->update_once("resume",$_POST,$where);
					if($nid){
						if(!empty($mvalue)){
							$this->obj->update_once('member',$mvalue,$where);
						}
						$this->member_log("保存基本信息");
						$resume=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'");
						if($row['name']==""||$row['living']=="")
						{
							$this->MODEL('integral')->company_invtal($this->uid,$this->config['integral_userinfo'],true,"首次填写基本资料",true,2,'integral',25);
						}else{
							$this->obj->update_once("resume_expect",array("edu"=>$_POST['edu'],"exp"=>$_POST['exp'],"uname"=>$_POST['name'],"sex"=>$_POST['sex'],"birthday"=>$_POST['birthday'],'photo'=>$_POST['photo']),$where);
						}
						$data['msg']='保存成功！';
					}else{
						$data['msg']='保存失败！';
					}
				}else{
					$data['msg']=$data['msg'];
				}
				if($_POST['eid']){
					$data['url']="index.php?c=resume&eid=".$_POST['eid'];
				}else{
					$data['url']="index.php";
				}
			}

			echo json_encode($data);die;
		}
		$year=date('Y',time());
		
		for($i=$year-70;$i<=$year;$i++){
			$years[]=$i;
		}
		$this->yunset("years",$years);
		$resume=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'"); 
		
 		$arr_data1=$arr_data['sex'][$resume['sex']];		
		$this->yunset("arr_data1",$arr_data1);
		if(!$resume['photo'] || !file_exists(str_replace('./',APP_PATH,$resume['photo']))){
			if ($resume['sex']==1){
				$resume['photo']=$this->config['sy_weburl']."/".$this->config['sy_member_icon'];
			}else{
				$resume['photo']=$this->config['sy_weburl']."/".$this->config['sy_member_iconv'];
			}
		}else{
			$resume['photo']=str_replace("./",$this->config['sy_weburl']."/",$resume['photo']);
		}
		$resume['wxewm']=str_replace("./",$this->config['sy_weburl']."/",$resume['wxewm']);
		$this->yunset("resume",$resume);
        
        $this->yunset($this->MODEL('cache')->GetCache(array('user')));
 		$this->waptpl('info');
	}
    function addexpect_action(){
		$this->yunset('headertitle',"意向职位修改");
    	$CacheArr=$this->MODEL('cache')->GetCache(array('city','user','hy','job'));
        $this->yunset($CacheArr);
		if($_GET['eid']){
			$row=$this->obj->DB_select_once("resume_expect","`id`='".(int)$_GET['eid']."' and `uid`='".$this->uid."'");
			$job_classid=@explode(",",$row['job_classid']);
			if(is_array($job_classid)){
				foreach($job_classid as $key){
					if($CacheArr['job_name'][$key]){
						$job_classname[]=$CacheArr['job_name'][$key];
						$jobclassid[]=$key;
					}
				}
				$row['job_classid']=@implode(',',$jobclassid);
				$this->yunset("job_classname",@implode('+',$job_classname));
			}
			$this->yunset("job_classid",$jobclassid);
			$city_classid=@explode(",",$row['city_classid']);
			if(is_array($city_classid)){
			    foreach($city_classid as $key){
			        if($CacheArr['city_name'][$key]){
			            $city_classname[]=$CacheArr['city_name'][$key];
			            $cityclassid[]=$key;
			        }
			    }
			    $row['city_classid']=@implode(',',$cityclassid);
			    $this->yunset("city_classname",@implode('+',$city_classname));
			}
			$this->yunset("city_classid",$cityclassid);
			$this->yunset("row",$row);
		}
		
		$this->get_user();
		$this->waptpl('addexpect');
	}
	function expect_action(){
    
		if($_POST){
			$eid=(int)$_POST['eid'];
			unset($_POST['submit']);
			unset($_POST['eid']);
			$where['id']=$eid;
			$where['uid']=$this->uid;
			$_POST['lastupdate']=time();
      if($_POST['minsalary']==0 && $_POST['maxsalary']!=0){
        $_POST['minsalary']=$_POST['maxsalary'];
        $_POST['maxsalary']=0;
      }
			if($eid==""){
				$num=$this->obj->DB_select_num("resume_expect","`uid`='".$this->uid."'");
				$_POST['uid']=$this->uid;
				$_POST['did']=$this->userdid;
				$_POST['source']=2;
				$_POST['defaults']=$num<=0?1:0;
				$nid=$this->obj->insert_into("resume_expect",$_POST);
				if ($nid){
				    
				    $this->morecity_insert($nid,$_POST['city_classid'],$this->uid);
				    $this->morejob_insert($nid, $_POST['city_classid'],$this->uid);
					$num=$this->obj->DB_select_once("member_statis","`uid`='".$this->uid."'");
					if($num['resume_num']==0){
						$this->obj->update_once('resume',array('def_job'=>$nid,'resumetime'=>time(),'lastupdate'=>time()),array('uid'=>$this->uid));
					}
					$data['uid'] = $this->uid;
					$data['eid'] = $nid;
					$this->obj->insert_into("user_resume",$data);
					$resume_num=$num+1;
					$this->obj->DB_update_all('member_statis',"`resume_num`='".$resume_num."'","`uid`='".$this->uid."'");
					$state_content = "发布了 <a href=\"".$this->config['sy_weburl']."/index.php?m=resume&id=$nid\" target=\"_blank\">新简历</a>。";
					$fdata['uid']	  = $this->uid;
					$fdata['content'] = $state_content;
					$fdata['ctime']   = time();
					$this->obj->insert_into("friend_state",$fdata);
					$this->member_log("发布了新简历");
				}
				$eid=$nid;
			}else{
			    $brforeresume = $this->obj->DB_select_once("resume_expect","`id`='".$eid."'","`city_classid`");
				$nid=$this->obj->update_once("resume_expect",$_POST,$where);
				
				if ($brforeresume){
				    $jobnochange=$citynochange=0;
				    if($brforeresume['city_classid']==''){
				        $citynochange=1;
				    }else{
				        $beforecity = @explode(',', $brforeresume['city_classid']);
				        $nowcity = @explode(',', $_POST['city_classid']);
				        if(array_diff($beforecity,$nowcity) || array_diff($nowcity,$beforecity)){
				            $citynochange=1;
				        }
				    }
				    if($citynochange==1){
				        $this->obj->DB_delete_all('resume_cityclass', "`eid`='".$eid."'","");
				        $this->morecity_insert($eid, $_POST['city_classid'],$this->uid);
				    }
				    if($brforeresume['job_classid']==''){
				        $jobnochange=1;
				    }else{
				        $beforejob = @explode(',', $brforeresume['job_classid']);
				        $nowjob = @explode(',', $_POST['job_classid']);
				        if(array_diff($beforejob,$nowjob) || array_diff($nowjob,$beforejob)){
				            $jobnochange=1;
				        }
				    }
				    if($jobnochange==1){
				        $this->obj->DB_delete_all('resume_jobclass', "`eid`='".$eid."'","");
				        $this->morejob_insert($eid, $_POST['job_classid'],$this->uid);
				    }
				}
				$this->member_log("更新了简历");
			}
		}
		
		echo $nid;die;
	}
	function resume_action(){
		$this->yunset('headertitle',"我的简历");
		if ($_GET['eid']){
		    $expect=$this->obj->DB_select_once("resume_expect","`uid`='".$this->uid."' and `id`='".(int)$_GET['eid']."'");
		}
		if(!$expect['id']){
			$expect=$this->obj->DB_select_once("resume_expect","`uid`='".$this->uid."' and `default`=1");
			if (!$expect){
			    $expect=$this->obj->DB_select_once("resume_expect","`uid`='".$this->uid."'order by `lastupdate` desc");
			}
		}
		include(CONFIG_PATH."db.data.php");
		unset($arr_data['sex'][3]);
		$this->yunset("arr_data",$arr_data);
		$CacheArr=$this->MODEL('cache')->GetCache(array('city','user','hy','job'));
		$this->yunset($CacheArr);
		if($expect['job_classid']){
		    $job_classid=@explode(',',$expect['job_classid']);
		    $jobname=array();
		    foreach($job_classid as $val){
		        $jobname[]=$CacheArr['job_name'][$val];
		    }
		}
		$this->yunset("jobname",@implode(' ',$jobname));
		if($expect['city_classid']){
		    $city_classid=@explode(',',$expect['city_classid']);
		    $cityname=array();
		    foreach($city_classid as $val){
		        $cityname[]=$CacheArr['city_name'][$val];
		    }
		}
		$this->yunset("cityname",@implode(' ',$cityname));
		
		$this->yunset("expect",$expect);
		$resume=$this->obj->DB_select_once("user_resume","`eid`='".$expect['id']."'");
		$this->yunset("resume",$resume);
		$user=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'");
		if($user['birthday']){
		    $a=date('Y',strtotime($user['birthday']));
		    $user['age']=date("Y")-$a;
		}
		if($user['tag']){
		    $tag = @explode(',',$user['tag']);
		}
		$this->yunset("arrayTag",$tag);
		$this->yunset("user",$user);
		$rows=$this->obj->DB_select_all("resume_expect","`uid`='".$this->uid."' order by lastupdate desc","id,name,defaults");
		foreach($rows as $key=>$val){
		    if($val['defaults']==1){
		        $rows[$key]['name']=$val['name'].'(默认)';
		    }
		}
		$this->yunset("rows",$rows);
		$maxnum=$this->config['user_number']-count($rows);
		if($maxnum<0){$maxnum='0';}
		$this->yunset("maxnum",$maxnum);
		$this->yunset("confignum",$this->config['user_number']);
		$doc=$this->obj->DB_select_once("resume_doc","`uid`='".$this->uid."' and `eid`='".$expect['id']."'","doc");
		$this->yunset("doc",$doc);
		$work=$this->obj->DB_select_all("resume_work","`uid`='".$this->uid."' and `eid`='".$expect['id']."' order by `sdate` desc");
		$this->yunset("work",$work);
		$edu=$this->obj->DB_select_all("resume_edu","`uid`='".$this->uid."' and `eid`='".$expect['id']."' order by `sdate` desc");
		$this->yunset("edu",$edu);
		$project=$this->obj->DB_select_all("resume_project","`uid`='".$this->uid."' and `eid`='".$expect['id']."' order by `sdate` desc");
		$this->yunset("project",$project);
		$training=$this->obj->DB_select_all("resume_training","`uid`='".$this->uid."' and `eid`='".$expect['id']."' order by `sdate` desc");
		$this->yunset("training",$training);
		$skill=$this->obj->DB_select_all("resume_skill","`uid`='".$this->uid."' and `eid`='".$expect['id']."' order by `id` desc");
		foreach($skill as $key=>$val){
		    if($val['pic'] &&file_exists(str_replace('./',APP_PATH,$val['pic']))){
		        $skill[$key]['pic']=str_replace("./",$this->config['sy_weburl']."/",$val['pic']);
		    }else{
				$skill[$key]['pic']='';
			}
		}
		$this->yunset("skill",$skill);
		$other=$this->obj->DB_select_all("resume_other","`uid`='".$this->uid."' and `eid`='".$expect['id']."' order by `id` desc");
		$this->yunset("other",$other);
				
		$gdeu=0;
		foreach ($edu as $v){
			if (in_array($CacheArr['userclass_name'][$v['education']],array('本科','硕士','研究生','硕士研究生','MBA','博士研究生','博士','博士后'))){
				$gdeu=1;
			}
		}
		if($gdeu==1){
			$this->yunset('heightone',1);
		}
		if(is_array($work)){
			$whour = 0;$hour=array();
			foreach($work as $value){
				if ($value['edate']){
					$workTime = ceil(($value['edate']-$value['sdate'])/(30*86400));
				}else{
					$workTime = ceil((time()-$value['sdate'])/(30*86400));
				}
				$hour[] = $workTime;
				$whour += $workTime;
			}
			$worknum = count($hour);
		}
		if($whour>24 || $worknum>3){
			$this->yunset('heighttwo',2);
		}	
		$this->rightinfo();
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('resume');
	}
    function rinfo_action(){
		switch($_GET['type']){
			case 'work':$headertitle="工作经历";
			break;
			case 'edu':$headertitle="教育经历";
			break;
			case 'project':$headertitle="项目经历";
			break;
			case 'training':$headertitle="培训经历";
			break;
			case 'skill':$headertitle="职业技能";
			break;
			case 'other':$headertitle="其他信息";
			break;
			case 'desc':$headertitle="自我评价";
			break;
			case 'show':$headertitle="作品案例";
			break;
		}
		$this->yunset('headertitle',$headertitle);
		$_GET['id']=intval($_GET['id']);
		if(!in_array($_GET['type'],array('expect','cert','doc','edu','other','project','show','skill','tiny','training','work'))){
			unset($_GET['type']);
		}
		if($_GET['type']&&intval($_GET['id'])){
			if($_GET['type']=='skill'){
				$skill=$this->obj->DB_select_once("resume_".$_GET['type'],"`id`='".(int)$_GET['id']."'","`pic`");
				unlink_pic(APP_PATH.$skill['pic']);
			}
			if($_GET['type']=='show'){
				$show=$this->obj->DB_select_once("resume_".$_GET['type'],"`id`='".(int)$_GET['id']."'","`picurl`");
				unlink_pic(APP_PATH.$show['picurl']);
			}
			$nid=$this->obj->DB_delete_all("resume_".$_GET['type'],"`eid`='".(int)$_GET['eid']."' and `id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'");
			if($nid){
				$url=$_GET['type'];
				$this->obj->DB_update_all("user_resume","`$url`=`$url`-1","`eid`='".(int)$_GET['eid']."' and `uid`='".$this->uid."'");
				$resume_row=$this->obj->DB_select_once("user_resume","`eid`='".(int)$_GET['eid']."'");
				$this->MODEL('resume')->complete($resume_row);
				if($_GET['type']=='work'){
					
					$workList = $this->obj->DB_select_all("resume_work","`eid`='".(int)$_GET['eid']."' AND `uid`='".$this->uid."'","`sdate`,`edate`");
					$whour = 0;$hour=array();
					foreach($workList as $value){
						
						if ($value['edate']){
							$workTime = ceil(($value['edate']-$value['sdate'])/(30*86400));
						}else{
							$workTime = ceil((time()-$value['sdate'])/(30*86400));
						}
						$hour[] = $workTime;
						$whour += $workTime;
					}
					
					$avghour = ceil($whour/count($hour));
					
					$this->obj->DB_update_all("resume_expect","`whour`='".$whour."',`avghour`='".$avghour."'","`id`='".(int)$_GET['eid']."' AND `uid`='".$this->uid."'");
	             }
				$data['msg']='删除成功！';
			}else{
				$data['msg']='删除失败！';
			}
            $data['url']="index.php?c=rinfo&eid=".(int)$_GET['eid']."&type=".$_GET['type'];
			$this->yunset("layer",$data);
		}
		$this->rightinfo();
		$this->yunset($this->MODEL('cache')->GetCache(array('city','user','hy','job')));
		$rows=$this->obj->DB_select_all("resume_".$_GET['type'],"`eid`='".(int)$_GET['eid']."' and `uid`='".$this->uid."'");
		$this->yunset("backurl","index.php?c=resume&eid=".intval($_GET['eid']));
		$this->yunset("rows",$rows);
		$this->yunset("type",$_GET['type']);
		$this->yunset("eid",$_GET['eid']);
		$backurl=Url('wap',array('c'=>'resume','eid'=>$_GET['eid']),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('rinfo');
	}
	function resumeset_action(){
		if($_GET['del']){
			$del=(int)$_GET['del'];
			$del_array=array("resume_cert","resume_edu","resume_other","resume_project","resume_skill","resume_training","resume_work","resume_doc","user_resume","resume_show","down_resume","userid_job","user_entrust","user_entrust_record");
			
			if($this->obj->DB_delete_all("resume_expect","`id`='".$del."' and `uid`='".$this->uid."'")){
				foreach($del_array as $v){
					$this->obj->DB_delete_all($v,"`eid`='".$del."' and `uid`='".$this->uid."'","");
					
				}
				$this->obj->DB_delete_all("look_resume","`resume_id`='".$del."'","");
				$defid=$this->obj->DB_select_once("resume","`uid`='".$this->uid."' and `def_job`='".$del."'");
			    if(is_array($defid)){
					$row=$this->obj->DB_select_once("resume_expect","`uid`='".$this->uid."'","`id`");
					if($row['id']!=''){
					    $this->obj->update_once('resume_expect',array('defaults'=>1),array('id'=>$row['id']));
					    $this->obj->update_once('resume',array('def_job'=>$row['id']),array('uid'=>$this->uid));
					}
			    } 
				$num=$this->obj->DB_select_num("resume_expect","`uid`='".$this->uid."'");
				$num=$num-1;
				$this->obj->DB_update_all('member_statis',"`resume_num`='".$num."'","`uid`='".$this->uid."'"); 
				$this->obj->DB_delete_all("resume_cityclass","`eid`='".$del."'","");
				$this->obj->DB_delete_all("resume_jobclass","`eid`='".$del."'","");
				$this->member_log("删除简历");
				$this->waplayer_msg("删除成功！");
			}else{
				$this->waplayer_msg("删除失败！");
			}
		}else if($_GET['update']){
			$id=(int)$_GET['update'];
			$nid=$this->obj->update_once('resume_expect',array('lastupdate'=>time()),array('id'=>$id,'uid'=>$this->uid));
			
			$nid?$this->waplayer_msg("刷新成功！"):$this->waplayer_msg("刷新失败！");
		}else if($_GET['def']){
			$nid=$this->obj->DB_update_all("resume","`def_job`='".(int)$_GET['def']."'","`uid`='".$this->uid."'");
            $nid=$this->obj->DB_update_all("resume_expect","`defaults`=''","`uid`='".$this->uid."'");
            $nid=$this->obj->DB_update_all("resume_expect","`defaults`='1'","`uid`='".$this->uid."' and `id`='".$_GET['def']."'");
			$nid?$this->waplayer_msg("设置成功！"):$this->waplayer_msg("设置失败！");
		}else if($_GET['open']){
			if(!in_array($_GET['type'],array('expect','cert','doc','edu','other','project','show','skill','tiny','training','work'))){
				unset($_GET['type']);
			}
			$_GET['type']?$type='1':$type='0';
			$nid=$this->obj->DB_update_all("resume_expect","`open`='".$type."'","`uid`='".$this->uid."' and `id`='".(int)$_GET['open']."'");
            $nid=$this->obj->DB_update_all("resume_expect","`defaults`=''","`uid`='".$this->uid."'");
			$nid?$this->waplayer_msg("设置成功！"):$this->waplayer_msg("设置失败！");
		}
	}
	function loginout_action(){
		$this->cookie->SetCookie("uid","",time() -86400);
		$this->cookie->SetCookie("username","",time() - 86400);
		$this->cookie->SetCookie("usertype","",time() -86400);
		$this->cookie->SetCookie("salt","",time() -86400);
		$this->cookie->SetCookie("shell","",time() -86400);
		$this->wapheader('index.php');
	}
	function lookjobdel_action(){
		$this->rightinfo();
		if($_GET['id']){
			$nid=$this->obj->DB_update_all("look_job","`status`='1'","`id`='".$_GET['id']."' and `uid`='".$this->uid."'");
			if($nid){
				$this->member_log("删除职位浏览记录（ID:".$_GET['id']."）");
				$this->waplayer_msg('删除成功！');
			}else{
				$this->waplayer_msg('删除失败！');
			}
		}
	}
	function look_job_action(){
		$this->yunset('headertitle',"职位浏览记录");
		$this->rightinfo();
		$title="职位管理";
		$urlarr=array("c"=>$_GET['c'],"page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$look=$this->get_page("look_job","`uid`='".$this->uid."' and `status`='0' order by `datetime` desc",$pageurl,"10");
		if(is_array($look)){
			include PLUS_PATH."/city.cache.php";
			include PLUS_PATH."/com.cache.php";
			foreach($look as $v){
				$jobid[]=$v['jobid'];
			}
			$job=$this->obj->DB_select_all("company_job","`id` in (".pylode(",",$jobid).")","`id`,`name`,`com_name`,`minsalary`,`maxsalary`,`provinceid`,`cityid`,`uid`,`edate`,`status`,`state`");
            
			foreach($look as $k=>$v){
				foreach($job as $val){
					if($v['jobid']==$val['id']){
						
							
						
						if($val['status']=='1'||$val['state']!='1'){
							$look[$k]['jobstate']=3;
						}else{
							$look[$k]['jobstate']=1;
						}
					   
						$look[$k]['jobname']=$val['name'];
						$look[$k]['com_id']=$val['uid'];
						$look[$k]['job_id']=$val['id'];
						$look[$k]['comname']=$val['com_name'];
						$look[$k]['minsalary']=$val['minsalary'];
						$look[$k]['maxsalary']=$val['maxsalary'];
						$look[$k]['provinceid']=$city_name[$val['provinceid']];
						$look[$k]['cityid']=$city_name[$val['cityid']];
					}
				}
			}
		}
		$this->yunset("js_def",2);
		$this->yunset("look",$look);
		$backurl=Url('wap',array('c'=>'jobcolumn'),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('look_job');
	}
	function getYears($startYear=0,$endYear=0){
		$list=array();
		$month_list=array();
		if($endYear>0){
			if($startYear<=0){
				$startYear=	$endYear-150;
			}
			for($i=$endYear;$i>=$startYear;$i--){
				$list[]=$i;
			}
		}
		for($i=12;$i>=1;$i--){
			$month_list[]=$i;
		}
		$this->yunset("year_list",$list);
		$this->yunset("month_list",$month_list);
		return $list;
	}
	function binding_action(){
		$this->yunset('headertitle',"社交账号绑定");
		if($_POST['moblie']){
			$row=$this->obj->DB_select_once("company_cert","`uid`='".$this->uid."' and `check`='".$_POST['moblie']."'");
			if(!empty($row)){
				session_start();
				if($row['check2']!=$_POST['code']){
					echo 3;die;
				}else if(!$_POST['authcode']){
					echo 4;die;
				}elseif(md5(strtolower($_POST['authcode']))!=$_SESSION['authcode'] || empty($_SESSION['authcode'])){
					echo 5;die;
				}else{
					$this->obj->DB_update_all("resume","`moblie_status`='0',`lastupdate`='".time()."'","`telphone`='".$row['check']."'");
					$this->obj->DB_update_all("company","`moblie_status`='0'","`linktel`='".$row['check']."'");
					$this->obj->DB_update_all("member","`moblie`='".$row['check']."'","`uid`='".$this->uid."'");
					$this->obj->DB_update_all("resume","`telphone`='".$row['check']."',`moblie_status`='1'","`uid`='".$this->uid."'");
					$this->obj->DB_update_all("company_cert","`status`='1'","`uid`='".$this->uid."' and `check2`='".$_POST['code']."'");
					$this->obj->member_log("手机绑定");
					$pay=$this->obj->DB_select_once("company_pay","`pay_remark`='手机绑定' and `com_id`='".$this->uid."'");
					if(empty($pay)){
						$this->MODEL('integral')->get_integral_action($this->uid,"integral_mobliecert","手机绑定");
					}
					echo 1;die;
				}
			}else{
				echo 2;die;
			}
		}
		if($_GET['type']){
			if($_GET['type']=="moblie"){
				$this->obj->DB_update_all("resume","`moblie_status`='0'","`uid`='".$this->uid."'");
			}
			if($_GET['type']=="email"){
				$this->obj->DB_update_all("resume","`email_status`='0'","`uid`='".$this->uid."'");
			}
			if($_GET['type']=="qqid"){
				$this->obj->DB_update_all("member","`qqid`=''","`uid`='".$this->uid."'");
			}
			if($_GET['type']=="wxid"){
				$this->obj->DB_update_all("member","`wxid`='',`wxopenid`='',`unionid`=''","`uid`='".$this->uid."'");
			}
			if($_GET['type']=="sinaid"){
				$this->obj->DB_update_all("member","`sinaid`=''","`uid`='".$this->uid."'");
			}
			$this->waplayer_msg('解除绑定成功！');
		}
		$member=$this->obj->DB_select_once("member","`uid`='".$this->uid."'");
		$this->yunset("member",$member);
		$resume=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'");
		$this->yunset("resume",$resume);
		if($member['restname']=="0"){
			$this->yunset("setname",1);
		}
		$this->rightinfo();
		$this->get_user();
		$this->yunset("backurl",Url('wap',array('c'=>'set'),'member'));
		$this->waptpl('binding');
	}
	function idcard_action(){
	    $this->yunset('headertitle',"实名认证");
	    if($_POST['submit']){
	        $row=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'");
	        if($_POST['name']==''){
	            $data['msg']='请输入姓名';
	        }elseif($_POST['idcard']==''){
	            $data['msg']='请输入身份证号';
	        }else if(!$_POST['preview'] && !$row['idcard_pic']){
	            $data['msg']='请上传证件照！';
	        }else{
	            if($_POST['preview']){
	                $UploadM =$this->MODEL('upload');
	                $upload  =$UploadM->Upload_pic(APP_PATH."/data/upload/cert/",false);
	                $pic     =$upload->imageBase($_POST['preview']);
	                $picmsg  = $UploadM->picmsg($pic,$_SERVER['HTTP_REFERER']);
	                
	                if($picmsg['status']==$pic){
	                    $data['msg']=$picmsg['msg'];
	                }else{
	                    $photo=str_replace(APP_PATH."/data/upload/cert/","./data/upload/cert/",$pic);
	                    if($row['idcard_pic']){
	                        unlink_pic(APP_PATH.$row['idcard_pic']);
	                    }
	                }
	            }else{
	                $photo=$row['idcard_pic'];
	            }
	        }
	        
	        if($data['msg']==""){
	            if($this->config['user_idcard_status']=="1"){
	                $status='0';
	            }else{
	                $status='1';
	            }
	            $dataarr=array(
	                'name'=>$_POST['name'],
	                'idcard'=>$_POST['idcard'],
	                'idcard_pic'=>$photo,
	                'idcard_status'=>$status,
	                'cert_time'=>time()
	            );
	            $nid=$this->obj->update_once('resume',$dataarr,array('uid'=>$this->uid));
	            
	            if($nid){
	                unlink_pic($row['idcard_pic']);
	                $data['msg']='上传成功！';
	                $data['url']="index.php?c=set";
	            }else{
	                $data['msg']='上传失败！';
	            }
	        }else{
	            
	            $data['url']="index.php?c=idcard";
	            $data['msg']=$data['msg'];
	        }
	        if($data){
	            $this->yunset("layer",$data);
	        }
	    }
	    
	    $this->rightinfo();
	    $resume=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'","`name`,`idcard`,`idcard_pic`,`statusbody`,`idcard_status`");
	    $resume['idcard_pic']=str_replace('./data', $this->config['sy_weburl'].'/data', $resume['idcard_pic']);
	    $this->yunset("resume",$resume);
	    $backurl=Url('wap',array('c'=>'set'),'member');
	    $this->yunset('backurl',$backurl);
	    $this->waptpl('idcard');
	}
	function bindingbox_action(){
		switch($_GET['type']){
			case 'moblie':$headertitle="手机认证";
			break;
			case 'email':$headertitle="邮箱认证";
			break;
		}
		$this->yunset('headertitle',$headertitle);
		$member=$this->obj->DB_select_once("member","`uid`='".$this->uid."'");
		$this->yunset("member",$member);
		$this->rightinfo();
		$backurl=Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('bindingbox');
	}
	
	function setname_action(){
		
		if($_POST['username']){
			$user=$this->obj->DB_select_once("member","`uid`='".$this->uid."'");
			if($user['restname']=="1"){
				echo "您无权修改账户！";die;
			}
			$username=$_POST['username'];
			$num = $this->obj->DB_select_num("member","`username`='".$username."'");
			if($num>0){
				echo "用户名已存在！";die;
			}
			if($this->config['sy_regname']!=""){
				$regname=@explode(",",$this->config['sy_regname']);
				if(in_array($username,$regname)){
					echo "该用户名禁止使用！";die;
				}
			}
			
			$oldpass = md5(md5($_POST['password']).$user['salt']);
			if($user['password']!=$oldpass){
				echo "密码错误！";die;
			}
    		$data['username']=$username;
		    $data['restname']=1;
			$this->obj->update_once('member',$data,array('uid'=>$this->uid));
			$this->cookie->unset_cookie();
			$this->obj->member_log("修改账户",8);
			echo 1;die;
		}
		
		$user=$this->obj->DB_select_once("member","`uid`='".$this->uid."'");
		
		if($user['restname']=="1"){
			$data['msg']="您无权修改账户！";
			$data['url']='index.php?c=set';
			$this->yunset("layer",$data);
		}
		
		$this->rightinfo();
		$backurl=Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->yunset('headertitle',"修改用户名");
		$this->waptpl('setname');
	}
	
	function reward_list_action(){
		$this->yunset('headertitle',"兑换记录");
		$urlarr['c']='reward_list';
		$urlarr["page"]="{{page}}";		
		$where = "`uid`='".$this->uid."' ";		
        $pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("change",$where."order by id desc",$pageurl,"10");
		if(is_array($rows)){
			foreach($rows as $key=>$val){
				$gid[]=$val['gid'];
			}
			$M=$this->MODEL('redeem');
			$gift=$M->GetReward(array('`id` in('.pylode(',', $gid).')'),array('field'=>'id,pic'));
			foreach($rows as $k=>$val){
				foreach ($gift as $v){
					if($val['gid']==$v['id']){
						$rows[$k]['pic']=$v['pic'];
					}
				}
			}
		}
		$change=$this->obj->DB_select_all("change","`uid`='".$this->uid."'");
		$dh = $sh = $wtg =0;
		if(is_array($change)){
			foreach($change as $value){
				if($value['status']=='0'){
					$sh +=1;
				}
				if($value['status']=='2'){
					$wtg +=1;
				}
				if($value['status']=='1'){
					$dh +=1;
				}												
			}
		}
		$this->yunset(array('dh'=>$dh,'sh'=>$sh,'wtg'=>$wtg));
		
		$this->yunset('rows',$rows);
		
		
		$statis=$this->obj->DB_select_once("member_statis","`uid`='".$this->uid."'","integral");
		$statis[integral]=number_format($statis[integral]);
		$this->yunset("statis",$statis);
		
		if($_GET['back']){
			$backurl=Url('wap',array('c'=>'redeem'));
		}else{
			$backurl=Url('wap',array('c'=>'integral'),'member');
		}
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('reward_list');
	}
	
	function privacy_action(){
		$this->yunset('headertitle',"隐私设置");
		$resume = $this->obj->DB_select_once("resume","`uid`='".$this->uid."'","`status`,`info_status`");
        $this->yunset("resume",$resume);
        $backurl=Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('privacy');
	}
	
	function up_action(){
		if(intval($_GET['status'])){ 
			$this->obj->DB_update_all("resume","`status`='".intval($_GET['status'])."'","`uid`='".$this->uid."'"); 
			$this->obj->DB_update_all("resume_expect","`status`='".intval($_GET['status'])."'","`uid`='".$this->uid."'"); 	
			echo 1;die;			
			
		}
	}
	function del_action(){
		if($_GET['id']){
			$del=(int)$_GET['id'];
			$nid=$this->obj->DB_delete_all("blacklist","`id`='".$del."' and `c_uid`='".$this->uid."'");
			if($nid){				
				$this->waplayer_msg('删除成功！');
			}else{
				$this->waplayer_msg('删除失败！');
			}
 		}
	}
	function delall_action(){
		$this->obj->DB_delete_all("blacklist","`c_uid`='".$this->uid."'","");
		$this->obj->member_log("清空公司黑名单信息");
		$this->waplayer_msg('清空成功！');
	}	
	function searchcom_action(){
		$blacklist=$this->obj->DB_select_all("blacklist","`c_uid`='".$this->uid."'","`p_uid`");
		if($blacklist&&is_array($blacklist)){
			$uids=array();
			foreach($blacklist as $val){
				$uids[]=$val['p_uid'];
			}
			$where=" and `uid` not in(".pylode(',',$uids).")";
		}
		$company=$this->obj->DB_select_all("company","`name` like '%".$this->stringfilter(trim($_POST['name']))."%' ".$where,"`uid`,`name`");
		
		if($company&&is_array($company)){
			$html="";
			foreach($company as $val){
				$html.="<li class='mui-table-view-cell mui-indexed-list-item mui-checkbox mui-left'><input type='checkbox' name='comname' value='".$val['uid']."' class='listCheckBox'  />".$val['name']."</li>";
			}
		}else{
			$html="";
		}
		
		echo $html;die;
		
	}
	function save_action(){
		if(is_array($_POST['buid'])&&$_POST['buid']){
			$company=$this->obj->DB_select_all("company","`uid` in(".pylode(',',$_POST['buid']).")","`uid`,`name`");
			foreach($company as $val){
				$this->obj->insert_into("blacklist",array('p_uid'=>$val['uid'],'c_uid'=>$this->uid,"inputtime"=>time(),'usertype'=>'1','com_name'=>$val['name']));
			}
			$this->waplayer_msg('操作成功！');
		}else{
			$this->waplayer_msg('请选择屏蔽的公司！');
		}
	}
    
	function getserver_action(){
		$eid=$_GET['id'];
		
        if($_GET['server']==1){
        	$expec=$this->obj->DB_select_once("resume_expect","`id`='".$eid."' and `uid`='".$this->uid."'","doc");
        	if($expec['doc']==0){
        		if($this->config['user_work_regiser']==1){
        			$work=$this->obj->DB_select_num("resume_work","`eid`='".$eid."' and `uid`='".$this->uid."'");
        			if($work<1){
        				$data['msg']="你的简历没有工作经历，请添加工作经历！";
        				$data['url']='index.php?c=resume&eid='.$eid.'';
        			}
        		}
        		if($this->config['user_project_regiser']==1){
        			$project=$this->obj->DB_select_num("resume_project","`eid`='".$eid."' and `uid`='".$this->uid."'");
        			if($project<1){
        				$data['msg']="你的简历没有项目经历，请添加项目经历！";
        				$data['url']='index.php?c=resume&eid='.$eid.'';
        			}
        		}
        		if($this->config['user_edu_regiser']==1){
        			$edu=$this->obj->DB_select_num("resume_edu","`eid`='".$eid."' and `uid`='".$this->uid."'");
        			if($edu<1){
        				$data['msg']="你的简历没有教育经历，请添加教育经历！";
        				$data['url']='index.php?c=resume&eid='.$eid.'';
        			}
        		}
        	} 
        }

      
            if($this->config['alipay']=='1' &&  $this->config['alipaytype']=='1'){
                $paytype['alipay']='1';
            }
            if($paytype){
                $this->yunset("paytype",$paytype);
            }
            $info=$this->obj->DB_select_once("resume_expect","`uid`='".$this->uid."' and `id`='".$eid."'");
            if($info['topdate']>1){
				$info['topdatetime']=$info['topdate'] - time();
				$info['topdate']=date("Y-m-d",$info['topdate']);
 			}else{
				$info['topdate']='未设置';
			}
        $this->yunset("info",$info);
        $this->yunset("layer",$data);
       
       
        $this->get_user();
		if($_GET['server']==1){
			$this->yunset('headertitle',"置顶简历");
		}
        $this->waptpl('getserver');
		
	}
	
	function getOrder_action(){
		if($_POST){
			$M=$this->MODEL('userpay');
			if($_POST['server']=='zdresume'){
				$return = $M->buyZdresume($_POST);
			}elseif ($_POST['server']=='wtresume'){
				$return = $M->wtResume($_POST);
			}
			
			if($return['order']['order_id'] && $return['order']['id']){
				$dingdan = $return['order']['order_id'];
				$price = $return['order']['order_price'];
				$id = $return['order']['id'];
				$this->member_log("下单成功,订单ID".$dingdan);
				$_POST['dingdan']=$dingdan;
				$_POST['dingdanname']=$dingdan;
				$_POST['alimoney']=$price;
				$data['msg']="下单成功，请付款！";
				
				if($_POST['paytype']=='alipay'){
					$url=$this->config['sy_weburl'].'/api/wapalipay/alipayto.php?dingdan='.$dingdan.'&dingdanname='.$dingdan.'&alimoney='.$price;
					header('Location: '.$url);exit();
				}
			}else{
				$data['msg']="提交失败，请重新提交订单！";
				$data['url']=$_SERVER['HTTP_REFERER'];
			}
 		}else{
			$data['msg']="参数不正确，请正确填写！";
			$data['url']=$_SERVER['HTTP_REFERER'];
		}
		$this->yunset("layer",$data);
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('pay');
	}
	
	function rtop_action(){
		$id=$_POST['id'];
		$days=intval($id);
		if($days<1){echo 1;die;}
		if(intval($_POST['eid'])<1){echo 2;die;}
		$statis=$this->obj->DB_select_once("member_statis","`uid`='".$this->uid."'","`pay`");
		$num=$days*$this->config['integral_resume_top'];
		if($num>$statis['pay']){
			echo 3;die;
			
		}else{
			
			$result=$this->MODEL('integral')->company_invtal($this->uid,$num,false,'简历置顶',true,1,'pay');
			if($result){
				$time=86400*$days;
				$topdate=$this->obj->DB_select_once("resume_expect","`id`='".intval($_POST['eid'])."' and `uid`='".$this->uid."'","topdate");
				if($topdate['topdate']>=time()){$time=$topdate['topdate']+$time;}else{$time=time()+$time;}
				$this->obj->DB_update_all("resume_expect","`top`='1',`topdate`='".$time."'","`id`='".intval($_POST['eid'])."' and `uid`='".$this->uid."'");
				
				echo 4;die;
				
			}else{
				echo 5;die;
				
			}
		}
	}


	function delreward_action(){
		if($this->usertype!='1' || $this->uid==''){
			$this->waplayer_msg('登录超时！');
		}else{
			$rows=$this->obj->DB_select_once("change","`uid`='".$this->uid."' and `id`='".(int)$_GET['id']."' ");
			if($rows['id']){
				$this->obj->DB_update_all("reward","`num`=`num`-".$rows['num'].",`stock`=`stock`+".$rows['num']."","`id`='".$rows['gid']."'");
				$this->MODEL('integral')->company_invtal($this->uid,$rows['integral'],true,"取消兑换",true,2,'integral',24);
				$this->obj->DB_delete_all("change","`uid`='".$this->uid."' and `id`='".(int)$_GET['id']."' ");
			}
			$this->obj->member_log("取消兑换");
			$this->waplayer_msg('取消成功！');
		}
	}
	
    function atncom_action(){
		$this->yunset('headertitle',"关注的企业");
        if($_GET['del']){
			$id=$this->obj->DB_delete_all("atn","`id`='".$_GET['del']."'");
			if($id){
                $this->member_log("取消关注企业");
				$this->waplayer_msg('取消成功！');
			}else{
				$this->waplayer_msg('取消失败！');
			}
		}
		$urlarr=array("c"=>"atncom","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("atn","`uid`='".$this->uid."' and `sc_usertype`='2'  order by `id` desc",$pageurl,"10");
         include PLUS_PATH."/com.cache.php";
		if($rows&&is_array($rows)){
			foreach($rows as $val){
				$uids[]=$val['sc_uid'];
			}
			$job=$this->obj->DB_select_all("company_job","`uid` in(".pylode(',',$uids).")and `status`<>1","`uid`,`name`,`id`");
			$company=$this->obj->DB_select_all("company","`uid` in(".pylode(',',$uids).")","`uid`,`name`,`pr`,`mun`,`sdate`");
			foreach($job as $v){
				$url=Url('wap',array('c'=>'job','a'=>'view','id'=>$v['id']));
				$jobname[$v['uid']][]="<a href='".$url."' target='_bank'>".$v['name']."</a>";
				
			}
			foreach($rows as $key=>$val){
				foreach($company as $v){
					if($val['sc_uid']==$v['uid']){
						$rows[$key]['com_name']=$v['name'];
						$rows[$key]['com_pr']=$comclass_name[$v['pr']];
						$rows[$key]['com_mun']=$comclass_name[$v['mun']];
						$sdate=explode('-',$v['sdate']);
						$rows[$key]['com_sdate']=$sdate[0];
					}
				}
				foreach($jobname as $k=>$v){
					if($val['sc_uid']==$k){
						$rows[$key]['jobnum']=count($v);
						$i=0;
						foreach($v as $value){
							if($i<2){
								$joblist[$key][]=$value;
							}
							$i++;
						}
						$rows[$key]['jobname']=@implode(",",$joblist[$key]);
					}
				}
			}
		}
		$this->yunset("rows", $rows);       
		
		
		$this->get_user();
        $this->waptpl('atncom');
    }
    
   
    
  
    
    
	function pay_action(){
		$this->yunset('headertitle',"充值");
		
		if($this->config['alipay']=='1' &&  $this->config['alipaytype']=='1'){
			$paytype['alipay']='1';
		}
		
		
		$banks=$this->obj->DB_select_all("bank");
		$this->yunset("banks",$banks);
		if($this->config['bank']=='1' &&  $banks){
			$paytype['bank']='1';
		}
		if($paytype){
			if($_GET['id']){
				$order=$this->obj->DB_select_once("company_order","`uid`='".$this->uid."' and `id`='".(int)$_GET['id']."'");
				if(empty($order)){ 
					$this->ACT_msg($_SERVER['HTTP_REFERER'],"订单不存在！"); 
				}elseif($order['order_state']!='1'){ 
					header("Location:index.php?c=paylog"); 
				}else{
					$this->yunset("order",$order);
				}
			}
			$this->yunset("statis",$statis);
			$remark="姓名：\n联系电话：\n留言：";
			$this->yunset("paytype",$paytype);
			$this->yunset("remark",$remark);
		}else{		
			$data['msg']="暂未开通手机支付，请移步至电脑端充值！";
			$data['url']=$_SERVER['HTTP_REFERER'];
			$this->yunset("layer",$data);
			
		}
		$nopayorder=$this->obj->DB_select_num("company_order","`uid`=".$this->uid." and `order_state`=1");
		$this->yunset('nopayorder',$nopayorder);
		$this->yunset($this->MODEL('cache')->GetCache(array('integralclass')));
		
		
		$this->get_user();
		$this->waptpl('pay');
	}
	
	function payment_action(){
 		
		if($this->config['alipay']=='1' &&  $this->config['alipaytype']=='1'){
			$paytype['alipay']='1';
		}
		$banks=$this->obj->DB_select_all("bank");
		$this->yunset("banks",$banks);
		if($this->config['bank']=='1' &&  $banks){
			$paytype['bank']='1';
		}
		
 		if($paytype){
			if($_GET['id']){
				
				$order=$this->obj->DB_select_once("company_order","`uid`='".$this->uid."' and `id`='".(int)$_GET['id']."'");
				if(empty($order)){
					$this->ACT_msg($_SERVER['HTTP_REFERER'],"订单不存在！");
				}elseif($order['order_state']!='1'){
					header("Location:index.php?c=paylog");
				}else{
					$this->yunset("order",$order);
				}
			}
 			$this->yunset("paytype",$paytype);
 			$this->yunset("js_def",4);
		}else{
			$data['msg']="暂未开通手机支付，请移步至电脑端充值！";
			$data['url']=$_SERVER['HTTP_REFERER'];
			$this->yunset("layer",$data);
		}
		
		
		
		
		$this->yunset('headertitle',"收银台");
		$this->get_user();
		$this->waptpl('payment');
	}
	
	function dingdan_action(){
		$this->yunset('headertitle',"订单");
		if($_POST['price'] || $_POST['pay']){
			if($_POST['price_int']){
				if($this->config['integral_min_recharge'] && $_POST['price_int']<$this->config['integral_min_recharge']){
					$data['msg']="充值不得低于".$this->config['integral_min_recharge'];
					$data['url']=$_SERVER['HTTP_REFERER'];
					$this->yunset("layer",$data);
					$this->waptpl('pay');exit;
				}
				$integralid=intval($_POST['integralid']);
				$CacheMclass=$this->MODEL('cache')->GetCache(array('integralclass'));
				$discount=$CacheMclass['integralclass_discount'][$integralid]/100;
				if($integralid&&$discount>0){
					$price =  $_POST['price_int']/$this->config['integral_proportion']*$discount;
				}else{
					$price = $_POST['price_int']/$this->config['integral_proportion'];
				}
				$price=floor($price*100)/100;
				
				$data['type']='2';
			}elseif($_POST['pay']){
				if($this->config['money_min_recharge'] && $_POST['pay']<$this->config['pay_min_recharge']){
					$data['msg']="充值不得低于".$this->config['money_min_recharge'];
					$data['url']=$_SERVER['HTTP_REFERER'];
					$this->yunset("layer",$data);
					$this->waptpl('money');exit;
				}
				$price = $_POST['pay'];
				$data['type']='4';
			}
			$dingdan=mktime().rand(10000,99999);
			$data['order_id']=$dingdan;
			$data['order_price']=$price;
			$data['order_time']=mktime();
			$data['order_state']="1";
			$data['order_remark']=trim($_POST['remark']);
			$data['uid']=$this->uid;
			$data['integral']=$_POST['price_int'];
			$data['did']=$this->userdid;
			$id=$this->obj->insert_into("company_order",$data);
			if($id){
				$this->member_log("下单成功,订单ID".$dingdan);
				$_POST['dingdan']=$dingdan;
				$_POST['dingdanname']=$dingdan;
				$_POST['alimoney']=$price;
				$data['msg']="下单成功，请付款！";
				
				if($_POST['paytype']=='alipay'){
					$url=$this->config['sy_weburl'].'/api/wapalipay/alipayto.php?dingdan='.$dingdan.'&dingdanname='.$dingdanname.'&alimoney='.$price;
					header('Location: '.$url);exit();
				}
			}else{
				$data['msg']="提交失败，请重新提交订单！";
				$data['url']=$_SERVER['HTTP_REFERER'];
			}
		}else{
			$data['msg']="参数不正确，请正确填写！";
			$data['url']=$_SERVER['HTTP_REFERER'];
		}
		$this->yunset("layer",$data);
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('pay');
	}

	function paybank_action(){
		
			if($_POST['bank_name']==""){
				$data['msg']="请填写汇款银行！";
				$data['url']=$_SERVER['HTTP_REFERER'];
				$this->yunset("layer",$data);
			}elseif($_POST['bank_number']==""){
				$data['msg']="请填写汇入账号！";
				$data['url']=$_SERVER['HTTP_REFERER'];
				$this->yunset("layer",$data);
			}elseif($_POST['bank_price']==""){
				$data['msg']="请填写汇款金额！";
				$data['url']=$_SERVER['HTTP_REFERER'];
				$this->yunset("layer",$data);
			}elseif($_POST['bank_time']==""){
				$data['msg']="请填写汇款时间！";
				$data['url']=$_SERVER['HTTP_REFERER'];
				$this->yunset("layer",$data);
			}
			
			$id=intval($_GET['id']);
			$orderbank=$_POST['bank_name'].'@%'.$_POST['bank_number'].'@%'.$_POST['bank_price'];
			if($_POST['bank_time']){
				$banktime=strtotime($_POST['bank_time']);
			}else{
				$banktime="";
			}
			 
			if($_POST['preview']){

				
				$UploadM =$this->MODEL('upload');
				$upload  =$UploadM->Upload_pic(APP_PATH."/data/upload/order/",false);
				
				$pic     =$upload->imageBase($_POST['preview']);
				
				$picmsg  = $UploadM->picmsg($pic,$_SERVER['HTTP_REFERER']);

				if($picmsg['status']==$pic){
					$data['msg']=$picmsg['msg'];
 				}else{
					$_POST['order_pic']=str_replace(APP_PATH."/data/upload/order/","./data/upload/order/",$pic);
				}
			} 
			
			if($id){
				$order=$this->obj->DB_select_once("company_order","`id`='".$id."' and `uid`='".$this->uid."'");
				if($order['id']){
				
					$company_order="`order_type`='bank',`order_state`='3',`order_remark`='".$_POST['remark']."',`order_pic`='".$_POST['order_pic']."',`order_bank`='".$orderbank."',`bank_time`='".$banktime."'";
					
					$this->obj->DB_update_all("company_order",$company_order,"`order_id`='".$order['order_id']."'");
					$data['msg']="操作成功，请等待管理员审核！";
					$data['url']="index.php?c=paylog";
					$this->yunset("layer",$data);
				}else{
					$data['msg']="非法操作！";
					$data['url']=$_SERVER['HTTP_REFERER'];
					$this->yunset("layer",$data);
				}
			}else{
				if($_POST['price']){
					if($_POST['comvip']){
						$comvip=(int)$_POST['comvip'];
						$ratinginfo =  $this->obj->DB_select_once("company_rating","`id`='".$comvip."'");
						if($ratinginfo['time_start']<time() && $ratinginfo['time_end']>time()){
							$price = $ratinginfo['yh_price'];
						}else{
							$price = $ratinginfo['service_price'];
						}
						$data['type']='1';
	
					}elseif($_POST['comservice']){
						$id=(int)$_POST['comservice'];
						$dkjf=(int)$_POST['dkjf'];
						$price=$_POST['dkprice'];
						$data['type']='5';
					}elseif($_POST['price_int'] || $_POST['money_int']){
						if($_POST['price_int']){
							if($this->config['integral_min_recharge'] && $_POST['price_int']<$this->config['integral_min_recharge']){
								$data['msg']="充值不得低于".$this->config['integral_min_recharge'];
								$data['url']=$_SERVER['HTTP_REFERER'];
								$this->yunset("layer",$data);
								$this->waptpl('pay');exit;
							}
							$integralid=intval($_POST['integralid']);
							$CacheMclass=$this->MODEL('cache')->GetCache(array('integralclass'));
							$discount=$CacheMclass['integralclass_discount'][$integralid]/100;
							if($integralid&&$discount>0){
								$price =  $_POST['price_int']/$this->config['integral_proportion']*$discount;
							}else{
								$price = $_POST['price_int']/$this->config['integral_proportion'];
							}
							$price=floor($price*100)/100;
							
							$data['type']='2';
						}elseif ($_POST['money_int']){
							if($this->config['money_min_recharge'] && $_POST['money_int']<$this->config['money_min_recharge']){
								$data['msg']="充值不得低于".$this->config['money_min_recharge'];
								$data['url']=$_SERVER['HTTP_REFERER'];
								$this->yunset("layer",$data);
								$this->waptpl('pay');exit;
							}
							$price = $_POST['money_int'];
							$data['type']='4';
						}
					}
					$dingdan=mktime().rand(10000,99999);
					$data['order_id']=$dingdan;
					$data['order_dkjf']=$dkjf;
					$data['order_price']=$price;
					$data['order_time']=mktime();
					$data['order_state']="3";
					$data['order_type']="bank";
					$data['order_remark']=trim($_POST['remark']);
					$data['order_pic']=$_POST['order_pic'];
					$data['order_bank']=$orderbank;
					$data['bank_time']=$banktime;
					$data['uid']=$this->uid;
					$data['rating']=$_POST['comvip']?$_POST['comvip']:$_POST['comservice'];
					$data['integral']=$_POST['price_int'];
						
					$id=$this->obj->insert_into("company_order",$data);
					if($id){
						if($_POST['comservice']){
							$this->MODEL('integral')->company_invtal($this->uid,$dkjf,$auto,"购买增值包",true,2,'integral',11);
						}
						$this->member_log("下单成功,订单ID".$dingdan);
						$data['msg']="操作成功，请等待管理员审核！";
						$data['url']="index.php?c=paylog";
						$this->yunset("layer",$data);
					}else{
						$data['msg']="提交失败，请重新提交订单！";
						$data['url']=$_SERVER['HTTP_REFERER'];
					}
				}else{
					$data['msg']="参数不正确，请正确填写！";
					$data['url']=$_SERVER['HTTP_REFERER'];
				}
				
			}
		
		$this->yunset("layer",$data);
	
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('paylog');
	}
	function paylog_action(){
		$this->yunset('headertitle',"充值记录");
		include(CONFIG_PATH."db.data.php");
		$this->yunset("arr_data",$arr_data);
		$urlarr=array("c"=>"paylog","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$where="`uid`='".$this->uid."' order by order_time desc";
		$rows=$this->get_page("company_order",$where,$pageurl,"10");
		$this->yunset("rows",$rows);
		
		$statis=$this->obj->DB_select_once("member_statis","`uid`='".$this->uid."'","integral");
		$allprice=$this->obj->DB_select_once("company_pay","`com_id`='".$this->uid."' and `type`='1' and `order_price`<0","sum(order_price) as allprice");
		if($allprice['allprice']<0){
			$statis['allprice']=number_format(str_replace("-","", $allprice['allprice']));
		}else{
			$statis['allprice']='0';
		}
		$this->yunset("statis",$statis);
		
		
		$backurl=Url('wap',array('c'=>'finance'),'member');
		$this->yunset('backurl',$backurl);
		
		$this->get_user();
		$this->waptpl('paylog');
	}
	function delpaylog_action(){
		if($this->usertype!='1' || $this->uid==''){
			$this->waplayer_msg('登录超时！');
		}else{
			$oid=$this->obj->DB_select_once("company_order","`uid`='".$this->uid."' and `id`='".(int)$_GET['id']."' and `order_state`='1'");
			if(empty($oid)){
				$this->waplayer_msg('订单不存在！');
			}else{
				$this->obj->DB_delete_all("company_order","`id`='".$oid['id']."' and `uid`='".$this->uid."'");
				$this->waplayer_msg('取消成功！');
			}
		}
	}
	
	function consume_action(){
		$this->yunset('headertitle',"消费记录");
		include(CONFIG_PATH."db.data.php");
		$this->yunset("arr_data",$arr_data);
		$urlarr=array("c"=>"consume","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$where="`com_id`='".$this->uid."'";
		$where.="  order by pay_time desc";
		$rows = $this->get_page("company_pay",$where,$pageurl,"10");
		if(is_array($rows)){
			foreach($rows as $k=>$v){
				$rows[$k]['pay_time']=date("Y-m-d H:i:s",$v['pay_time']);
				$rows[$k]['order_price']=str_replace(".00","",$rows[$k]['order_price']);
			}
		}
		
		$statis=$this->obj->DB_select_once("member_statis","`uid`='".$this->uid."'","integral");
		
		$allprice=$this->obj->DB_select_once("company_pay","`com_id`='".$this->uid."' and `type`='1' and `order_price`<0","sum(order_price) as allprice");
		
		if($allprice['allprice']<0){
			$statis['allprice']=number_format(str_replace("-","", $allprice['allprice']));
		}else{
			$statis['allprice']='0';
		}
		$this->yunset("statis",$statis);
		
		if ($_GET['type']==1){
			$this->yunset('backurl',Url('wap',array('c'=>'user'),'member'));
		}else{
			$backurl=Url('wap',array('c'=>'integral'),'member');			
		}
		$this->yunset('backurl',$backurl);
		$this->yunset("rows",$rows);
		$this->get_user();
		$this->waptpl('consume');
	}

	function likejob_action(){
		$this->rightinfo();
		if($_GET['id']){
			$id=(int)$_GET['id'];
			$resumeexp=$this->obj->DB_select_once("resume_expect","`id`='".$id."'","uid,job_classid,city_classid,exp,edu,report");
			$resume=$this->obj->DB_select_once("resume","`uid`='".$resumeexp['uid']."'","marriage");
			$where = "`sdate`<'".time()."'and `r_status`<>2 and `status`<>1 and `state`='1' ";
			if($resumeexp['job_classid']!=""){
			    $jobclass=@explode(",",$resumeexp['job_classid']);
			    foreach($jobclass as $v){
			        $job_classid[]=$v;
			    }
			    $where .=" and (`job_post` in (".@implode(" , ",$job_classid).") or `job1_son` in (".@implode(" , ",$job_classid).") or `job_post` in (".@implode(" , ",$job_classid)."))";
			}
			if($resumeexp['city_classid']!=""){
			    $cityclass=@explode(",",$resumeexp['city_classid']);
			    foreach($cityclass as $v){
			        $city_classid[]=$v;
			    }
			    $where .=" and (`provinceid` in (".@implode(" , ",$city_classid).") or `cityid` in (".@implode(" , ",$city_classid).") or three_cityid in (".@implode(" , ",$city_classid)."))";
			}
			$where.= " order by id desc limit 16";
			$select="id,name,com_name,three_cityid,edu,sex,marriage,report,exp,minsalary,maxsalary,uid";
			$job=$this->obj->DB_select_all("company_job",$where,$select);
			if(is_array($resumeexp)){
				include PLUS_PATH."/user.cache.php";
				include PLUS_PATH."/com.cache.php";
				include(CONFIG_PATH."db.data.php");
				$this->yunset("arr_data",$arr_data);
				$this->yunset("comclass_name",$comclass_name);
				foreach($job as $k=>$v){
					$job[$k]['sex']=$arr_data['sex'][$v['sex']];
					$pre=60;
					if($v['three_cityid']==$resumeexp['three_cityid']){
						$pre=$pre+10;
					}
					if($userclass_name[$resumeexp['edu']]==$comclass_name[$v['edu']] || $comclass_name[$v['edu']]=="不限"){
						$pre=$pre+5;
					}
					if($userclass_name[$resume['marriage']]==$comclass_name[$v['marriage']] || $comclass_name[$v['sex']]=="不限"){
						$pre=$pre+5;
					}
					if($job['sex']==$v['sex']){
						$pre=$pre+5;
					}
					if($userclass_name[$resumeexp['report']]==$comclass_name[$v['report']] || $comclass_name[$v['report']]=="不限"){
						$pre=$pre+5;
					}
					if($userclass_name[$resumeexp['exp']]==$comclass_name[$v['exp']] || $comclass_name[$v['exp']]=="不限"){
						$pre=$pre+5;
					}
					$job[$k]['pre']=$pre;
				}
				$sort = array(
						'direction' => 'SORT_DESC',
						'field'     => 'pre',     
				);
				$arrSort = array();
				foreach($job AS $uniqid => $row){
					foreach($row AS $key=>$value){
						$arrSort[$key][$uniqid] = $value;
					}
				}
				if($sort['direction']){
					array_multisort($arrSort[$sort['field']], constant($sort['direction']), $job);
				}
				$this->yunset("job",$job);
			}
		}
		$this->yunset("js_def",2);
		$backurl=Url('wap',array('c'=>'resume','eid'=>$_GET['id']),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('headertitle',"匹配职位");
		$this->get_user();
		$this->waptpl('likejob');
	}

	function set_action(){
		$this->yunset('headertitle',"账户设置");
		$resume=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'");
		$resume['idcard_pic']=str_replace("./data/upload/user","../data/upload/user",$resume['idcard_pic']);
		$this->yunset("resume",$resume);
		$info = $this->obj->DB_select_once("member","`uid`='".$this->uid."'","`restname`");
		if($info['restname']=="0"){
			$this->yunset("setname",1);
		}
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);

		$this->waptpl('set');
	}

	function sysnews_action(){
		$this->yunset('headertitle',"消息");
		
		$yqrows=$this->obj->DB_select_once("userid_msg","`uid`='".$this->uid."' order by datetime desc");
		$this->yunset('yqrows',$yqrows);
	    $wkyqnum=$this->obj->DB_select_num("userid_msg","`uid`='".$this->uid."' and `is_browse`='1'");
		$this->yunset("wkyqnum",$wkyqnum);
		
		$sxrows=$this->obj->DB_select_once("sysmsg","`fa_uid`='".$this->uid."' order by ctime desc");
	
		$this->yunset('sxrows',$sxrows);
		
		$sxrowsnum=$this->obj->DB_select_num("sysmsg","`fa_uid`='".$this->uid."'and `remind_status`='0'");
		$this->yunset('sxrowsnum',$sxrowsnum);
		
		
		$commsg=$this->obj->DB_select_once("msg","`uid`='".$this->uid."' and `del_status`<>'1' and `reply` <>'' and `user_remind_status`='0' order by reply_time desc");
		$this->yunset('commsg',$commsg);
	    
	    $commsgnum=$this->obj->DB_select_num("msg","`uid`='".$this->uid."'and `reply`<>'' and `user_remind_status`='0'");
		$this->yunset('commsgnum',$commsgnum);
		
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		
		$this->waptpl('sysnews');
		
	}
	
	function sxnews_action(){
		$this->yunset('headertitle',"私信");
		$where.= "`fa_uid`='".$this->uid."' order by id desc";
		$urlarr["c"] = $_GET["c"];
		$urlarr["page"] = "{{page}}";
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("sysmsg",$where,$pageurl,"10");
		if(is_array($rows)){
			$patten = array("\r\n", "\n", "\r");
			foreach($rows as $key=>$value){
			
				$rows[$key]['content_all'] = str_replace($patten, "<br/>", $value['content']);
			}
		}
		$this->yunset("rows",$rows);
		$backurl=Url('wap',array('c'=>'sysnews'),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('sxnews');
	}
	function sxnewsset_action(){
    	$id=(int)$_POST['id'];
		$remind_status=(int)$_POST['remind_status'];
		if($id&&$remind_status){
			$nid=$this->obj->update_once("sysmsg",array('remind_status'=>$remind_status),array("fa_id"=>$this->uid,"id"=>$id));
			$this->member_log("更改系统消息状态（ID:".$id."）");
		}
		$nid?$this->waplayer_msg("操作成功！"):$this->waplayer_msg("操作失败！");
    }
	function delsxnews_action(){
		if($_GET['id']){
		   $nid = $this->obj->DB_delete_all("sysmsg","`id`='".(int)$_GET['id']."' and `fa_uid`='".$this->uid."'");
		    if($nid){
			$this->obj->member_log("删除系统消息");
			$this->layer_msg('删除成功！');
		    }else{
			$this->layer_msg('删除失败！');
		    }
	    } 
	}
	
	function commsg_action(){
		$this->yunset('headertitle',"求职咨询");
        $urlarr=array("c"=>"commsg","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("msg","`uid`='".$this->uid."' order by id desc",$pageurl,"10");
        $this->yunset("rows",$rows);
        $this->obj->DB_update_all("msg","`user_remind_status`='1'","`uid`='".$this->uid."' and `user_remind_status`='0'");
		$backurl=Url('wap',array('c'=>'sysnews'),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('commsg');
	}
	
	function delcommsg_action(){
		if($_GET['id']){ 
		$nid=$this->obj->DB_delete_all("msg","`id`='".$_GET['id']."' and `uid`='".$this->uid."'");
		if($nid){  
			$this->obj->member_log("删除求职咨询");
			$this->layer_msg('删除成功！',9,0,"index.php?c=commsg");
		}else{
			$this->layer_msg('删除失败！',8,0,"index.php?c=commsg");
		}
	}
}
	function finance_action(){
		$this->yunset('headertitle',"财务管理");
		$resume=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'"); 
  		if(!$resume['photo'] || !file_exists(str_replace('./',APP_PATH,$resume['photo']))){
			if ($resume['sex']==1){
				$resume['photo']=$this->config['sy_weburl']."/".$this->config['sy_member_icon'];
			}else{
				$resume['photo']=$this->config['sy_weburl']."/".$this->config['sy_member_iconv'];
			}
		}else{
			$resume['photo']=str_replace("./",$this->config['sy_weburl']."/",$resume['photo']);
		}
		$this->yunset("resume",$resume);
        
		
		$userM  = $this->MODEL('userinfo');
		$statis = $userM->GetUserstatisOne(array('uid'=>$this->uid),array('usertype'=>1));
		
		$allprice=$this->obj->DB_select_once("company_pay","`com_id`='".$this->uid."' and `type`='1' and `order_price`<0","sum(order_price) as allprice");
		
		if($allprice['allprice']<0){
			$statis['allprice']=number_format(str_replace("-","", $allprice['allprice']));
		}else{
			$statis['allprice']='0';
		}
		
		
		$urlarr['c']=$_GET['c'];
		$urlarr["page"]="{{page}}";
		$pageurl=Url('wap',$urlarr,'member');
		$statis['freeze'] = sprintf("%.2f", $statis['freeze']);
 		
		$this->yunset("statis",$statis);
		$reg_url = Url('wap',array('c'=>'register','uid'=>$this->uid));
		$this->yunset('reg_url', $reg_url);
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);

		$this->waptpl('finance');
	}
	function integral_action(){
		$this->yunset('headertitle',"积分管理");
		$statis=$this->obj->DB_select_once("member_statis","`uid`='".$this->uid."'","`integral`");
	    $date=date("Ymd"); 
		$reg=$this->obj->DB_select_once("member_reg","`uid`='".$this->uid."' and `usertype`='".$this->usertype."' and `date`='".$date."'"); 
		if($reg['id']){
			$signstate=1;
		}else{
			$signstate=0;
		}
		
		$baseInfo			= false;	
		$photo				= false;	
		$emailChecked		= false;	
		$phoneChecked		= false;	
		$question        	=false;		
		$answer       		=false;		
		$answerpl           =false;		
		$identification		= false;	
		
		$row = $this->obj->DB_select_once("resume",'`uid` = '.$this->uid,"`name`,`sex`,`birthday`,`telphone`,`email`,`edu`,`exp`,`living`,`photo`,`email_status`,`moblie_status`,`idcard_status`");
		
		if(is_array($row) && !empty($row)){
			if($row['name'] != '' && $row['sex'] != '' && $row['birthday'] != '' && $row['telphone'] != '' && $row['edu'] != '' && $row['exp'] != '' && $row['living'] != ''){$baseInfo = true;}
			if($row['photo'] != '') {$photo = true;}
			if($row['email_status'] != 0) {$emailChecked = true;}
			if($row['moblie_status'] != 0) {$phoneChecked = true;}
			if($row['idcard_status'] != 0) {$identification = true;}
		}
		
		if($this->config['integral_question_type']=="1"){
			$question=$this->max_time('发布问题');
		}
		if($this->config['integral_answer_type']=="1"){
			$answer=$this->max_time('回答问题');
		}
		if($this->config['integral_answerpl_type']=="1"){
			$answerpl=$this->max_time('评论问答');
		}
		
		$statusList = array(
			'baseInfo'		=>$baseInfo,
			'photo'			=>$photo,
			'emailChecked'	=>$emailChecked,
			'phoneChecked'	=>$phoneChecked,
			'question'	    =>$question,
			'answer'	    =>$answer,
			'answerpl'	    =>$answerpl,
			'identification'=>$identification
		); 
		$expectnum=$this->obj->DB_select_num("resume_expect","`uid`='".$this->uid."'");
		$this->yunset("expectnum",$expectnum);
		$this->yunset("statusList",$statusList);
		
		$this->yunset("statis",$statis);
		$this->yunset("signstate",$signstate);

		$reg_url = Url('wap',array('c'=>'register','uid'=>$this->uid));
		$this->yunset('reg_url', $reg_url);
		
		if($_GET['back']){
			$backurl=Url('wap',array('c'=>'redeem'));
		}else{
			$backurl=Url('wap',array('c'=>'finance'),'member');
		}
		$this->yunset('backurl',$backurl);

		$this->waptpl('integral');
	}
			
	function integral_reduce_action(){
		$this->yunset('headertitle',"消费规则");
		$backurl=Url('wap',array('c'=>'integral'),'member');
		$this->yunset('backurl',$backurl);

		$this->waptpl('integral_reduce');
	}
		
	function blacklist_action(){
		$this->yunset('headertitle',"屏蔽企业");
		$urlarr=array("c"=>$_GET['c'],"page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$this->get_page("blacklist","`c_uid`='".$this->uid."' and `usertype`='1' order by `id` desc",$pageurl,"10");
		$backurl=Url('wap',array('c'=>'privacy'),'member');
		$this->yunset('backurl',$backurl);

		$this->waptpl('blacklist');
	}
	function blacklistadd_action(){
		
		$this->yunset('headertitle',"添加屏蔽");
		$backurl=Url('wap',array('c'=>'blacklist'),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('blacklistadd');
	}
	function jobcolumn_action(){
		
		$invitenum=$this->obj->DB_select_num("userid_msg","`uid`='".$this->uid."'");
		$this->yunset('invitenum',$invitenum);
		
		
		$sqnum=$this->obj->DB_select_num("userid_job","`uid`='".$this->uid."'");
		$this->yunset('sqnum',$sqnum);
		
		
		$collectnum=$this->obj->DB_select_num("fav_job","`uid`='".$this->uid."'");
		$this->yunset('collectnum',$collectnum);
		
		
		$atncomnum=$this->obj->DB_select_num("atn","`uid`='".$this->uid."' and `sc_usertype`='2' ");
		
		$atnnum=$atncomnum;
		$this->yunset('atnnum',$atnnum);
		
		
		$lookjobnum=$this->obj->DB_select_num("look_job","`uid`='".$this->uid."' and `status`='0'");
		$this->yunset('lookjobnum',$lookjobnum);
		
		
		$looknum=$this->obj->DB_select_num("look_resume","`uid`='".$this->uid."'");
		$this->yunset('looknum',$looknum);
		
		$this->yunset('headertitle',"职位管理");
		$wkyqnum=$this->obj->DB_select_num("userid_msg","`uid`='".$this->uid."' and `is_browse`=1");
		$this->yunset("wkyqnum",$wkyqnum);
		$wlooknum=$this->obj->DB_select_num("look_job","`jobid`='".$this->uid."' and `status`='0' and `datetime`<time()");
		$this->yunset("wlooknum",$wlooknum);
		
		
		
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('job');
	}
}
?>