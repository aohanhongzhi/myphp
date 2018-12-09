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
class index_controller extends common{
	function index_action(){
		$this->seo("register");
		$type = $_GET['type'];
		$this->yunset('type', $type);

		if($this->config['reg_user_stop']!=1){
			$this->yun_tpl(array('stopreg'));
		}else{
			if($this->uid!=""&&$this->username!=""){
				$this->ACT_msg($this->config['sy_weburl'], "您已经登录了！");
			}

			switch($type){
				case 1:
					if($this->config['reg_user'] != 1){
						$this->ACT_msg("index.php","用户名注册已关闭！");
					}
					break;
				case 2:
					if($this->config['reg_moblie'] != 1){
						$this->ACT_msg("index.php","手机注册已关闭！");
					}
					break;
				case 3:
					if($this->config['reg_email'] != 1){
						$this->ACT_msg("index.php","email注册已关闭！");
					}
					break;
			}
			
			if((int)$_GET['uid']){
				$time=time()+3600;
				$this->cookie->setcookie('regcode',(int)$_GET['uid'],$time);
			}
			
			if($this->config['reg_moblie']){
				$reg_url = Url("register",array("usertype"=>"1",'type'=>2),"1");
				$reg_com_url = Url("register",array("usertype"=>"2",'type'=>2),"1");
			}
			else if($this->config['reg_email']){
				$reg_url = Url("register",array("usertype"=>"1",'type'=>3),"1");
				$reg_com_url = Url("register",array("usertype"=>"2",'type'=>3),"1");
			}
			else{
				$reg_url = Url("register",array("usertype"=>"1",'type'=>1),"1");
				$reg_com_url = Url("register",array("usertype"=>"2",'type'=>1),"1");
			}

			$this->yunset('reg_url', $reg_url);
			$this->yunset('reg_com_url', $reg_com_url);
			
			if((int)$_GET['usertype']=="2"){
				$this->yun_tpl(array('company'));
			}elseif((int)$_GET['usertype']=="1"){
				$this->yun_tpl(array('user'));
			}else{
				$this->yun_tpl(array('index'));
			}
		}
	}
	function ok_action(){
		if($this->uid){
			header("location:".$this->config['sy_weburl'].'/member');
		}
		if((int)$_GET['type']==1){
			$seo=$this->config['sy_webname']."- 注册成功";
		}elseif((int)$_GET['type']==2){
			$seo=$this->config['sy_webname']."- 帐号被锁定";
		}elseif((int)$_GET['type']==3){
			$seo=$this->config['sy_webname']."- 审核未通过";
		}elseif((int)$_GET['type']==4){
			$seo=$this->config['sy_webname']."- 邮件认证成功";
		}elseif((int)$_GET['type']==5){
			$seo=$this->config['sy_webname']."- 未审核";
		}elseif((int)$_GET['type']==6){
			$seo=$this->config['sy_webname']."- 订阅";
		}else{
			header("location:".$this->config['sy_weburl']);
		}
		$this->seo('register');
		$this->yun_tpl(array('ok'));
	}
	function ajaxreg_action(){
		$post = array_keys($_POST);
		$key_name = $post[0];
		if(!in_array($key_name,array('username','email', 'realname'))){
			exit();
		}
		
		$Member=$this->MODEL("userinfo");
		if($key_name=="username"){
			$username= $_POST['username'];
			if(CheckRegUser($username)==false && CheckRegEmail($username)==false){
				echo 2;die;
			}
			if($this->config['sy_uc_type']=="uc_center"){
				$this->uc_open();
				$user = uc_get_user($username);
			}else{
				$user = $Member->GetMemberNum(array("username"=>$username));
			}
			if($this->config['sy_regname']!=""){
				$regname=@explode(",",$this->config['sy_regname']);
				if(in_array($username,$regname)){
					echo 3;die;
				}
			}
		}elseif($key_name=="email"){
			
			if(CheckRegEmail($_POST['email'])==false){
				echo 2;die;
			}else{
				$user = $Member->GetMemberNum(array("`email`='".$_POST['email']."' or `username`='".$_POST['email']."'"));
			}
			
			
		}
		elseif($key_name == 'realname'){
			$realname=$_POST['realname'];
			if($this->config['sy_regname']!=""){
				$regname=@explode(",",$this->config['sy_regname']);
				if(in_array($realname,$regname)){
					echo 3;die;
				}
			}

			if($this->config['sy_fkeyword']!=""){
				$fkeyword =@explode(",",$this->config['sy_fkeyword']);
				if(in_array($realname,$fkeyword)){
					echo 3;die;
				}
			}
		}

		if($user){echo 1;}else{echo 0;}
	}
	function regmoblie_action(){
		if($_POST['moblie']){
			$Member=$this->MODEL("userinfo");
			
			$resume_info = $Member->GetUserinfoOne(array("`telphone`= '".$_POST['moblie']."' and `moblie_status`='1'"),array('usertype'=>'1','field'=>"uid,name"));
			$company_info = $Member->GetUserinfoOne(array("`linktel`= '".$_POST['moblie']."' and `moblie_status`='1'"),array('usertype'=>'2','field'=>"uid,name"));
			
			
			$m_info = $Member->GetMemberOne(array("moblie='".$_POST['moblie']."' or `username`='".$_POST['moblie']."'"),array('field'=>"`uid`,`usertype`,`username`,`moblie`"));
			
			if($resume_info){
				$data['name']=$resume_info['name'];
				$data['uid']=$resume_info['uid'];
				$data['usertype']='1';
			}else if($company_info){
				$data['name']=$company_info['name'];
				$data['uid']=$company_info['uid'];
				$data['usertype']='2';
			}else if($m_info){
				
				if($m_info['usertype']=='1'){
					$info = $Member->GetUserinfoOne(array("`uid`= '".$m_info['uid']."'"),array('usertype'=>'1','field'=>"name"));
					$data['name']=$info['name'];
				}else if($m_info['usertype']=='2'){
					$info = $Member->GetUserinfoOne(array("`uid`= '".$m_info['uid']."'"),array('usertype'=>'2','field'=>"name"));
					$data['name']=$info['name'];
				}
				
				$data['uid']=$m_info['uid'];
				$data['usertype']=$m_info['usertype'];
			}
			if($data!=null){
				echo json_encode($data);die;
			}
			
			
			if($this->config['sy_web_mobile']!=""){
				$regnamer=@explode(";",$this->config['sy_web_mobile']);
				if(in_array($_POST['moblie'],$regnamer)){
					echo 2;die;
				}
			}
			echo 0;die;
		}
	}
	
