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
class upload_controller extends common{
	
	private $tokenSalt = 'phpyun';

	
	private function generateToken($type, $uid)
	{
		
		$row = $this->obj->DB_select_once('member', "`uid`={$uid}", '`password`');
		$password = isset($row['password']) ? $row['password'] : '';
		$password = substr($password, 0, 8);
		
		$this->tokenSalt = $this->config['sy_safekey'];
		return encrypt("{$type}|{$uid}|{$password}", $this->tokenSalt);
	}

	
	private function checkToken($token)
	{
		$token = urldecode($token);
		
		$this->tokenSalt = $this->config['sy_safekey'];
		$str = decrypt($token, $this->tokenSalt);
		$arr = explode('|', $str);
		if(count($arr) != 3 || $arr[1] == ''){
			return false;
		}

		
		$uid = $arr[1];
		$row = $this->obj->DB_select_once('member', "`uid`={$uid}", '`password`');
		$password = isset($row['password']) ? $row['password'] : '';
		$password = substr($password, 0, 8);
		if($password != $arr[2]){
			return false;
		}

		return array('uid' => $uid, 'type' => $arr[0]);
	}

	
	public function qrcode_action()
	{
		if(!$this->uid){
			exit('请先登录登录');
		}
		
		
		$type = isset($_GET['type']) ? $_GET['type'] : '';
		if($type == ''){
			exit('扫码上传图片可选类型type：1企业营业执照上传，2个人身份证上传，3个人头像');
		}

		$token = $this->generateToken($type, $this->uid);
		$token = urlencode($token);
		$url = Url('wap',array('c'=> 'upload', 'a' => 'p', 't' => $token) );

		include_once LIB_PATH."yunqrcode.class.php";
		YunQrcode::generatePng2($url, 4);
	}

	
	public function p_action(){
		$token = isset($_GET['t']) ? $_GET['t'] : '';
		$arr = $this->checkToken($token);
		if($arr == false || !isset($arr['type']) || !isset($arr['uid']) ){
			exit('抱歉，功能维护中');
		}
		
		$this->yunset('token', $token);
		$this->yunset('type', $arr['type']);
		
		$this->seo("wap_upload");

		if($arr['type'] == 3){
			$photo = $this->obj->DB_select_once("resume", "`uid`='".$arr['uid']."'" , '`resume_photo`,`sex`');
			if(!$photo['resume_photo'] || !file_exists(str_replace('./',APP_PATH,$photo['resume_photo']))){
				if ($photo['sex']==1){
					$photo['photo']=$this->config['sy_weburl']."/".$this->config['sy_member_icon'];
				}else{
					$photo['photo']=$this->config['sy_weburl']."/".$this->config['sy_member_iconv'];
				}
			}else{
				$photo['photo']=str_replace("./",$this->config['sy_weburl']."/",$photo['resume_photo']);
			}
			$this->yunset('photo',$photo['photo']);
			$this->yuntpl(array('wap/uploadimg_userlogo'));
		}
		else{
			$this->yuntpl(array('wap/uploadimg'));
		}
	}

	
	public function uploadimg_save_action(){
		$token = isset($_GET['token']) ? $_GET['token'] : '';
		if($token == ''){
			echo json_encode(array('status' => -1, 'msg' => '二维码传图出错，请联系网站管理员'));
			exit;
		}
		$arr = $this->checkToken($token);
		if($arr == false || !isset($arr['type']) || !isset($arr['uid']) ){
			echo json_encode(array('status' => -1, 'msg' => '操作超时，请刷新pc端网页二维码重试' . $token));
			exit;
		}

		$path = $this->uploadimg_save_path($arr['type'], $arr['uid']);
		
		if($path != ''){
			echo json_encode(array('status' => 1, 'path' => $path));
			exit;
		}else{
			echo json_encode(array('status' => -1, 'msg' => '上传失败，请重试'));
			exit;
		}
	}

	
	private function uploadimg_save_path($type, $uid)
	{
		$uid = addslashes($uid);

		switch($type){
			case 1://上传企业营业执照		
				$pic = $this->upload();
				$path = str_replace("../data/upload/","./data/upload/", $pic);

				$row = $this->obj->DB_select_once("company_cert","`uid`='{$uid}' and `type`=3");
				if(is_array($row)){
					$id=$this->obj->DB_update_all('company_cert',"`check`='".$path."'","`uid`='".$uid."' and `type`=3");
					if($id){
						@unlink_pic(".".$row['check']);
						$this->obj->member_log("更新营业执照");
						return $pic;
					}
				}
				else{
					if($this->config['com_cert_status']=="1"){
						$sql['status']=0;
					}else{
						$sql['status']=1;
					}

					$sql['step']=1;
					$sql['check'] = $path;
					$sql['check2']="0";
					$sql['ctime']=mktime();

					$sql['uid']=$uid;
					
					$sql['type']=3;
					$id=$this->obj->insert_into("company_cert",$sql);
					$this->obj->member_log("上传营业执照");
					if($this->config['com_cert_status']!="1"){
						$IntegralM->MODEL('integral')->get_integral_action($uid,"integral_comcert","认证营业执照");
					}
					if($id){
						return $pic;
					}
				}
				break;

			case 2://个人上传身份证
				$pic = $this->upload();
				
				$path = str_replace("../data/upload/","./data/upload/", $pic);

				$resume=$this->obj->DB_select_once("resume","`uid`='".$uid."'","idcard_pic");
				$pictures = $this->checksrc($path,$resume['idcard_pic']);

				if($this->config['user_idcard_status']=="1"){
		        $status='0';
		    }else{
		        $status='1';
		    }
		    $id=$this->obj->DB_update_all('resume',"`idcard_pic`='".$pictures."',`idcard`='".addslashes($_POST['idcard'])."',`idcard_status`='".$status."',`cert_time`='".time()."'","`uid`='".$uid."'");
		    $this->obj->DB_update_all('resume_expect',"`idcard_status`='".$status."'","`uid`='".$uid."'");
		    
		    if($id){
		    	$_COOKIE['uid'] = $uid;
		    	$_COOKIE['usertype'] = 1;
	        $this->obj->member_log("上传身份验证图片");
		      return $pictures;
		    }

				break;

			case 3://个人上传头像
				preg_match('/^(data:\s*image\/(\w+);base64,)/', $_POST['uimage'], $result);

				$uimage=str_replace($result[1], '', str_replace('#','+',$_POST['uimage']));
				if(in_array(strtolower($result[2]),array('jpg','png','gif','jpeg'))){
					$new_file = time().rand(1000,9999).".".$result[2];
				}else{
					$new_file = time().rand(1000,9999).".jpg";
				}
				$im = imagecreatefromstring(base64_decode($uimage));
				if ($im === false) {
					break;
				}
				if (!file_exists(DATA_PATH."upload/user/".date('Ymd')."/")){
					mkdir(DATA_PATH."upload/user/".date('Ymd')."/");
					chmod(DATA_PATH."upload/user/".date('Ymd')."/",0777);
				}
				$re=file_put_contents(DATA_PATH."upload/user/".date('Ymd')."/".$new_file, base64_decode($uimage));
				chmod(DATA_PATH."upload/user/".date('Ymd')."/".$new_file,0777);

				if($re){
					$user=$this->obj->DB_select_once("resume","`uid`='".$uid."'","`photo`,`resume_photo`");
					if($user['photo']||$user['resume_photo']){
						unlink_pic(APP_PATH.$user['photo']);
						unlink_pic(APP_PATH.$user['resume_photo']);
					}
					$photo="./data/upload/user/".date('Ymd')."/".$new_file;
					$this->obj->DB_update_all("resume","`resume_photo`='".$photo."',`photo`='".$photo."'","`uid`='".$uid."'");
					$this->obj->DB_update_all("resume_expect","`photo`='".$photo."'","`uid`='".$uid."'");
					return $photo;
				}else{
					unlink_pic("../data/upload/user/".date('Ymd')."/".$new_file);
					break;
				}

				break;
		}
		return '';
	}

	private function upload(){
		
		if($_POST['preview']){
			
			$UploadM =$this->MODEL('upload');
			
			$upload  =$UploadM->Upload_pic("../data/upload/cert/",false);
			
			$pic     =$upload->imageBase($_POST['preview']);
			
			$result  = $UploadM->picmsg($pic,$_SERVER['HTTP_REFERER']);
			
			if($result === true){
				return $pic;		
			}else{
				echo json_encode(array('status' => $pic, 'msg' => $result['msg']));
				exit;
			}
		}
		else{
			echo json_encode(array('status' => -1, 'msg' => '请上传图片'));
			exit;
		}
	}

	
}
?>