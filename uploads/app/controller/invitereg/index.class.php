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
class index_controller extends common{
	function index_action(){
		if($this->uid==""){
			$this->ACT_msg($this->config['sy_weburl'], "您还没有登录，请先登录！");
		}
		if($_POST['submit']){
			if($this->config['sy_reg_invite']>0){
				$inviteregNum = $this->obj->DB_select_num('email_msg',"`uid`='".$this->uid."' AND `ctime`>'".strtotime(date('Y-m-d'))."' AND `title` LIKE '邀请注册-%'");
				if($inviteregNum>=$this->config['sy_reg_invite']){

					$this->ACT_layer_msg("今日邀请注册已达限额，请明天继续！",8,$_SERVER['HTTP_REFERER']);
					exit();
				}
			}
			
			$emailData['uid'] = $this->uid;
			$_POST['content'] = '我一直在用'.$this->config['sy_webname'].'找工作；真的很不错；忍不住的想推荐给你，快去注册吧！免费哦！链接地址：'.Url('register',array('uid'=>$this->uid));
			$authcode=md5(strtolower($_POST['authcode']));
			unset($_POST['authcode']);
			$_POST['email']=trim($_POST['email']);
			if($this->config['sy_email_set']!="1"){
				$this->ACT_layer_msg("网站邮件服务器暂不可用！",8,$_SERVER['HTTP_REFERER']);
			}
			
			if($_POST['email']==""){
				$this->ACT_layer_msg("邮件不能为空！",8,$_SERVER['HTTP_REFERER']);
			} 
			if(CheckRegEmail($_POST['email'])==false){
				$this->ACT_layer_msg("邮件格式不正确！",8,$_SERVER['HTTP_REFERER']);
			}
			if($_POST['content']==""){
				$this->ACT_layer_msg("内容不能为空！",8,$_SERVER['HTTP_REFERER']);
			}
		
			if($authcode!=$_SESSION['authcode'] || empty($_SESSION['authcode'])){
					unset($_SESSION['authcode']);
					$this->ACT_layer_msg($_POST['authcode']."验证码错误！".$_SESSION['authcode'],8);
			} 
			
			$emailData['email'] = $_POST['email'];
			$emailData['subject'] = "邀请注册-".$this->config['sy_webname'];
			$emailData['content'] = $_POST['content'];
			$notice = $this->MODEL('notice');
			$sendid = $notice->sendEmail($emailData);

			if($sendid['status'] != -1){
				$this->ACT_layer_msg("邀请注册邮件已发送！",9,$_SERVER['HTTP_REFERER']);
			}else{
				$this->ACT_layer_msg("邀请注册邮件发送失败！",8,$_SERVER['HTTP_REFERER']);
			}
		}

		if($this->config['reg_moblie']){
			$type = 2;
		}
		else if($this->config['reg_email']){
			$type = 3;
		}
		else{
			$type = 1;
		}
		$reg_url = Url('register', array('uid'=>$this->uid), '1');
		$this->yunset('reg_url', $reg_url);

		$this->seo("invitereg");
		$this->yun_tpl(array('index'));
	}
}
?>