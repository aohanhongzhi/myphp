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
class forgetpw_controller extends common{
	function index_action(){
		$this->get_moblie();
		$this->yunset("headertitle","找回密码");
		$this->seo("forgetpw");
		$this->yuntpl(array('wap/forgetpw'));
	}
	function sendcode_action(){
	    $sendtype = $_POST['sendtype'];
	    if ($sendtype=='moblie') {
	        $moblie = $_POST['moblie'];
	        if (CheckMoblie($moblie)){
	            if(!$this->config["sy_msguser"] || !$this->config["sy_msgpw"] || !$this->config["sy_msgkey"]||$this->config['sy_msg_isopen']!='1'){
	                $data['msg']="网站没有配置短信，请联系管理员！";
	                $data['error']=1;
	            }elseif($this->config['sy_msg_getpass']=="2"){
	                $data['msg']="网站未开启短信找回密码！";
	                $data['error']=2;
	            }
	            $num=$this->obj->DB_select_num("moblie_msg","`moblie`='".$moblie."' and `ctime`>='".strtotime(date("Y-m-d"))."'");
	            if($num>=$this->config['moblie_msgnum']){
	                $data['msg']='请不要频繁发送！';
	                $data['error']=3;
	            }
	            $ip=fun_ip_get();
	            $ipnum=$this->obj->DB_select_num("moblie_msg","`ip`='".$ip."' and `ctime`>'".strtotime(date("Y-m-d"))."'");
	            if($ipnum>=$this->config['ip_msgnum']){
	                $data['msg']='当前IP短信发送受限！';
	                $data['error']=4;
	            }
	        }else{
	            $data['msg']='手机格式错误';
	            $data['error']=5;
	        }
	    }elseif ($sendtype=='email'){
	        $email = $_POST['email'];
	        if (CheckRegEmail($email)){
	            if($this->config['sy_email_set']!="1"){
	                $data['msg']="网站邮件服务器暂不可用！";
	                $data['error']=6;
	            }elseif($this->config['sy_email_getpass']=="2"){
	                $data['msg']="网站未开启邮件找回密码！";
	                $data['error']=7;
	            }
	            $num=$this->obj->DB_select_num("company_cert","`check`='".$email."' AND `ctime`>='".strtotime(date('Y-m-d'))."'");
	            if($num>=5){
	                $data['msg']='请不要频繁发送邮件！';
	                $data['error']=8;
	            }
	        }else{
	            $data['msg']='邮箱格式错误';
	            $data['error']=9;
	        }
	    }
	    if (!$data['error']){
	        $M=$this->MODEL("userinfo");
	        if ($sendtype=='moblie') {
	            $info=$M->GetMemberOne(array('moblie'=>$moblie),array("field"=>"`uid`,`username`,`usertype`,`moblie`"));
	        }elseif ($sendtype=='email'){
	            $info=$M->GetMemberOne(array('email'=>$email),array("field"=>"`uid`,`username`,`usertype`,`email`"));
	        }
	        $fdata=$this->forsend(array('uid'=>$info['uid'],'usertype'=>$info['usertype']));
	        $data1['uid']=$info['uid'];
	        $data1['username']=$info['username'];
	        $data1['name']=$fdata['name'];
	        $data1['type']="getpass";
	        $sendcode = rand(100000,999999);
	        $data1['sendcode']=$sendcode;
	        $data1['date']=date("Y-m-d");
	        
	        $notice = $this->MODEL('notice');
	        if($sendtype=='email'){
	            $check = $data1['email']=$info['email'];
	            $notice->sendEmailType($data1);
	            $data['msg']='验证码邮件发送成功！';
	        }else if($sendtype=='moblie'){
	            $check = $data1['moblie']=$info['moblie'];
	            $result = $notice->sendSMSType($data1);
	            $data['msg']='验证码短信'.$result['msg'];
	            if($result['status'] == -1){
	                $data['msg']='短信发送失败';
	                $data['error']=5;
	                echo json_encode($data);die;
	            }
	        }
	        
	        $cert=$M->GetCompanyCert(array("uid"=>$info['uid'],"type"=>"7","check"=>$check),array("field"=>"`uid`,`check2`,`ctime`,`id`"));
	        if($cert){
	            $M->UpdateCompanyCert(array("check2"=>$sendcode,"ctime"=>time()),array("id"=>$cert['id']));
	        }else{
	            $M->AddCompanyCert(array('type'=>'7','status'=>0,'uid'=>$info['uid'],'check2'=>$sendcode,'check'=>$check,'ctime'=>time(),'did'=>$info['did']));
	        }
	        $data['error']=0;
	    }
	    echo json_encode($data);die;
	}
	function checksendcode_action(){
		$moblie=$_POST['moblie'];
		$email=$_POST['email'];
		$M=$this->MODEL("userinfo");
		if($_POST['sendtype']=='email'){
		    $info = $M->GetMemberOne(array('email'=>$email),array("field"=>"`uid`,`username`,`email`"));
			$check=$info['email'];
		}elseif($_POST['sendtype']=='moblie'){
		    $info = $M->GetMemberOne(array('moblie'=>$moblie),array("field"=>"`uid`,`username`,`moblie`"));
			$check=$info['moblie'];
		}
		$cert = $M->GetCompanyCert(array("uid"=>$info['uid'],"type"=>"7","check"=>$check),array("field"=>"`uid`,`check2`,`ctime`,`id`"));
		if(($_POST['code']!=$cert['check2'])||(!$cert)){
		    $res['msg']="验证码错误";
		    $res['type']='8';
		    echo json_encode($res);die;
		}
		$res['msg']="验证码正确！";
		$res['error']=0;
		$res['uid']=$info['uid'];
		$res['username']=$info['username'];
		echo json_encode($res);die;
	}
	function checklink_action(){
	    $_POST=$this->post_trim($_POST);
	    $username=$_POST['username'];
    $member=$this->obj->DB_select_once("member","`username`='".$_POST['username']."'","`username`");
	    if($member['username']==""){
          $res['msg']="用户名不存在！";
	        $res['error']='8';
	        echo json_encode($res);die;
      }	   
     if(CheckRegUser($username)==false && CheckRegEmail($username)==false){
	        $res['msg']="用户名包含特殊字符！";
	        $res['error']='8';
	        echo json_encode($res);die;
	    }
	     
	    $shensu=$_POST['linkman'].'-'.$_POST['linkphone'].'-'.$_POST['linkemail'];
	    $M=$this->MODEL("userinfo");
	    $nid = $M->UpdateMember(array('appeal'=>$shensu,'appealtime'=>time(),'appealstate'=>'1'),array('username'=>$username));
	    if ($nid){
	        $res['error']=0;
	        echo json_encode($res);die;
	    }
	}
	function editpw_action(){
        $username=$_POST['username'];
        $uid=$_POST['uid'];
		$mobile=$_POST['mobile'];
		$email=$_POST['email'];
		$code=$_POST['code'];
        if($username!=''&&$uid!=''){
            $M=$this->MODEL("userinfo");
            if($email!=''){
				$info = $M->GetMemberOne(array('email'=>$email),array("field"=>"`uid`,`username`,`email`"));
				$check=$info['email'];
			}elseif($mobile!=''){
				$info = $M->GetMemberOne(array('moblie'=>$mobile),array("field"=>"`uid`,`username`,`moblie`"));
				$check=$info['moblie'];
			}
			$cert = $M->GetCompanyCert(array("uid"=>$info['uid'],"type"=>"7","check"=>$check),array("field"=>"`uid`,`check2`,`ctime`,`id`"));
 			if(($code!=$cert['check2'])||(!$cert)){
				$res['msg']="验证码错误";
				$res['error']='8';
				echo json_encode($res);die;
			}else{
				$info = $M->GetMemberOne(array('uid'=>$uid),array("field"=>"`uid`,`username`,`email`,`moblie`,`name_repeat`"));
				if ($username==$info['username']){
					$password = $_POST['password'];
					if($this->config[sy_uc_type]=="uc_center" && $info['name_repeat']!="1"){
						$this->uc_open();
						uc_user_edit($info[username], "", $password, $info['email'],"0");
					}else{
						$salt = substr(uniqid(rand()), -6);
						$pass2 = md5(md5($password).$salt);
						$M->UpdateMember(array("password"=>$pass2,"salt"=>$salt),array("uid"=>$uid));
					}
					$res['msg']='密码修改成功！';
					$res['error']=0;
					echo json_encode($res);die;
				}else{
					$res['msg']='没有该用户';
				}
			}
        }else{
            $res['msg']='对不起,没有该用户';
        }
        echo json_encode($res);die;
    }
}
?>