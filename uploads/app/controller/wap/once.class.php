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
class once_controller extends common{
	function index_action(){
		$this->rightinfo();
		if($this->config['sy_once_web']=="2"){
			$data['msg']='很抱歉！该模块已关闭！';
			$data['url']='index.php';
			$this->yunset("layer",$data);
		}
		$this->get_moblie();
		$this->seo("once");
		$this->yunset("topplaceholder","请输入招聘关键字,如：服务员...");
		$M=$this->MODEL('once');
		$ip=fun_ip_get();
		$start_time=strtotime(date('Y-m-d 00:00:00')); 
		$mess=$M->GetOncejobNum(array('login_ip'=>$ip,'`ctime`>\''.$start_time.'\''));
		$num=$this->config['sy_once']-$mess;
		$this->yunset("num",$num);				
		$CacheM=$this->MODEL('cache');
        $CacheArr=$CacheM->GetCache(array('city'));
		$this->yunset($CacheArr);
		$this->yunset("headertitle","店铺招聘");
		$this->yuntpl(array('wap/once'));
	}
	
	function add_action(){ 
 		$ip=fun_ip_get();
		$this->yunset("ip",$ip);
		$this->rightinfo();		
		$CacheM=$this->MODEL('cache');
        $CacheArr=$CacheM->GetCache(array('city'));
		$this->yunset($CacheArr);
		if($this->config['sy_once_web']=="2"){
			$data['msg']='很抱歉！该模块已关闭！';
			$data['url']='index.php';
			$this->yunset("layer",$data);
		}
		$this->get_moblie();
		$TinyM=$this->MODEL('once');

		if((int)$_GET['id']){
            $row=$TinyM->GetOncejobOne(array('id'=>(int)$_GET[id]));
			$row['edate']=ceil(($row['edate']-$row['ctime'])/86400) ;
			if(!$row['pic'] || !file_exists(str_replace('./',APP_PATH,$row['pic']))){
				$row['pic']=$this->config['sy_weburl']."/".$this->config['sy_unit_icon'];
			}else{
				$row['pic']=str_replace("./",$this->config['sy_weburl']."/",$row['pic']);
			}
			$this->yunset("row",$row);
		}else{
			$row['pic']=$this->config['sy_weburl']."/".$this->config['sy_unit_icon'];
			$this->yunset("row",$row);
			if($this->config['once_pay_price']!="0" && $this->config['once_pay_price']!="" && $_COOKIE['fast'] ){
				$orderNum = $this->obj->DB_select_num("company_order","`order_state`='1' and `type` = '25' and `fast`='".$_COOKIE['fast']."'");
				$this->yunset("num",$orderNum);
			}
		}
		
		if($_POST['submit']){
			if(strpos($this->config['code_web'],'店铺招聘')!==false){
			    session_start();
			   
				if($this->config['code_kind']==3){
					 
					if(!gtauthcode($this->config,'mobile')){
						$data['msg']='请点击按钮进行验证！';
						$this->layer_msg($data['msg'],9,0,'',2);
					}
			    }else{
			        if(md5(strtolower($_POST['checkcode']))!=$_SESSION['authcode'] || empty($_SESSION['authcode'])){
			            $data['msg']='验证码错误！';
						unset($_SESSION['authcode']);
						$this->layer_msg($data['msg'],10,0,'',2);
			        }
					unset($_SESSION['authcode']);
			    }
			}
			
			$password=md5(trim($_POST['password']));
			$id=intval($_POST['id']);
			
			if($_POST['preview']){
				
				$UploadM =$this->MODEL('upload');
				$upload  =$UploadM->Upload_pic(APP_PATH."/data/upload/once/",false);
				
				$pic     =$upload->imageBase($_POST['preview']);
				
				$picmsg  = $UploadM->picmsg($pic,$_SERVER['HTTP_REFERER']);
				if($picmsg['status']==$pic){
					$data['msg']=$picmsg['msg'];
 				}else{
					$_POST['pic']=str_replace(APP_PATH."/data/upload/once/","./data/upload/once/",$pic);
				}
			} 
			if($id < 1){
				
				$start_time=strtotime(date('Y-m-d 00:00:00')); 
				$mess=$TinyM->GetOncejobNum(array('login_ip'=>$ip,'`ctime`>\''.$start_time.'\'')); 
				
				if($this->config['sy_once']<=$mess&&$this->config['sy_once']){
					$data['msg']="一天内只能发布".$this->config['sy_once']."次！";
					$data['url']=Url('wap',array('c'=>'once'));
				}else{
					if($this->config['once_pay_price']!="0" && $this->config['once_pay_price']!=""){
					    $sql['pay']='1';
					}
					$sql['mans']=$_POST['mans'];
					$sql['title']=$_POST['title'];
					$sql['require']=$_POST['require'];
					$sql['companyname']=$_POST['companyname'];
					$sql['phone']=$_POST['phone'];
					$sql['linkman']=$_POST['linkman'];
					$sql['salary']=$_POST['salary'];
					$sql['address']=$_POST['address'];
					$sql['status']=$this->config['com_fast_status'];
					$sql['password']=$password;
					$sql['edate']=strtotime("+".(int)$_POST['edate']." days");
					$sql['pic']=$_POST['pic'];
					$sql['ctime']=time();
					$sql['login_ip']=$ip;
					$sql['did']=$this->config['did'];
					$nid=$TinyM->AddOncejob($sql);
					
					if($nid){
						$oldorder = $this->obj->DB_select_once("company_order","`order_state` = '1' and `type`='25' and `fast` = '".$_COOKIE['fast']."'");
						if(is_array($oldorder)){
							$this->obj->DB_delete_all("once_job","`id` = '".$oldorder['once_id']."' and `status` = '0' and `pay` = '1' " );
							$this->obj->DB_delete_all("company_order","`order_state` = '1' and `type`='25' and `fast` = '".$_COOKIE['fast']."'" );
						}
					}
					
					if($this->config['once_pay_price']!="0" && $this->config['once_pay_price']!=""){
						$msg="订单生成，请付款!";
						$nid?$data['msg']=$msg:$data['msg']=$msg;
						$data['url']=Url('wap',array('c'=>'once','a'=>'pay','id'=>$nid));
						
					}else{
						if($this->config['com_fast_status']=="0"){
							$msg="发布成功，等待审核！";
						}else{
							$msg="发布成功!";
						}
						$nid?$data['msg']=$msg:$data['msg']=$msg;
						$data['url']=Url('wap',array('c'=>'once'));
					}
				}
				
			}else{
				$arr=$TinyM->GetOncejobOne(array('id'=>$id,'password'=>$password),array('field'=>'pic,id'));
				if($arr['id']){
				    $sql['mans']=$_POST['mans'];
				    $sql['title']=$_POST['title'];
				    $sql['require']=$_POST['require'];
				    $sql['companyname']=$_POST['companyname'];
				    $sql['phone']=$_POST['phone'];
				    $sql['linkman']=$_POST['linkman'];
				    $sql['salary']=$_POST['salary'];
				    $sql['address']=$_POST['address'];
				    $sql['status']=$this->config['com_fast_status'];
				    $sql['password']=$password;
				    $sql['edate']=strtotime("+".(int)$_POST['edate']." days");
				    $sql['login_ip']=$ip;
				    if ($_POST['pic']!=''){
				        $sql['pic']=$_POST['pic'];
				    }else{
				        $sql['pic']=$arr['pic'];
				    }
					$nid=$TinyM->UpdateOncejob($sql,array("id"=>$id));
					if($this->config['com_fast_status']=="0"){
					    $msg="操作成功，等待审核！";
					}else{
					    $msg="操作成功!";
					}
					$nid?$data['msg']=$msg:$data['msg']=$msg;
					$data['url']=Url('wap',array('c'=>'once'));
				}else{ 
					$data['msg']='密码错误！';
					$data['url']=Url('wap',array('c'=>'once','a'=>'show','id'=>$id));
				}
			}
			echo json_encode($data);die;
		}
		
		$CacheList=$this->MODEL('cache')->GetCache('user');
        $this->yunset($CacheList);
		$this->yunset("layer",$data);
		$this->yunset("headertitle","店铺招聘");
		$this->yunset("title","添加店铺招聘");
		$this->yuntpl(array('wap/once_add'));
	}
	function show_action(){
		if($this->config['sy_once_web']=="2"){
			$data['msg']='很抱歉！该模块已关闭！';
			$data['url']='index.php';
			$this->yunset("layer",$data);
		}
		$this->rightinfo();
		$this->get_moblie();
		$this->yunset("headertitle","店铺招聘");
        $TinyM=$this->MODEL('once');
		$TinyM->UpdateOncejob(array("`hits`=`hits`+1"),array('id'=>(int)$_GET[id]));
		$row=$TinyM->GetOncejobOne(array('id'=>(int)$_GET[id]));
		if($row['status']<'1'  && !$_GET['pay']){
			$data['msg']='店铺正在审核！';
			$data['url']='index.php';
			$this->yunset("layer",$data);
		}elseif($row['pay']=='1' && !$_GET['pay']){
			$data['msg']='店铺招聘付费中！';
			$data['url']='index.php';
			$this->yunset("layer",$data);
		}
		if($row['pic']&&file_exists(APP_PATH.$row['pic'])){
			$row['pic']=$this->config['sy_weburl']."/".$row['pic'];
		}else{
			$row['pic']='';
		}
		$row['ctime']=date("Y-m-d",$row[ctime]);
		$row['edate']=date("Y-m-d",$row[edate]);
		$row['require'] = str_replace("\r\n","<br>",$row['require']);
		$row['require'] = str_replace("\n","<br>",$row['require']);
		$this->yunset("row",$row);
		$data['once_job']=$row['title'];
		$data['once_name']=$row['companyname'];
		$description=$row['require'];
		$data['once_desc']=$this->GET_content_desc($description);
		$this->data=$data;
		$this->seo('once_show');
		$CacheM=$this->MODEL('cache');
        $CacheArr=$CacheM->GetCache(array('city'));
        $this->yunset($CacheArr);
		$this->yuntpl(array('wap/once_show'));
	}
	
