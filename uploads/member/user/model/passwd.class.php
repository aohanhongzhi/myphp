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
class passwd_controller extends user{
	
	function index_action(){
		$this->public_action();
		$resume=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'");
		$resume['idcard_pic']=str_replace("./data","/data",$resume['idcard_pic']);
		$this->yunset("resume",$resume);
		if($_POST['submit']){
			$info = $this->obj->DB_select_once("member","`uid`='".$this->uid."'","`salt`,`password`,`name_repeat`,`username`");
			if(is_array($info)){
				$oldpass = md5(md5($_POST['oldpassword']).$info['salt']);
                if($info['password']!=$oldpass){
					$this->ACT_layer_msg("原始密码错误！", 8,"index.php?c=passwd");
				}
				$newpass1 = md5(md5($_POST['newpassword']).$info['salt']);
				if($newpass1==$oldpass){
					$this->ACT_layer_msg("新密码和原始密码相同！", 8,"index.php?c=passwd");
				}
				if($this->config['sy_uc_type']=="uc_center" &&$info['name_repeat']!="1"){
					$this->uc_open();
					$ucresult= uc_user_edit($info['username'], $_POST['oldpassword'], $_POST['newpassword'], "","1");
					if($ucresult == -1){
						$this->ACT_layer_msg("原始密码错误！", 8,"index.php?c=passwd");
					}
				}else{
					$salt = substr(uniqid(rand()), -6);
				    $newpass2 = md5(md5($_POST['newpassword']).$salt);
					$this->obj->update_once('member',array('password'=>$newpass2,'salt'=>$salt),array('uid'=>$this->uid));

				}
				$this->cookie->unset_cookie();
				$this->obj->member_log("修改密码",8);
				$this->ACT_layer_msg("修改成功，请重新登录！", 9,$this->config['sy_weburl']."/index.php?m=login");
			}
		}

		
		if($_POST['submit2']){
			
			
			$username = isset($_POST['username']) ? $_POST['username'] : '';
            if(mb_strlen($username)<2 || mb_strlen($username)>16){
				$this->ACT_layer_msg("请输入2-16位字符！", 8);
			}elseif(CheckRegUser($username)==false && CheckRegEmail($username)==false){
				$this->ACT_layer_msg("用户名不得包含特殊字符！", 8);
			}
			
			$info = $this->obj->DB_select_once("member","`uid`='".$this->uid."'","`salt`,`password`,`restname`,`username`");
			if(is_array($info)){
				
				$oldpass = md5(md5($_POST['password']).$info['salt']);
        		if($info['password']!=$oldpass){
					$this->ACT_layer_msg("密码错误！", 8);
				}

				
				if($info['restname'] == 1){
					$this->ACT_layer_msg("只可以修改用户名一次！", 8,"index.php?c=passwd");
				}

				
				$num = $this->obj->DB_select_num('member', "`username` = '{$username}'");
				if($num > 0){
					$this->ACT_layer_msg("用户名 {$username} 已被使用！", 8);
				}
			}

			$this->obj->update_once('member', array('username'=>$username, 'restname' => 1), array('uid'=>$this->uid));

			$this->obj->member_log("修改用户名", 11);
			$this->cookie->unset_cookie();
			$this->ACT_layer_msg("修改成功，请重新登录！", 9 ,$this->config['sy_weburl']."/index.php?m=login");
		}
		$info = $this->obj->DB_select_once("member","`uid`='".$this->uid."'","`salt`,`password`,`restname`,`username`");
		if($info['restname'] == 1){
			$this->yunset("restname",1);
		}
		$this->user_tpl('passwd');
	}

}
?>