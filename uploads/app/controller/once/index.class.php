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
        
		if($this->config['sy_once_web']=="2"){
			header("location:".Url('error'));
		}
		if($_GET['keyword']=='请输入店铺招聘的关键字'){
			$_GET['keyword']='';
		}
		
        
		$M=$this->MODEL('once');
		$ip=fun_ip_get();
		$start_time=strtotime(date('Y-m-d 00:00:00')); 
		$mess=$M->GetOncejobNum(array('login_ip'=>$ip,'`ctime`>\''.$start_time.'\''));
		if($this->config['sy_once'] > 0){
			$num=$this->config['sy_once']-$mess;
		}else{
			$num=1;
		} 
		
		$this->yunset("num",$num);
		
		
		if($this->config['once_pay_price']!="0" && $this->config['once_pay_price']!="" && $_COOKIE['fast']){
			
			$orderNum = $this->obj->DB_select_num("company_order","`order_state`='1' and `type` = '25' and `fast` = '".$_COOKIE['fast']."'");
			$this->yunset("ordernum",$orderNum);
			
			$paylog = $this->obj->DB_select_all("company_order","`order_state`='1' and `type` = '25' and `fast` = '".$_COOKIE['fast']."' ");
			$this->yunset("paylog",$paylog);
			$this->yunset("fast",$_COOKIE['fast']);
		}
		
		
		if($_POST['submit']){
		    if (mb_strlen($_POST['title'],'utf8')>30){
		        $this->ACT_layer_msg("我想招聘标题最多30个字！",8);
		    }
            session_start();
                        
			$authcode=md5(strtolower($_POST['authcode']));
			$password=md5($_POST['password']);
			$id=(int)$_POST['id'];
			$submit=$_POST['submit'];
			unset($_POST['authcode']);
			unset($_POST['password']);
			unset($_POST['submit']);
			unset($_POST['id']);
			$_POST['status']=$this->config['com_fast_status'];
			$_POST['login_ip']=$ip;
			$_POST['ctime']=time();
			if(is_uploaded_file($_FILES['pic']['tmp_name'])){
				$UploadM=$this->MODEL('upload');
				$upload=$UploadM->Upload_pic("../data/upload/once/",false);
				$pictures=$upload->picture($_FILES['pic']);
				$pic=str_replace("../data/upload/once/","data/upload/once/",$pictures);
				$_POST['pic']=$pic;
				if($_POST['id']){
					$row=$this->obj->DB_select_once("once_job","`id`='".$_POST['id']."'");
					unlink_pic("../".$row['pic']);
				}
			}
			if(strpos($this->config['code_web'],'店铺招聘')!==false){
			    session_start();
			    if ($this->config['code_kind']==3){
					 
					if(!gtauthcode($this->config)){
						$this->ACT_layer_msg("请点击按钮进行验证！",8);
					}

			    }else{
			        if($authcode!=$_SESSION['authcode'] || empty($_SESSION['authcode'])){
						unset($_SESSION['authcode']);
			            $this->ACT_layer_msg("验证码错误！",8); 
			        }
					unset($_SESSION['authcode']);
			    }
			}
			$_POST['did']=$_COOKIE['did'];
			$_POST['edate']=strtotime("+".(int)$_POST['edate']." days");
			
			if($id){
				$arr=$M->GetOncejobOne(array('id'=>$id,'password'=>$password));
				if(empty($arr)){
					$this->ACT_layer_msg("密码不正确",8);
				}
				$_POST['status']=0;
				$data['title']=$_POST['title'];
				$data['require']=$_POST['require'];
				$data['companyname']=$_POST['companyname'];
				$data['phone']=$_POST['phone'];
				$data['linkman']=$_POST['linkman'];
				$data['provinceid']=$_POST['provinceid'];
				$data['cityid']=$_POST['cityid'];
				$data['three_cityid']=$_POST['three_cityid'];
				$data['address']=$_POST['address'];
				$data['salary']=$_POST['salary'];
				$data['status']=$this->config['com_fast_status'];
				$data['password']=$password;
				$data['edate']=$_POST['edate'];
				$data['ctime']=time();
				if ($_POST['pic']!=''){
					$data['pic']=$_POST['pic'];
				}else{
					$data['pic']=$arr['pic'];
				}
                $M->UpdateOncejob($data,array('id'=>$id));
				if($this->config['com_fast_status']=="0"){$msg="修改成功，等待审核！";}else{$msg="修改成功!";}
			}else{
				
				if($this->config['once_pay_price'] != "0" && $this->config['once_pay_price'] != ""){
 					$_POST['pay']='1';
				}
				
				$_POST['password']=$password;
				
				if($num){
					$nid = $M->AddOncejob($_POST);
					
					if($nid){
						$oldorder = $this->obj->DB_select_once("company_order","`order_state` = '1' and `type`='25' and `fast` = '".$_COOKIE['fast']."'");
						if(is_array($oldorder)){
							$this->obj->DB_delete_all("once_job","`id` = '".$oldorder['once_id']."' and `status` = '0' and `pay` = '1' " );
							$this->obj->DB_delete_all("company_order","`order_state` = '1' and `type`='25' and `fast` = '".$_COOKIE['fast']."'" );
						}
						
						if($this->config['once_pay_price'] != "0" && $this->config['once_pay_price'] != ""){
							$msg="订单生成，请付款!";
							$this->ACT_layer_msg($msg,10,$nid);
							
						}else{
							if($this->config['com_fast_status']=="0"){
								$msg="发布成功，等待审核！";
							}else{
								$msg="发布成功!";
							}
						}
					}else{
						$msg="发布失败!";
					}
					
					
				}else{
					$this->ACT_layer_msg("一天内只能发布".$this->config['sy_once']."次！",8,$_SERVER['HTTP_REFERER']);
				} 
			}
			$this->ACT_layer_msg($msg,9,'index.php');
		}
		if((int)$_GET['id']){
			$onceinfo=$M->GetOncejobOne(array('id'=>(int)$_GET['id']));
			if(!empty($onceinfo)){
                
                $onceinfo=array_merge($onceinfo,array('title'=>$onceinfo["title"],'companyname'=>$onceinfo["companyname"],'salary'=>$onceinfo["salary"],'linkman'=>$onceinfo["linkman"],'address'=>$onceinfo["address"],'require'=>$onceinfo["require"],'edate'=>ceil(($onceinfo['edate']-mktime())/86400)));
				echo json_encode($onceinfo);die;
			}
			$this->yunset('once_id',$_GET['id']);
		}
		
		
		include PLUS_PATH."keyword.cache.php";
		if(is_array($keyword)){
		  foreach($keyword as $k=>$v){
			if($v['type']=='1'&&$v['tuijian']=='1'){
			  $oncekeyword[]=$v;
			}
		  }
		}
		
		$this->yunset("oncekeyword",$oncekeyword);
		$CacheM=$this->MODEL('cache');
		$CacheList=$CacheM->GetCache(array('city'));
        $this->yunset($CacheList);
        $add_time=array("0"=>"不限","7"=>"一周以内","15"=>"半个月","30"=>"一个月","60"=>"两个月","180"=>"半年","365"=>"一年");
        $this->yunset("add_time",$add_time);
		
		
		$this->seo("once");
		$this->yunset("ip",$ip);
		$this->yun_tpl(array('index'));
		 
	}
	function ajax_action(){
		$CacheM=$this->MODEL('cache');
		$CacheList=$CacheM->GetCache(array('city'));
        $this->yunset($CacheList);
        session_start();
		if(md5(strtolower($_POST['code']))!=$_SESSION['authcode'] || empty($_SESSION['authcode'])){
			unset($_SESSION['authcode']);
			echo 1;die;
		}
        
		$M=$this->MODEL('once');
        
		$jobinfo=$M->GetOncejobOne(array('id'=>(int)$_POST['tid'],'password'=>md5($_POST['pw'])));
		if(!is_array($jobinfo) || empty($jobinfo)){
			echo 2;die;
		}
		if($_POST['type']==1){
            
			if($this->config['com_xin']>$jobinfo['sxnumber']){
				$M->UpdateOncejob(array('ctime'=>time(),'sxtime'=>time(),'sxnumber'=>$jobinfo['sxnumber']+1),array('id'=>(int)$jobinfo['id']));
				echo 3;die;
			}
			else{
				echo 5;die;
			}						
		}elseif($_POST['type']==3){
            
			$M->DeleteOncejob(array('id'=>(int)$jobinfo['id']));
			echo 4;die;
		}else{
			if($jobinfo['edate']>mktime()){
				$jobinfo['edate']=ceil(($jobinfo['edate']-mktime())/86400);
			}else{
				$jobinfo['edate']="已过期";
			}
			if($jobinfo['provinceid']){
				$jobinfo['provincename']=$CacheList['city_name'][$jobinfo['provinceid']];
			}
			if($jobinfo['cityid']){
				$jobinfo['cityname']=$CacheList['city_name'][$jobinfo['cityid']];
			}
			if($jobinfo['three_cityid']){
				$jobinfo['three_cityname']=$CacheList['city_name'][$jobinfo['three_cityid']];
			}
            

			$jobinfo = $jobinfo;
			echo json_encode($jobinfo);die;
		}
	}
	function show_action(){
		if(isset($_GET['id'])){
		   	$id=(int)$_GET['id'];
		 
            
		   	$M=$this->MODEL('once');
           	
		   	$M->UpdateOncejob(array("`hits`=`hits`+1"),array('id'=>$id));
           	
           	$o_info=$M->GetOncejobOne(array('id'=>$id));
           	
           	if($o_info['status']<'1' && !$_GET['pay']){
				$this->ACT_msg($this->config['sy_weburl'].'/once',"店铺正在审核中！");
			}
			
		}
		$ip=fun_ip_get();
		$this->yunset("ip",$ip);
		$o_info['require'] = str_replace("\r\n","<br>",$o_info['require']);
		$o_info['require'] = str_replace("\n","<br>",$o_info['require']);
		$this->yunset('o_info',$o_info);
		$data['once_job']=$o_info['title'];
		$data['once_name']=$o_info['companyname'];
		$description=$o_info['require'];
		$data['once_desc']=$this->GET_content_desc($description);
		$this->data=$data;
		$this->seo('once_show');
		$start_time=strtotime(date('Y-m-d 00:00:00')); 
		$mess=$M->GetOncejobNum(array('login_ip'=>$ip,'`ctime`>\''.$start_time.'\''));
		if($this->config['sy_once']){
			$num=$this->config['sy_once']-$mess;
		}else{
			$num=1;
		} 
		$this->yunset("num",$num);
		if($this->config['once_pay_price']!="0" && $this->config['once_pay_price']!="" && $_COOKIE['fast']){
			$orderNum = $this->obj->DB_select_num("company_order","`order_state`='1' and `type` = '25' and `fast` = '".$_COOKIE['fast']."'");
			$this->yunset("ordernum",$orderNum);
			
			$paylog = $this->obj->DB_select_all("company_order","`order_state`='1' and `type` = '25' and `fast` = '".$_COOKIE['fast']."' ");
			$this->yunset("paylog",$paylog);
			$this->yunset("fast",$_COOKIE['fast']);
		}
		
		$CacheM=$this->MODEL('cache');
		$CacheList=$CacheM->GetCache(array('city'));
        $this->yunset($CacheList);
		$this->yun_tpl(array('show'));
	} 
	
	function pay_action(){
	 
 			if($_POST['onceid']){
 				
				$order = $this->obj->DB_select_once("company_order","`once_id`='".$_POST['onceid']."' and `order_state` = '1'");
			
				if($order){
					$this->obj->DB_delete_all("company_order","`once_id`='".$_POST['onceid']."'","");
				} 			
					
 				$row = $this->obj->DB_select_once("once_job","`pay`='1' and `id`='".$_POST['onceid']."'");
 				if(is_array($row)){
 					
 					
					$dingdan=time().rand(10000,99999);
					$orderData['type']='25';
					$orderData['order_id']=$dingdan;
					$orderData['order_price']=$this->config['once_pay_price'];
					$orderData['order_time']=time();
					$orderData['order_type']=$_POST['pay_type'];
					$orderData['order_state']="1";
					$orderData['order_remark']='店铺招聘收费';
 					$orderData['did']=$this->userdid;
 					$orderData['once_id']=$_POST['onceid'];
 					$fast = rand(10000,99999);
 					$orderData['fast']=$fast;
 					
 					$nid=$this->obj->insert_into("company_order",$orderData);
 					
  					if($nid && $dingdan){
 						
 						$this->cookie->SetCookie("fast",$fast,time() + 86400);
 						
						
						echo json_encode(array('error'=>0,'orderid'=>$dingdan,'id'=>$nid));
					}else{
						
						echo json_encode(array('error'=>1,'msg'=>"下单失败"));
					}
					
 				}else{
 					echo json_encode(array('error'=>1,'msg'=>"店铺数据不存在"));
 				}
 			} 
		 
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