	function pay_action(){
		if($this->config['sy_once_web']=="2"){
			$data['msg']='很抱歉！该模块已关闭！';
			$data['url']='index.php';
			$this->yunset("layer",$data);
		}
		$TinyM=$this->MODEL('once');
		
		if($_GET['id']){
			$once=$TinyM->GetOncejobOne(array('id'=>(int)$_GET[id]));
			if(!$once){
				$data['msg']='店铺信息不存在！';
				$data['url']=Url('wap',array('c'=>'once'));
				$this->yunset("layer",$data);
			}
		}
		$this->rightinfo();
		$this->get_moblie();
		
		if($this->config['alipay']=='1' &&  $this->config['alipaytype']=='1'){
			$paytype['alipay']='1';
		}
		if($paytype){
			$this->yunset("paytype",$paytype);
		}
		$TinyM=$this->MODEL('once');
		$row=$TinyM->GetOncejobOne(array('id'=>(int)$_GET[id]));
		$row['require'] = str_replace("\r\n","<br>",$row['require']);
		$row['require'] = str_replace("\n","<br>",$row['require']);
		$data['once_job']=$row['title'];
		$data['once_name']=$row['companyname'];
		$description=$row['require'];
		$data['once_desc']=$this->GET_content_desc($description);
		$this->data=$data;
         $this->seo('once_show');
		 $this->yunset("headertitle","店铺招聘");
		$this->yuntpl(array('wap/once_pay'));
	}
	
