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
class wxconnect_controller extends common{
    function wxlogin_check_action(){
        if($_POST['username']!="" && $_POST['password']!=""){
            $user = $this->obj->DB_select_once("member","`username`='$username'","`uid`,`salt`,`password`");
            if(is_array($user)){
                $pass = md5(md5($_POST['password']).$user['salt']);
                if($pass==$user['password']){
                    $this->obj->DB_update_all("member","`login_date`='".time()."'","`uid`='".$user[uid]."'");
                    echo "成功";
                }
            }else{
                echo "<font color='red'>该用户不存在</font>";
            }
        }else{
            echo "<font color='red'>用户名或密码不能为空！</font>";
        }
    }
    function index_action(){
		if($this->config['wx_rz']!="1"){
		    if((int)$_GET['login']=="1"){
		        $this->ACT_msg(Url("wap").'/member/', $msg = "对不起，微信绑定已关闭！");
		    }else{
		        $this->ACT_msg(Url("wap"), $msg = "对不起，微信登录已关闭！");
		    }
		}
		$this->seo('wxlogin');
		$app_id = $this->config['wx_appid'];
		$app_secret = $this->config['wx_appsecret'];
		$my_url = $this->config['sy_wapdomain']."/index.php?c=wxconnect";
		$code = $_GET['code'];
		session_start();
		if(empty($code)){
			$this->cookie->unset_cookie();
			$_SESSION['wx']['state'] = md5(uniqid(rand(), TRUE));

			$dialog_url ="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$app_id."&redirect_uri=".urlencode($my_url)."&response_type=code&scope=snsapi_userinfo&state=".$_SESSION['wx']['state']."#wechat_redirect";
			header("location:".$dialog_url);
		}
		
		if($_GET['state'] == $_SESSION['wx']['state']){

			$token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $app_id . "&secret=" . $app_secret . "&code=".$code."&grant_type=authorization_code";
			if(function_exists('curl_init')) {

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$token_url);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				$response=curl_exec ($ch);
				curl_close ($ch);

				$user = json_decode($response,true);
				
				if($user['openid']){
					
					$userinfo = $this->obj->DB_select_once("member","`wxid`='".$user['openid']."' OR (`unionid`='".$user['unionid']."' AND `unionid`<>'')");
					if(is_array($userinfo) && !empty($userinfo)){
					    $this->obj->update_once('member',array('login_date'=>time(),'unionid'=>$user['unionid']),array('uid'=>$userinfo['uid']));
						$logtime=date("Ymd",$userinfo['login_date']);
						$nowtime=date("Ymd",time());
						if($logtime!=$nowtime){
							$this->MODEL('integral')->get_integral_action($userinfo['uid'],"integral_login","会员登录");
						} 
						if($this->config['sy_uc_type']=="uc_center"){
							$this->uc_open();
							$uc_user = uc_get_user($userinfo['username']);
							$ucsynlogin=uc_user_synlogin($uc_user[0]);
							$msg =  '登录成功！';

							
							header("location:".$this->config['sy_wapdomain'].'/member/');
						}else{
							$this->cookie->add_cookie($userinfo['uid'],$userinfo['username'],$userinfo['salt'],$userinfo['email'],$userinfo['password'],$userinfo['usertype'],1,$userinfo['did']);
							
							header("location:".$this->config['sy_wapdomain'].'/member/');
						}
					}else{
						$_SESSION['wx']["openid"] = $user['openid'];
						$_SESSION['wx']["unionid"] = $user['unionid'];
						$_SESSION['wx']["tooken"] = $user['access_token'];
						$_SESSION['wx']["logininfo"] = "您已登录微信，请绑定您的帐户！";
						if($this->uid){
							$this->obj->DB_update_all("member","`wxopenid`='',`wxid`='',`unionid`=''","`wxid`='".$_SESSION['wx']["openid"]."' OR (`unionid`='".$_SESSION['wx']['unionid']."' AND `unionid`<>'')");
							$this->obj->DB_update_all("member","`wxid`='".$_SESSION['wx']["openid"]."',`unionid`='".$_SESSION['wx']["unionid"]."'","`uid`='".$this->uid."'");
							
							header("location:".$this->config['sy_wapdomain'].'/member/index.php?c=binding');

						}else{
							
							$nick_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$user['access_token'].'&openid='.$user['openid'].'&lang=zh_CN';
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL,$nick_url);
							curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
							$response=curl_exec ($ch);
							curl_close ($ch);

							$user = json_decode($response,true);
							
							if($user['nickname']){
								$_SESSION['wx']['nickname'] = $user['nickname'];
								$_SESSION['wx']['pic'] = $user['headimgurl'];

								header("location:".Url('wap',array('c'=>'wxconnect','a'=>'wxbind')));
							}else{
								$this->ACT_msg(Url("wap"),"用户信息获取失败，请重新登录！",8);
							}
						}
					}
					
				}
			}
		}else{
			  echo("The state does not match. You may be a victim of CSRF.");
		}

	}
    function wxbind_action(){
		session_start();
		if($_POST){
			
			
		
			if($_SESSION['wx']['openid']){
				$bindInfo['wxid'] =$_SESSION['wx']['openid'];
				$bindInfo['wxunionid'] =$_SESSION['wx']['unionid'];
				$Member=$this->MODEL("userinfo");
				$return  = $Member->bindUser($_POST['username'],$_POST['password'],$bindInfo);
				if($return['error']=='1'){
					
					$userinfo = $return['info'];
					$this->cookie->add_cookie($userinfo['uid'],$userinfo['username'],$userinfo['salt'],$userinfo['email'],$userinfo['password'],$userinfo['usertype'],1,$userinfo['did']);
					$bind['url'] = Url("wap").'/member/';
				}else{
					$bind['msg'] =$return['msg'];
				}
		
			}else{
			
				$bind['msg'] = '微信登录信息已失效，请重新登录！';
			}
			echo json_encode($bind);
		}else{
			$this->yunset("headertitle","微信登录绑定");
			$this->yunset('wxlogin');
			$this->yuntpl(array('wap/wxbind'));
		}
	}
}
?>