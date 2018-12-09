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
class binding_controller extends company{
	function index_action(){
		$member=$this->obj->DB_select_once("member","`uid`='".$this->uid."'");
		$this->yunset("member",$member);
		if($member['restname']=="0"){
			$this->yunset("setname",1);
		}
		$company=$this->obj->DB_select_once("company","`uid`='".$this->uid."'");
		$this->yunset("company",$company);
		$cert=$this->obj->DB_select_once("company_cert","`uid`='".$this->uid."' and type='3'");
		if($cert&&is_array($cert)){
			if($cert['check'] && file_exists(str_replace('./',APP_PATH,$cert['check']))){
				$cert['check']=str_replace('./',$this->config['sy_weburl'].'/',$cert['check']);
			}else{
				$cert['check']="";
			}
		}
		$this->yunset("cert",$cert);
		$this->public_action();
		$this->company_satic();
		$this->get_user();
		$this->yunset("js_def",2);
		$this->com_tpl("binding");
	}
 
	function save_action(){
		if($_POST['moblie']){
            $row=$this->obj->DB_select_once("company_cert","`uid`='".$this->uid."' and `check`='".$_POST['moblie']."' and `type`='2'");
			if(!empty($row)){
				if($row['check2']!=$_POST['code']){
					echo 3;die; 
				}
				$this->obj->DB_update_all("member","`moblie`=''","`moblie`='".$row['check']."'");
				$this->obj->DB_update_all("resume","`moblie_status`='0',`telphone`=''","`telphone`='".$row['check']."'");
				$this->obj->DB_update_all("company","`moblie_status`='0',`linktel`=''","`linktel`='".$row['check']."'");
				$this->obj->DB_update_all("member","`moblie`='".$row['check']."'","`uid`='".$this->uid."'");
				$this->obj->DB_update_all("company","`linktel`='".$row['check']."',`moblie_status`='1'","`uid`='".$this->uid."'");
				$this->obj->DB_update_all("company_cert","`status`='1'","`uid`='".$this->uid."' and `check2`='".$_POST['code']."'");
				$this->obj->member_log("手机绑定");
				$pay=$this->obj->DB_select_once("company_pay","`pay_remark`='手机绑定' and `com_id`='".$this->uid."'");
				if(empty($pay)){
				    $IntegralM=$this->MODEL('integral');
					$IntegralM->get_integral_action($this->uid,"integral_mobliecert","手机绑定");
				}
				echo 1;die;
			}else{
				echo 2;die;
			}
		}
	}
	function savecert_action(){
	    $row=$this->obj->DB_select_once("company_cert","`uid`='".$this->uid."' and type='3'");
	    if($this->config['com_cert_status']=="1"){
	        $sql['status']=0;
	    }else{
	        $sql['status']=1;
	    }
	    $sql['step']=1;
	    $sql['check'] = $this->checksrc($_POST['check'],$row['check']);
	    $sql['check2']="0";
	    $sql['ctime']=mktime();
	    $where['uid']=$this->uid;
	    $where['id']=$row['id'];
	    $where['type']='3';
	    if(is_array($row)&&$row['ctime']){
	        $id=$this->obj->update_once("company_cert",$sql,$where);
	        $this->obj->member_log("更新营业执照");
	    }else{
	        $sql['uid'] = $this->uid;
	        $sql['type']=3;
	        $sql['did']=$this->userdid;
	        $id=$this->obj->insert_into("company_cert",$sql);
	        $this->obj->member_log("上传营业执照");
	        if($this->config['com_cert_status']!="1"){
	            $IntegralM=$this->MODEL('integral');
	            $IntegralM->get_integral_action($this->uid,"integral_comcert","认证营业执照");
	        }
	    }
	    
	    if ($_POST['upfile']=='info'){
	        if($id){
	            $data['url']=$sql['check'];
	            $this->obj->DB_update_all("company","`name`='".$_POST['company_name']."',`yyzz_status`='".$sql['status']."'","`uid`='".$this->uid."'");
	        }else{
	            $data['msg']='上传失败';
	        }
	        echo json_encode($data);
	    }else{
	        if($id){
	            $company_name=$_POST['company_name'];
	            $this->obj->DB_update_all("company","`name`='".$company_name."',`yyzz_status`='".$sql['status']."'","`uid`='".$this->uid."'");
	            $this->ACT_layer_msg("上传成功！",9,$_SERVER['HTTP_REFERER']);
	        }else{
	            $this->ACT_layer_msg("上传失败！",8);
	        }
	    }
	}
	function del_action(){
		if($_GET['type']=="moblie"){
			$this->obj->DB_update_all("company","`moblie_status`='0'","`uid`='".$this->uid."'");
		}
		if($_GET['type']=="email"){
			$this->obj->DB_update_all("company","`email_status`='0'","`uid`='".$this->uid."'");
		}
		if($_GET['type']=="qqid"){
			$this->obj->DB_update_all("member","`qqid`=''","`uid`='".$this->uid."'");
		}
		if($_GET['type']=="sinaid"){
			$this->obj->DB_update_all("member","`sinaid`=''","`uid`='".$this->uid."'");
		}
		if($_GET['type']=="wxid"){
			$this->obj->DB_update_all("member","`wxid`='',`wxopenid`='',`unionid`=''","`uid`='".$this->uid."'");
		}
		$this->layer_msg("解除绑定成功！",9,0,$_SERVER['HTTP_REFERER']);
	}
}
?>