	function getOrder_action(){
		if($_POST){
			$order = $this->obj->DB_select_once("company_order","`once_id`='".$_POST['id']."'");
			
			if($order && $order['order_state']=='1'){
				$this->obj->DB_delete_all("company_order","`once_id`='".$_POST['id']."'","");
			}
			
 			$dingdan=mktime().rand(10000,99999);
 			$data['order_id']=$dingdan;
			$data['order_price']=$this->config['once_pay_price'];
			$data['order_time']=time();
			$data['order_state']="1";
			$data['order_type']=$_POST['paytype'];
			$data['order_remark']="店铺招聘";
 			$data['did']=$this->userdid;
 			$data['once_id']=$_POST['id'];
 			$data['type']='25';
 			$fast = rand(10000,99999);
 			$data['fast']=$fast;
			$nid=$this->obj->insert_into("company_order",$data);
			
			if($nid){
				$this->cookie->SetCookie("fast",$fast,time() + 86400);
				
				$dingdan = $dingdan;
				$price = $this->config['once_pay_price'];
				$id = $nid;
				
				if($_POST['paytype']=='alipay'){
					$url=$this->config['sy_weburl'].'/api/wapalipay/alipayto_fast.php?dingdan='.$dingdan.'&dingdanname='.$dingdan.'&alimoney='.$price;
					header('Location: '.$url);exit();
				}
				
			}else{
				$data['msg']='提交失败！！';
				$data['url']=Url('wap',array('c'=>'once'));
				$this->yunset("layer",$data);
			}
			
		}else if($_GET['id']){
			$order = $this->obj->DB_select_once("company_order","`id`='".$_GET['id']."'");
		}
	}

	function paylog_action(){
		$rows = $this->obj->DB_select_all("company_order","`order_state`='1' and `type` = '25' and `fast`='".$_COOKIE['fast']."' ");
		$this->yunset("rows",$rows);
		$this->yunset("headertitle","待付款店铺");
		$this->seo('once');
		$this->yuntpl(array('wap/once_paylog'));
	}
	
	function delpaylog_action(){
		
 		$oid=$this->obj->DB_select_once("company_order","`id`='".(int)$_GET['id']."' and `order_state`='1'");
		
		if(empty($oid)){
			$this->layer_msg('操作失败！',8,0,$_SERVER['HTTP_REFERER']);
 		}else{
			$this->obj->DB_delete_all("company_order","`id`='".$oid['id']."'");
			$this->obj->DB_delete_all("once_job","`id`='".$oid['once_id']."'");
			$this->layer_msg('操作成功！',9,0,$_SERVER['HTTP_REFERER']);
 		}
	}
}
?>