	function regemail_action(){
		if($_POST['email']){
			$Member=$this->MODEL("userinfo");
			
			$resume_info = $Member->GetUserinfoOne(array("`email`= '".$_POST['email']."' and `email_status`='1'"),array('usertype'=>'1','field'=>"uid,name"));
			$company_info = $Member->GetUserinfoOne(array("`linkmail`= '".$_POST['email']."' and `email_status`='1'"),array('usertype'=>'2','field'=>"uid,name"));
			
			$m_info = $Member->GetMemberOne(array("email='".$_POST['email']."' or `username`='".$_POST['email']."'"),array('field'=>"`uid`,`usertype`,`username`,`email`"));
			
			if($resume_info){
				$data['name']=$resume_info['name'];
				$data['uid']=$resume_info['uid'];
				$data['usertype']='1';
			}else if($company_info){
				$data['name']=$company_info['name'];
				$data['uid']=$company_info['uid'];
				$data['usertype']='2';
			}else if($m_info){
				
				if($m_info['usertype']=='1'){
					$info = $Member->GetUserinfoOne(array("`uid`= '".$m_info['uid']."'"),array('usertype'=>'1','field'=>"name"));
					$data['name']=$info['name'];
				}else if($m_info['usertype']=='2'){
					$info = $Member->GetUserinfoOne(array("`uid`= '".$m_info['uid']."'"),array('usertype'=>'2','field'=>"name"));
					$data['name']=$info['name'];
				}
				
				$data['uid']=$m_info['uid'];
				$data['usertype']=$m_info['usertype'];
			}
			if($data!=null){
				echo json_encode($data);die;
			}
			echo 0;die;
		}
	}
	
	
	function writtenOff_action(){
		
		$Member=$this->MODEL("userinfo");
		session_start();
		
		if(md5(strtolower($_POST['code']))!=$_SESSION['authcode'] || empty($_SESSION['authcode'])){
			unset($_SESSION['authcode']);
			echo 3;die;
		}
		
		$user = $Member->GetMemberOne(array("`uid`='".$_POST['zyuid']."'"),array("field"=>"username,moblie,email,uid,password,salt,usertype"));
 		$pass = md5(md5($_POST['pw']).$user['salt']);
		if($user['password']!=$pass){
			echo 2;die;
		}else{
			
			if($_POST[mobile]!=""){
				
				if($user['username'] == $_POST['mobile']){
					$str = md5(uniqid(mt_rand(), true));   
					$new = "yun".substr($str,0,8);
					$newid = $this->obj->DB_update_all("member"," `username` = '".$new."', `restname`='0' ","`uid`='".$_POST['zyuid']."'");
				}
				
				if($user['moblie'] == $_POST['mobile']){
					$this->obj->DB_update_all("member","`moblie`=''","`uid`='".$_POST['zyuid']."'");
				}
				
				if($user['usertype']=='1'){
					$nid = $this->obj->DB_update_all("resume","`telphone`='', `moblie_status`='0' ","`uid`='".$_POST['zyuid']."'");
				}else if($user['usertype']=='2'){
					$nid = $this->obj->DB_update_all("company","`linktel`='', `moblie_status`='0' ","`uid`='".$_POST['zyuid']."'");
				}
				if($newid){
					$value="`uid`='".(int)$user['uid']."',";
					$value.="`usertype`='".(int)$user['usertype']."',";
					$value.="`content`='账号解绑：修改用户名《".$user['username']."》，新用户名《".$new."》',";
					$value.="`opera`='12',";
					$value.="`type`='2',";
					$value.="`ip`='".fun_ip_get()."',";
					$value.="`ctime`='".time()."'";
					$this->obj->DB_insert_once("member_log",$value);
				}
				
				if($nid){
					$value="`uid`='".(int)$user['uid']."',";
					$value.="`usertype`='".(int)$user['usertype']."',";
					$value.="`content`='解除绑定手机：".$_POST['mobile']."',";
					$value.="`opera`='12',";
					$value.="`type`='2',";
					$value.="`ip`='".fun_ip_get()."',";
					$value.="`ctime`='".time()."'";
					$this->obj->DB_insert_once("member_log",$value);
				}
				
				
				
			}else if($_POST['email']!=""){
				
				if($user['username'] == $_POST['email']){
					$str = md5(uniqid(mt_rand(), true));   
					$new = "yun".substr($str,0,8);
					$newid = $this->obj->DB_update_all("member"," `username` = '".$new."', `restname`='0' ","`uid`='".$_POST['zyuid']."'");
				}
				
				if($user['email'] == $_POST['email']){
					$this->obj->DB_update_all("member","`email`=''","`uid`='".$_POST['zyuid']."'");
				}
				
				if($user['usertype']=='1'){
					$nid = $this->obj->DB_update_all("resume","`email`='', `email_status`='0' ","`uid`='".$_POST['zyuid']."'");
				}else if($user['usertype']=='2'){
					$nid = $this->obj->DB_update_all("company","`linkmail`='', `email_status`='0' ","`uid`='".$_POST['zyuid']."'");
				}
				
				if($newid){
					$value="`uid`='".(int)$user['uid']."',";
					$value.="`usertype`='".(int)$user['usertype']."',";
					$value.="`content`='账号解绑：修改用户名《".$user['username']."》，新用户名《".$new."》',";
					$value.="`opera`='12',";
					$value.="`type`='2',";
					$value.="`ip`='".fun_ip_get()."',";
					$value.="`ctime`='".time()."'";
					$this->obj->DB_insert_once("member_log",$value);
				}
				
				if($nid){
					$value="`uid`='".(int)$user['uid']."',";
					$value.="`usertype`='".(int)$user['usertype']."',";
					$value.="`content`='解除绑定邮箱：".$_POST['email']."',";
					$value.="`opera`='12',";
					$value.="`type`='2',";
					$value.="`ip`='".fun_ip_get()."',";
					$value.="`ctime`='".time()."'";
					$this->obj->DB_insert_once("member_log",$value);
				}
				
			}
			
			echo 1;die;
		}					
	}
	
	function checkcomname_action(){

		if($_POST['unit_name']){
			
			$company = $this->obj->DB_select_all("company","`name` like '%".trim($_POST['unit_name'])."%'","`uid`,`name`,`linkman`,`linkphone`,`linktel`");
			
			foreach ($company as $val){
				$uids[] = $val['uid'];
			}
			
			$ComMember = $this->obj->DB_select_all("member","`uid` in (".@implode(',',$uids).")","`uid`,`claim`,`source`,`email`"); 
						
			foreach($company as $key=>$val){
				foreach($ComMember as $k => $v){
					if($val['uid']==$v['uid']){
						$company[$key]['claim']=$v['claim'];
						$company[$key]['source']=$v['source'];
						$company[$key]['email']=$v['email'];
 					}
				}
			}
			$html="";
 			if($company && is_array($company)){
 				foreach($company as $val){
 					
 					$val['name']= str_replace($_POST['unit_name'],"<font color=\"#FF0000\">".$_POST['unit_name']."</font>",$val['name']);
 					$val['linkman'] = mb_substr($val['linkman'], 0,1)."**";
 					
 					if ($val['linktel']){
					    $val['linktel']=substr_replace($val['linktel'],'****',4,4);
					}elseif ($Info['linkphone']){
					    $val['linktel']=substr_replace($val['linkphone'],'****',4,4);
					}
 					



						$html.="<div class=\"reg_com_name_list\"><span class=\"reg_comname\">".$val['name']."</span><span class=\"reg_comuser\">".$val['linkman']."</span><span class=\"reg_comtel\">".$val['linktel']."</span><span class=\"reg_comcz\"><a href=\"".Url(forgetpw)."\" class=\"reg_combth\">申诉</a></span></div>";

 					
				} 
				echo $html;die;
 			}else{
				echo 0;
			}
		}
	}
	function errjson($msg,$status='8'){
		$arr['status']=$status;
		$arr['msg']  = $msg;
		echo json_encode($arr);die;
	}
	function regsave_action(){
		if($this->config['reg_user_stop']!=1){
			$this->errjson('网站已关闭注册！');
		}
		$Member=$this->MODEL("userinfo");
		$IntegralM=$this->MODEL('integral');
		$_POST=$this->post_trim($_POST);
		$usertype=intval($_POST['usertype']);
		 
		if($this->uid!="" && $this->username!=""){
			$this->errjson('您已经登录了！');
		}
		$ip=fun_ip_get();
		if($this->config['sy_reg_interval']>0){
			$intervaltime=time()-3600*$this->config['sy_reg_interval'];
			$regnum=$Member->GetMemberNum(array('reg_ip'=>$ip,"`reg_date`>='".$intervaltime."'"));
			if($regnum){
				$this->errjson('请勿频繁注册！');
			}
		}

		if(CheckRegUser($_POST['username'])==false && CheckRegEmail($_POST['username'])==false){
			$this->errjson('用户名包含特殊字符！');
		}

		
		if(($this->config['reg_usertel']=='1' && $usertype=='1') 
				|| ($this->config['reg_comtel']=='1' && $usertype=='2')
				|| $_POST['codeid']=='2'
				|| $this->config['reg_real_name_check']=="1"
			){
			if(!preg_match("/1[3456789]{1}\d{9}$/",$_POST['moblie'])){
				$this->errjson('手机格式错误！');
			}else{
				$moblieNum = $Member->GetMemberNum(array("moblie"=>$_POST['moblie']));
				if($moblieNum>0){
					$this->errjson('手机已存在！');
				}
			}
		}

		
		$mobliestatus = 0;
		if($this->config['reg_real_name_check']=="1" || $_POST['codeid']=='2' && $this->config['sy_msg_regcode']=="1"){
			if($_POST['moblie_code']){
				$regCertMobile = $Member->GetCompanyCert(array("type"=>'2',"check"=>$_POST['moblie']));
			}
			if($regCertMobile['check2']!=$_POST['moblie_code'] || $regCertMobile['check2']==''){
				$this->errjson('短信验证码错误！');
			}else{
				$mobliestatus = 1;
			}
		}

		
		if($mobliestatus == 0){
			if(strpos($this->config['code_web'],'注册会员')!==false && $_POST['codeid']!='2'){
				session_start();
				if ($this->config['code_kind']==3){
					
					if(!gtauthcode($this->config)){
						$this->errjson("请点击按钮进行验证！");
					}
				}else{
					if(md5(strtolower($_POST['authcode']))!=$_SESSION['authcode'] || empty($_SESSION['authcode'])){
						unset($_SESSION['authcode']);
						$this->errjson('验证码错误！');
					}
				}
			}
		}

		if($usertype == 1){
			if($this->config['reg_username'] == 1 || $this->config['reg_real_name_check']== 1){
				if($_POST['name']==""){
					$this->errjson('请填写真实姓名');
				}
				else if(CheckRegUser($_POST['name'])==false){
					$this->errjson('真实姓名包含特殊字符');
				}
			}
		}
		
		if(($this->config['reg_useremail']=='1' && $usertype=='1') 
			|| ($this->config['reg_comemail']=='1' && $usertype=='2')
			|| $_POST['codeid'] == 3
		){
			if($_POST['email'] == ''){
				$this->errjson('请填写email');
			}
			else if(CheckRegEmail($_POST['email'])==false){
				$this->errjson('Email格式不规范！');
			}
		}

		if($usertype=='2'){
			if($this->config['reg_comname'] =='1'){
				if($_POST['unit_name']==""){
					$this->errjson('请正确填写企业名称！');
				}else{
					$comnameNum = $this->obj->DB_select_num("company","`name`='".$_POST['unit_name']."'");
					if($comnameNum>0){
						$this->errjson('企业名称已被使用！');
					}
				}
			}
			if($this->config['reg_comaddress'] =='1'){
				if($_POST['address']==""){
					$this->errjson('请正确填写企业地址！');
				}
			}
			if($this->config['reg_comlink'] =='1'){
				if(CheckRegUser($_POST['linkman'])==false || $_POST['linkman']==""){
					$this->errjson('请正确填写企业联系人');
				}
			}
		}

		if($_POST['codeid']=='2'){
			$_POST['username'] =  $_POST['moblie'];
		}elseif($_POST['codeid']=='3'){
			
			if(CheckRegEmail($_POST['email'])==false || $_POST['email']==""){
				$this->errjson('Email格式不规范！');
			}
			$_POST['username'] =  $_POST['email'];
		}
		
		if($_POST['username']){
			$nid = $Member->GetMemberNum(array("username"=>$_POST['username']));
			if($nid){
				$this->errjson('账户名已存在！');
			}
			
			if($_POST['usertype']=='1'){
				$satus = $this->config['user_state'];
			}elseif($_POST['usertype']=='2'){
				$satus = $this->config['com_status'];
			}

			if($this->config['sy_uc_type']=="uc_center"){
				$ucinfo = $this->uc_open();
				if(strtolower($ucinfo['UC_CHARSET']) =='utf8' || strtolower($ucinfo['UC_DBCHARSET'])=='utf8'){
					$ucusername = $_POST['username'];
				}else{
					$ucusername = $_POST['username'];
				}
				
				if(!$_POST['email']){
					$_POST['email'] = $ucinfo['UC_EMAIL'];
				}
				$uid=uc_user_register($ucusername,$_POST['password'],$_POST['email']);
				if($uid<=0){
					switch($uid){
						case "-1":$this->errjson('用户名不合法!');
						break;
						case "-2":$this->errjson('包含不允许注册的词语!');
						break;
						case "-3":$this->errjson('用户名已经存在!');
						break;
						case "-4":$this->errjson('Email 格式有误!');
						break;
						case "-5":$this->errjson('Email 不允许注册!');
						break;
						case "-6":$this->errjson('该 Email 已经被注册!');
						break;
					}
				}else{
					list($uid,$username,$password,$email,$salt)=uc_user_login($ucusername,$_POST['password']);
					$pass = md5(md5($_POST['password']).$salt);
					$ucsynlogin=uc_user_synlogin($uid);
				}
			}elseif($this->config['sy_pw_type']=="pw_center"){
				include(APP_PATH."/api/pw_api/pw_client_class_phpapp.php");
				$password=$_POST['password'];
				$email=$_POST['email'];
				$pw=new PwClientAPI($_POST['username'],$password,$email);
				$pwuid=$pw->register();
				$salt = substr(uniqid(rand()), -6);
				$pass = md5(md5($password).$salt);
			}else{
				$salt = substr(uniqid(rand()), -6);
				$pass = md5(md5($_POST['password']).$salt);
			}

			$data['username']=$_POST['username'];
			$data['password']=$pass;
			$data['usertype']=$_POST['usertype'];
			$data['email']=$_POST['email'];
			$data['moblie']=$_POST['moblie'];
			$data['did']=$this->config['did'];
			$data['status']=$satus;
			$data['salt']=$salt;
			$data['reg_date']=time();
			$data['reg_ip']=$ip;
			$data['qqid']=$_SESSION['qq']['openid'];
			$data['sinaid']=$_SESSION['sina']['openid'];
			$data['wxid']=$_SESSION['wx']['openid'];
			$data['regcode']=(int)$_COOKIE['regcode'];
			$userid=$Member->AddMember($data);
			if(!$userid){
				$user_id = $Member->GetMemberOne(array("username"=>$_POST['username']),array("field"=>"uid"));
				$userid = $user_id['uid'];
			}
			if($userid){

				$this->cookie->unset_cookie();
				if($this->config['sy_pw_type']=="pw_center"){
					$Member->UpdateMember(array("pwuid"=>$pwuid),array("uid"=>$userid));
				}
				if($_POST['usertype']=="1"){
					$table = "member_statis";
					$table2 = "resume";
					$data1=array("uid"=>$userid);
					$data2=array("uid"=>$userid,"email"=>$_POST['email'],"telphone"=>$_POST['moblie'],"name"=>$_POST['name'],'did'=>$_COOKIE['did']);
				}elseif($_POST['usertype']=="2"){
					$table = "company_statis";
					$table2 = "company";
					$data1=$Member->FetchRatingInfo(array("uid"=>$userid));
					$data2['uid']=$userid;
					$data2['linkmail']=$_POST['email'];
					if($_POST['areacode']&&$_POST['telphone']){
						$data2['linkphone']=$_POST['areacode'].'-'.$_POST['telphone'];
					}
					if($_POST['exten']){
						$data2['linkphone'].='-'.$_POST['exten'];
					}
					$data2['name']=$_POST['unit_name'];
					$data2['linktel']=$_POST['moblie'];
					$data2['address']=$_POST['address'];
					$data2['linkman']=$_POST['linkman'];
					$data2['did']=$this->config['did'];

					
					$conid = $Member->Guwen();
					$data2['conid'] = $conid['conid'];

					if($conid['crmid']){
						$data2['crm_uid'] = $conid['crmid'];
						$data2['crm_time'] = time();
					}
					
					if($this->config['com_status']==0){
						$data2['r_status']=2;
					}
					
				}

				
				if($_POST['codeid']=='2' && $this->config['sy_msg_regcode']=="1" || $this->config['reg_real_name_check']=="1"){
					
					$Member->UpdateMember(array("moblie"=>''),array("moblie"=>trim($_POST['moblie']),'uid<>'=>$userid));
					if($usertype == '1'){
						$data2['moblie_status']="1";
					}elseif($usertype == '2'){
						$data2['moblie_status']="1";
					}
				}
				$data1['did']=$this->config['did'];
				$Member->InsertReg($table,$data1);
				$Member->InsertReg($table2,$data2);
				if($_COOKIE['regcode']!=""){
					if($this->config['integral_invite_reg_type']=="1"){
						$auto=true;
					}else{
						$auto=false;
					}
					$IntegralM->company_invtal((int)$_COOKIE['regcode'],$this->config['integral_invite_reg'],$auto,"邀请注册",true,2,'integral',23);
				}
				if($this->config['integral_reg']>0){
					$IntegralM->company_invtal($userid,$this->config['integral_reg'],true,"注册赠送",true,2,'integral',23);
				}


				if($_POST['usertype']=="1"){
					if($this->config['user_status']=="1" && $_POST['email']){
						$randstr=rand(10000000,99999999);
						$base=base64_encode($userid."|".$randstr."|".$this->config['coding']);
						$data_cert['uid']=$userid;
						$data_cert['type']="cert";
						$data_cert['did']=$this->config['did'];
						$data_cert['email']=$_POST['email'];
						$data_cert['url']="<a href='".$this->config['sy_weburl']."/index.php?m=qqconnect&c=mcert&id=".$base."'>点击认证</a>";
						$data_cert['date']=date("Y-m-d");
						if($this->config['sy_email_set']=="1"){
                        $notice = $this->MODEL('notice');
                        $notice->sendEmailType($data_cert);
							$this->errjson('帐号激活邮件已发送到您邮箱，请先激活！',7);
						}else{
							$this->errjson('还没有配置邮箱，请联系管理员！');
						}
					}else{
						$_POST['uid']=$userid;
						$this->regemail($_POST);
						
						if($this->config['user_state']!="1"){
							$this->errjson('注册成功，请等待管理员审核！',6);
						}else{
						$this->MODEL('integral')->get_integral_action($userid,"integral_login","会员登录");
						$Member->UpdateMember(array("login_date"=>time(),"login_ip"=>$ip),array("uid"=>$userid));
						$this->cookie->add_cookie($userid,$_POST['username'],$salt,$_POST['email'],$pass,$usertype,1,$this->config['did']);
						$this->errjson('注册成功',1);
						}
					}
				}elseif($usertype=="2"){
					$_POST['uid']=$userid;
					$this->regemail($_POST);
					
						$this->MODEL('integral')->get_integral_action($userid,"integral_login","会员登录");
						$Member->UpdateMember(array("login_date"=>time(),"login_ip"=>$ip),array("uid"=>$userid));
						$this->cookie->add_cookie($userid,$_POST['username'],$salt,$_POST['email'],$pass,$usertype);
						$this->errjson('注册成功',1);
					
				}
			}else{
				$this->errjson('注册失败！');
			}

		}
		else{
			$this->errjson('用户名不能为空！');
		}
	}
	function regemail($post){
    $notice = $this->MODEL('notice');
		if($post['email']){
      $notice->sendEmailType(array("name"=>$post['username'],"username"=>$post['username'],"password"=>$post['password'],"email"=>$post['email'],"type"=>"reg",'uid'=>$post['uid']));
		}
		if($this->config["sy_msguser"]!="" && $this->config["sy_msgpw"]!="" 
			&& $this->config["sy_msgkey"]!=""&&$this->config['sy_msg_isopen']=='1'){

      $notice->sendSMSType(array("name"=>$post['username'],"username"=>$post['username'],"password"=>$post['password'],"moblie"=>$post['moblie'],"type"=>"reg",'uid'=>$post['uid']));
		}
	}
}
?>