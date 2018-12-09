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
class com_controller extends wap_controller{
	
	function get_user(){
		$rows=$this->obj->DB_select_once("company","`uid`='".$this->uid."'");
		if(!$rows['name'] || !$rows['address'] || !$rows['pr']){
			$data['msg']='请先完善企业资料！';
			$data['url']='index.php?c=info';
			$this->yunset("layer",$data);
		}
		if(!$rows['logo'] || !file_exists(str_replace('./',APP_PATH,$rows['logo']))){
			$rows['logo']=$this->config['sy_weburl']."/".$this->config['sy_unit_icon'];
		}else{
			$rows['logo']=str_replace("./",$this->config['sy_weburl']."/",$rows['logo']);
		}
		$this->yunset("company",$rows);
		return $rows;
	}
	
	function waptpl($tpname){
		$this->yuntpl(array('wap/member/com/'.$tpname));
	}

	function index_action(){
		$this->rightinfo();
		$member=$this->obj->DB_select_once("member","`uid`='".$this->uid."'","`login_date`,`status`");
		$this->yunset("member",$member);
		
		$date=date("Ymd"); 
		$reg=$this->obj->DB_select_once("member_reg","`uid`='".$this->uid."' and `usertype`='".$this->usertype."' and `date`='".$date."'"); 
		if($reg['id']){
			$signstate=1;
		}else{
			$signstate=0;
		}
		$this->yunset("signstate",$signstate);
		
		$jobs=$this->obj->DB_select_all("company_job","`state`=1 and `r_status`<>2 and `status`<>1 and `uid`='".$this->uid."'");
		if($jobs && is_array($jobs)){
			foreach($jobs as $key=>$v){
				$ids[]=$v['id'];
			}
			$jobids ="".@implode(",",$ids)."";
			$this->yunset("jobids",$jobids);
		}		
		
		$statis = $this->company_satic();
		$this->yunset("statis",$statis);
		$this->cookie->SetCookie("updatetoast",'1',time() + 86400);
		$this->get_user();
		$this->yunset('backurl',Url('wap',array()));
		$this->waptpl('index');
	}
	function com_action(){
		
		$this->rightinfo();
		$this->company_satic();
		$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'");
		if($statis['rating']){
			$rating=$this->obj->DB_select_once("company_rating","`id`='".$statis['rating']."'");
		}
		$com=$this->obj->DB_select_once("company","`uid`='".$this->uid."'");
		if($statis['rating']>0){
			if($statis['vip_etime']>time()){
				$days=round(($statis['vip_etime']-mktime())/3600/24) ;
				$this->yunset("days",$days);
			}
		}
		$allprice=$this->obj->DB_select_once("company_pay","`com_id`='".$this->uid."' and `type`='1' and `order_price`<0","sum(order_price) as allprice");
		if($allprice['allprice']==''){$allprice['allprice']='0';}
		$this->yunset("integral",number_format(str_replace("-","", $allprice['allprice'])));
		$this->yunset("com",$com);
		$this->yunset("statis",$statis);
		$this->yunset("rating",$rating);
		$backurl=Url('wap',array('c'=>'finance'),'member');
		$this->yunset('backurl',$backurl);
		
		$this->yunset('header_title',"我的服务");
		$this->get_user();
		$this->waptpl('com');
	}
        
    function map_action(){
        if($_POST['submit']){
            if($_POST['xvalue']==""){
                $data['msg']='请设置企业地图！';
            }else{
                $IntegralM=$this->MODEL('integral');
                $rows = $this->obj->DB_select_once("company","`uid`='".$this->uid."'","`x`,`y`");
                if($rows['x'] == "" && $rows['y'] == ""){
                    $IntegralM->get_integral_action($this->uid,"integral_map","设置企业地图");
                }
                $data['x']=(float)$_POST['xvalue'];
                $data['y']=(float)$_POST['yvalue'];
                $nid=$this->obj->update_once("company",$data,array("uid"=>$this->uid));
                if($nid){
					$this->obj->DB_update_all("company_job","`x`='".$data['x']."',`y`='".$data['y']."'","`uid`='".$this->uid."'");
                    $this->member_log("设置企业地图");
                    $data['msg']='地图设置成功！';
                    $data['url']="index.php?c=set";
                }else{
                    $this->member_log("设置企业地图");
                    $data['msg']='地图设置失败！';
                    $data['url']=$_SERVER['HTTP_REFERER'];
                }
            }
           
            
        }
        $this->yunset("layer",$data);
        $urlarr=array("c"=>"map","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
        $row=$this->obj->DB_select_once("company","`uid`='".$this->uid."'","x,y,address,provinceid,cityid,three_cityid");
		$this->yunset("row",$row);
		$this->yunset($this->MODEL('cache')->GetCache(array('city')));
		
		$backurl=Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		
		$this->yunset('header_title',"设置地图");
		
		$this->get_user();
        $this->waptpl('map');
    }
     function reportlist_action(){
        
        if($_POST['submit']){
            if($_POST['reason']==""){
                $data['msg']='请选择举报原因！';
            }else{
                $data['c_uid']=(int)$_GET['uid'];
                $data['inputtime']=mktime();
                $data['p_uid']=$this->uid;
                $data['did']=$this->userid;
                $data['usertype']=(int)$this->usertype;
                $data['eid']=(int)$_GET['eid'];
                $data['r_name']=$_GET['r_name'];
                $data['username']=$this->username;
                $data['r_reason']=@implode(',',$_POST['reason']);
                $nid=$this->obj->insert_into("report",$data);
                if($nid){
                    $this->member_log("举报简历");
                    $data['msg']='举报成功！';
                    $data['url']='index.php?c=down';
                }else{
                    $this->member_log("举报简历");
                    $data['msg']='举报失败！';
                    $data['url']='index.php?c=down';
                }
            }
            
            
        }
        $this->yunset("layer",$data);
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"举报简历");
		$this->get_user();
		$this->waptpl('reportlist');
	}
	function info_action(){
		$this->rightinfo();
		if($_POST['submit']){
			$_POST=$this->post_trim($_POST);
			$comname=$this->obj->DB_select_num('company',"`uid`<>'".$this->uid."' and `name`='".$_POST['name']."'","`uid`");
			 
			if($data['msg']==''){
 				if($_POST['name']==""){
					$data['msg']='企业全称不能为空！';
				}elseif($comname>1){
					$data['msg']='企业全称已存在！';
				}elseif($_POST['hy']==""){
					$data['msg']='从事行业不能为空！';
				}elseif($_POST['pr']==""){
					$data['msg']='企业性质不能为空！';
				}elseif($_POST['provinceid']==""){
					$data['msg']='所在地不能为空！';
				}elseif($_POST['mun']==""){
					$data['msg']='企业规模不能为空！';
				}else if($_POST['address']==""){
					$data['msg']='公司地址不能为空！';
				}else if($_POST['linkphone']==""&&$_POST['linktel']==""){
					$data['msg']='手机或电话必填一项！';
				}elseif($_POST['content']==""){
					$data['msg']='企业简介不能为空！';
				}
			}
			if($data['msg']==''){
				delfiledir("../data/upload/tel/".$this->uid);
				$company=$this->obj->DB_select_once("company","`uid`='".$this->uid."'");
				$Member=$this->MODEL("userinfo");
				if($company['moblie_status']==1){
					unset($_POST['linktel']);
				}elseif($_POST['linktel']){
					$moblieNum = $Member->GetMemberNum(array("moblie"=>$_POST['linktel'],"`uid`<>'".$this->uid."'"));
					if(!CheckMoblie($_POST['linktel'])){
						$data['msg']='手机号码格式错误！';
					}elseif($moblieNum>0){
						$data['msg']='手机号码已存在！';
					}else{
						$mvalue['moblie']=$_POST['linktel'];
					}

				}
				if($company['email_status']==1){
					unset($_POST['linkmail']);
				}elseif($_POST['linkmail']){
					$emailNum = $Member->GetMemberNum(array("email"=>$_POST['linkmail'],"`uid`<>'".$this->uid."'"));
					if(CheckRegEmail($_POST['linkmail'])==false){
						$data['msg']='联系邮箱格式错误！';
					}elseif($emailNum>0){
						$data['msg']='联系邮箱已存在！';
					}else{
						$mvalue['email']=$_POST['linkmail'];
					}
				}
				if($company['yyzz_status']=='1'){
					$_POST['name'] = $company['name'];
				}
				
				
				
				
				if(is_uploaded_file($_FILES['comqcode']['tmp_name'])){
					
 				    $UploadM=$this->MODEL('upload');
				    $upload=$UploadM->Upload_pic(APP_PATH."/data/upload/company/",false);
				    
				    $pictures=$upload->picture($_FILES['comqcode']);
				    
				    $pic=str_replace(APP_PATH."/data/upload/company/","./data/upload/company/",$pictures);
				    $_POST['comqcode']=$pic;
				}
				
				if($_POST['preview']){
					
					$UploadM =$this->MODEL('upload');
					$upload  =$UploadM->Upload_pic(APP_PATH."/data/upload/company/",false);
					
					$pic     =$upload->imageBase($_POST['preview']);
					
					$picmsg  = $UploadM->picmsg($pic,$_SERVER['HTTP_REFERER']);
					if($picmsg['status']==$pic){
						$data['msg']=$picmsg['msg'];
	 				}else{
						$_POST['comqcode']=str_replace(APP_PATH."/data/upload/company/","./data/upload/company/",$pic);
						if($company['comqcode']){
							unlink_pic(APP_PATH.$company['comqcode']);
						}
					}
				} 
				
				
				
				unset($_POST['submit']);
				$where['uid']=$this->uid;
				$_POST['lastupdate']=time();
				$_POST['welfare']=@explode(',',$_POST['welfare']);
				foreach($_POST['welfare'] as $v){
					if($v){
						$welfare[]=$v;
					}
				}
				$_POST['welfare']=@implode(',',$welfare);
				if($data['msg']==""){
					$nid=$this->obj->update_once("company",$_POST,$where);
					if($nid){
						if(!empty($mvalue)){
							$this->obj->update_once('member',$mvalue,array("uid"=>$this->uid));
						}
						$data['com_name']=$_POST['name'];
						$data['pr']=$_POST['pr'];
						$data['mun']=$_POST['mun'];
						$data['com_provinceid']=$_POST['provinceid'];
						$data['welfare']=@implode(',',$_POST['welfare']);
						$data['com_logo']=$_POST['photo'];
						$this->obj->update_once("company_job",$data,array("uid"=>$this->uid));
						if($company['name']!=$_POST['name']){
							$this->obj->update_once("partjob",array("com_name"=>$_POST['name']),array("uid"=>$this->uid));
							$this->obj->update_once("userid_job",array("com_name"=>$_POST['name']),array("com_id"=>$this->uid));
							$this->obj->update_once("fav_job",array("com_name"=>$_POST['name']),array("com_id"=>$this->uid));
							$this->obj->update_once("report",array("r_name"=>$_POST['name']),array("c_uid"=>$this->uid));
							$this->obj->update_once("blacklist",array("com_name"=>$_POST['name']),array("c_uid"=>$this->uid));
							$this->obj->update_once("msg",array("com_name"=>$_POST['name']),array("job_uid"=>$this->uid));
						}
						

						if($company['lastupdate']<1){
							if($this->config['integral_userinfo_type']=="1"){
								$auto=true;
							}else{
								$auto=false;
							}
							
							$this->MODEL('integral')->company_invtal($this->uid,$this->config['integral_userinfo'],$auto,"首次填写基本资料",true,2,'integral',25);
						}
						$this->member_log("修改企业资料");
						$data['msg']='更新成功！';
						$data['url']='index.php';
					}else{
 						$data['msg']='更新失败！';
						$data['url']='index.php?c=info';
					}
				}else{
					$data['msg']=$data['msg'];
				}				
				
			}
			echo json_encode($data);die;
		}
		$row=$this->obj->DB_select_once("company","`uid`='".$this->uid."'");
		if ($row['comqcode']){
			$row['comqcode']=str_replace('./', $this->config['sy_weburl'].'/', $row['comqcode']);
		}
		if ($row['logo']){
			$row['logo']=str_replace('./', $this->config['sy_weburl'].'/', $row['logo']);
		}
		 
		if ($row['welfare']){
			$row['arraywelfare']=explode(',', $row['welfare']);
		}
		
		if ($row['content']){
			$row['content_t']=strip_tags($row['content']);
		}
 
		$this->yunset($this->MODEL('cache')->GetCache(array('city','com','hy')));
		$this->yunset("row",$row);
		
		$this->yunset('header_title',"基本信息");
		$this->waptpl('info');
	}
	
	function get_com($type){
		$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'");
		
		if($statis['rating_type'] && $statis['rating']) {

			if($type==1){
				if($statis['rating_type']=='1' && $statis['job_num']>0 && ($statis['vip_etime']<1 || $statis['vip_etime']>=time())){
					$value="`job_num`=`job_num`-1";
				}elseif($statis['rating_type']=='2' && ($statis['vip_etime']>time() || $statis['vip_etime']=='0')){
					$value="";
				}else{
					return "你的套餐不够发布职位！";
				}
			}elseif($type==4){
				if($statis['rating_type']=='2' && ($statis['vip_etime']>time() || $statis['vip_etime']=='0')){
					$value="";
				}else{
					return "你的套餐不够发布职位！";
				}
			}elseif($type==7){
				if($statis['rating_type']=='1' && $statis['part_num']>0 && ($statis['vip_etime']<1 || $statis['vip_etime']>=time())){
					$value="`part_num`=`part_num`-1";
				}else if($statis['rating_type']=='2' && ($statis['vip_etime']>time() || $statis['vip_etime']=='0')){
					$value="";
				}else{
					return "你的套餐不够发布兼职！";
				}
			}
			if($value){
				$this->obj->DB_update_all("company_statis",$value,"`uid`='".$this->uid."'");
			}
		}else{
			return "你的会员已经到期！";
		}
	}
	 
	function jobadd_action(){
		include(CONFIG_PATH."db.data.php");
		$this->yunset("arr_data",$arr_data);
		$this->get_user();
		$statics=$this->company_satic();
		
		if(!$_GET['id']){
			if($this->config['integral_job']!='0'){
				if( $statics['addjobnum'] == 2){
					$data['msg']="您的套餐已用完！";
					$data['url']='index.php?c=rating';
				}
			}else{
				if($statics['addjobnum']==2){
					$this->obj->DB_update_all("company_statis","`job_num` = '1'","`uid`='".$this->uid."'");
				}elseif($statics['addjobnum']==0){ 
					$data['msg']="您的会员已到期！";
					$data['url']='index.php?c=rating';
				}
			}
		}
		
		$row=$this->obj->DB_select_once("company","`uid`='".$this->uid."'");
		if($row['lastupdate']<1){
			$data['msg']="请先完善基本资料！";
			$data['url']='index.php?c=info';
		}
		$this->rightinfo();
		$msg=array();
		
		$isallow_addjob="1";
		
		if($this->config['com_enforce_emailcert']=="1"){
		    if($row['email_status']!="1"){
				$isallow_addjob="0";
				$msg[]="邮箱认证";
				$data['url']='index.php?c=set';
			}
		}
		if($this->config['com_enforce_mobilecert']=="1"){
		    if($row['moblie_status']!="1"){
		    	$isallow_addjob="0";
				$msg[]="手机认证";
				$data['url']='index.php?c=set';
			}
		}
		if($this->config['com_enforce_licensecert']=="1"){
		    if($row['yyzz_status']!="1"){
		    	$isallow_addjob="0";
				$msg[]="营业执照认证";
				$data['url']='index.php?c=set';
			}
		}
		if($this->config['com_enforce_setposition']=="1"){
		    if(empty($row['x'])||empty($row['y'])){
				$isallow_addjob="0";
				$msg[]="企业地图设置";
				$data['url']="index.php?c=map";
			}
		}
		
		if($isallow_addjob=="0"){
			
			$data['msg']="请先完成".implode(",",$msg)."！";
			
		}else if($_GET['id']){
			$job=$this->obj->DB_select_once("company_job","`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'");
			$arr_data1=$arr_data['sex'][$job['sex']];
			$this->yunset("arr_data1",$arr_data1);
			if($job['id']){
				$job['langid']=$job['lang'];
				if($job['lang']!=""){
					$job['lang']= @explode(",",$job['lang']);
				}
				$job['days']= ceil(($job['edate']-$job['sdate'])/86400);
				$job_link=$this->obj->DB_select_once("company_job_link","`jobid`='".(int)$_GET['id']."' and `uid`='".$this->uid."'");
				$this->yunset("job_link",$job_link);
				$job['islink']=$job_link['link_type'];
 				$job['isemail']=$job_link['email_type'];
 				if($job['description']){
 					$job['description_t']=strip_tags($job['description']);
 				}
				$this->yunset("row",$job);
			}else{
				$data['msg']='非法操作！';
				$data['url']='index.php?c=job';
			}
		}
		if($_POST['submit']){
			$id=intval($_POST['id']);
			$state= intval($_POST['state']);
			unset($_POST['submit']);
			unset($_POST['id']);
			unset($_POST['state']);
			$companycert=$this->obj->DB_select_once("company_cert","`uid`='".$this->uid."'and type=3","uid,type,status");
			if($this->config['com_free_status']=="1"&&$companycert['status']=="1"){	
				$_POST['state']=1;
			}else{
				$member=$this->obj->DB_select_once("member","`uid`='".$this->uid."'","`status`,`did`");
				if($member['status']!="1"){
					$_POST['state']=0;
				}else{
					$_POST['state']=$this->config['com_job_status'];
				}
			}
			$_POST['r_status']=1;
			if(!empty($_POST['lang'])){
				$_POST['lang'] = pylode(",",$_POST['lang']);
			}else{
				$_POST['lang'] = "";
			}
			$_POST['sdate']=time();
			$mapinfo=$this->obj->DB_select_once("company","`uid`='".$this->uid."'","`x`,`y`");
			if($mapinfo){
				$_POST['x']=$mapinfo['x'];
 				$_POST['y']=$mapinfo['y'];
			}
 			$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'","`rating`");
			$_POST['com_name']=$row['name'];
			$_POST['com_logo']=$row['logo'];
			$_POST['com_provinceid']=$row['provinceid'];
			$_POST['pr']=$row['pr'];
			$_POST['mun']=$row['mun'];
			$_POST['rating']=$statis['rating'];
			$islink=(int)$_POST['islink'];
			$tblink=$_POST['tblink'];
			$link_type=$islink;
			if($islink<3){
				$linktype=$islink;
				$islink=1;
			}else{
				$islink=0;
			}
			$isemail=(int)$_POST['isemail'];
			$emailtype=$isemail;
			if($isemail<3){
				$isemail=1;
			}else{
				$isemail=0;
			}
			if($_POST['salary_type']==1){
				$_POST['minsalary']=$_POST['maxsalary']=0;
			}
			$_POST['is_link']=$islink;
			$_POST['link_type']=$linktype;
 			$_POST['is_email']=$isemail;
			$link_moblie=$_POST['link_moblie'];
			$email=$_POST['email'];
			$link_man=$_POST['link_man'];
			unset($_POST['salary_type']);
			unset($_POST['link_moblie']);
			unset($_POST['islink']);
			unset($_POST['isemail']);
			unset($_POST['link_man']);
			unset($_POST['email']);
			if($this->config['com_job_status']=="0" && $_POST['state']!=1){
			    $msg=",请等待审核";
			}else{
				$msg="";
			}
			if(!$id){
				$_POST['lastupdate']=time();
				$_POST['uid']=$this->uid;
				$_POST['did']=$member['did'];

				$data['msg']=$this->get_com(1);
				if($data['msg']==''){
					$_POST['source']=2;
					$nid=$this->obj->insert_into("company_job",$_POST);
					$name="添加职位";
					if($nid){
						$this->obj->DB_update_all("company","`jobtime`='".$_POST['lastupdate']."'","`uid`='".$this->uid."'");
						$state_content = "发布了新职位 <a href=\"".Url("job",array("c"=>"comapply","id"=>$nid))."\" target=\"_blank\">".$_POST['name']."</a>。";
						$this->addstate($state_content);
						$this->member_log("发布了新职位 ".$_POST['name']);
						$Warning=$this->MODEL("warning");
						$Warning->warning("1");
					}
					$data['msg']=$nid?$name."成功".$msg:$name."失败";
				}
			}else{
				$where['id']=$id;
				$where['uid']=$this->uid;
				
				if($data['msg']==''){
					$nid=$this->obj->update_once("company_job",$_POST,$where);
					$name="更新职位";
					if($nid){
						$this->member_log("更新职位《".$_POST['name']."》");
					}
					$data['msg']=$nid?$name."成功".$msg:$name."失败";
				}

			}
			$joblink=array();
			$joblink[]="`email`='".trim($email)."',`is_email`='".$isemail."',`email_type`='".$emailtype."'";
			if($linktype==2){
				$joblink[]="`link_man`='".$link_man."',`link_moblie`='".$link_moblie."'";
			}
			if ($link_type){
				$joblink[]="`link_type`='".$link_type."'";
			}
			if($id){
				delfiledir("../data/upload/tel/".$this->uid);
				$linkid=$this->obj->DB_select_once("company_job_link","`uid`='".$this->uid."' and `jobid`='".$id."'","id");
				if($linkid['id']){
					if ($tblink==1){
						$this->obj->DB_update_all("company_job_link",@implode(',',$joblink),"`uid`='".$this->uid."'");
						$this->obj->DB_update_all("company_job","`link_type`='2'","`uid`='".$this->uid."'");
					}else {
						$this->obj->DB_update_all("company_job_link",@implode(',',$joblink),"`id`='".$linkid['id']."'");
					}
				}else{
					$joblink[]="`uid`='".$this->uid."'";
					$sid=$this->obj->DB_insert_once("company_job_link",@implode(',',$joblink).",`jobid`='".(int)$id."'");
					if($sid && $tblink==1){
						$this->obj->DB_update_all("company_job_link",@implode(',',$joblink),"`uid`='".$this->uid."'");
						$this->obj->DB_update_all("company_job","`link_type`='2'","`uid`='".$this->uid."'");
					}
				}
			}else if($nid>0){
				$joblink[]="`uid`='".$this->uid."'";
				$sid=$this->obj->DB_insert_once("company_job_link",@implode(',',$joblink).",`jobid`='".(int)$nid."'");
				if($sid && $tblink==1){
					$this->obj->DB_update_all("company_job_link",@implode(',',$joblink),"`uid`='".$this->uid."'");
					$this->obj->DB_update_all("company_job","`link_type`='2'","`uid`='".$this->uid."'");
				}
			}
			$data['error']=0;
			
			if($this->config['com_job_status']=="0"){
				$data['url']='index.php?c=job';
			}else if($this->config['com_job_status']=="1"){
				if($id){
					$data['url_tg']='index.php?c=job_tg&id='.$id;
				} else if($nid > 0){
					$data['url_tg']='index.php?c=job_tg&id='.$nid;
				}
			}
			echo json_encode($data);die;
		}
		$this->yunset("layer",$data);
		$cacheList=$this->MODEL('cache')->GetCache(array('city','com','hy','job'));
		$this->yunset($cacheList);
		$this->yunset('header_title',"发布职位");
		$this->waptpl('jobadd');
	}
	
	function job_tg_action(){
		$this->company_satic();
		if($_GET['id']){
			$id = (int)$_GET['id'];
			$job = $this->obj->DB_select_once("company_job","`id`='".$id."' and `state`='1' and `status`='0'");
			
			if($job && is_array($job)){
				$this->yunset('job',$job);
			}else{
				$data['msg']="该职位未满足推广条件";
				$data['url']="index.php?c=job";
				$this->yunset("layer",$data);
			}
		}
		
		$backurl=Url('wap',array('c'=>'job'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"职位推广");
		$this->waptpl('job_tg');
	}
	function job_action(){
		$this->rightinfo();
		$jobM = $this->MODEL('job');
		$rows = $jobM->GetComjobList(array('uid'=>$this->uid),array('orderby'=>'`lastupdate`','desc'=>'desc'));
		$zp=$sh=$xj=0;
		if(is_array($rows)){
			$jobids = array();
		    foreach($rows as $value){
		    	$jobids[] = $value['id'];
		        if($value['state']==1 && $value['status']!=1){
		            $zp +=1;
		        }
		        if($value['state']!='1'){
		            $sh +=1;
		        }
		        if($value['status']=='1'){
		            $xj +=1;
		        }
		    }
		    $jobnum=$this->obj->DB_select_all("userid_job","`job_id` in(".pylode(',',$jobids).") and `com_id`='".$this->uid."' GROUP BY `job_id`","`job_id`,count(`id`) as `num`");
			foreach($rows as $k=>$v){
				$rows[$k]['jobnum']=0;
				foreach($jobnum as $val){
					if($v['id']==$val['job_id']){
						$rows[$k]['snum']=$val['num'];
					}
				}
 			}
		    
		}
		$this->yunset(array('zp'=>$zp,'sh'=>$sh,'xj'=>$xj));
		$this->yunset("rows",$rows);
		$this->company_satic();
		$backurl=Url('wap',array('c'=>'jobcolumn'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"职位管理");
		$this->waptpl('job');
	}
	function refreshjob_action(){
		if($_GET['up']){
			$nid=$this->obj->DB_update_all("company_job","`lastupdate`='".time()."'","`uid`='".$this->uid."' and `id`='".(int)$_GET['up']."'");
			if($nid){
				$this->obj->DB_update_all("company","`jobtime`='".time()."'","`uid`='".$this->uid."'");
				$job=$this->obj->DB_select_once("company_job","`id`='".(int)$_GET['up']."'","name");
				$job_sx=$this->obj->member_log("刷新职位《".$job['name']."》",1,4);
				$this->layer_msg('刷新职位成功！',9,0,$_SERVER['HTTP_REFERER']);
			}else{
				$this->layer_msg('刷新失败！',8,0,$_SERVER['HTTP_REFERER']);
			}
		}
	}
	function getserver_action(){
		
		if($this->config['alipay']=='1' &&  $this->config['alipaytype']=='1'){
			$paytype['alipay']='1';
		}
		if($paytype){
			$this->yunset("paytype",$paytype);
		}
		if($_GET['id']){
			$jobid=intval($_GET['id']);
			$info['id']=$jobid;
			$info['count']=1;
		}
		if($_GET['ids']){
			$info['id']=$_GET['ids'];
			$ids=@explode(",",$_GET['ids']);
			$count=count($ids);
			$info['count']=$count;
		}
		$server=intval($_GET['server']);
		$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'");
		$this->yunset("info",$info);
		$this->yunset("statis",$statis);
		
		
 		$this->get_user();
 		
 		switch($server){
			case 1:$header_title="自动刷新";
			break;
			case 2:$header_title="职位置顶";
			break;
			case 3:$header_title="职位推荐";
			break;
			case 4:$header_title="紧急招聘";
			break;
			case 5:$header_title="刷新职位";
			break;
			
			case 7:$header_title="下载简历";
			break;
			case 8:$header_title="发布职位";
			break;
			case 9:$header_title="发布兼职";
			break;
			
			case 11:$header_title="邀请面试";
			break;
			case 12:$header_title="下载简历";
			break;
			case 13:$header_title="兼职刷新";
			break;
		}
		$this->yunset('header_title',$header_title);
		
		$this->waptpl('getserver');
	}
	function saveserver_action(){
		$server=intval($_POST['server']);
		$days=intval($_POST['days']);
		$jobid=intval($_POST['jobid']);
		if($days<1){
			echo json_encode(array('type'=>2,'msg'=>'请选择或填写天数！'));die;
		}else{
			if($server=='1'){
				$price=$days * $this->config['job_auto'];
				if($this->config['job_auto_type']=="1"){
					$auto=true;
				}else{
					$auto=false;
				}
				$type=9;
				$msg="购买自动刷新";
			}else if($server=='2'){
				$price=$days*$this->config['integral_job_top'];
				$auto=false;
				$type=11;
				$msg="发布置顶职位";
			}else if($server=='3'){
				if($this->config['com_recjob_type']=="1"){
					$auto=true;
				}else{
					$auto=false;
				}
				$price=$days*$this->config['com_recjob'];
				$type=12;
				$msg="发布推荐职位";
			}else{
				$price=$days*$this->config['com_urgent'];
				if($this->config['com_urgent_type']=="1"){
					$auto=true;
				}else{
					$auto=false;
				}
				$type=10;
				$msg="发布紧急职位";
			}
			$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'","`pay`");
			$info=$this->obj->DB_select_once("company_job","`uid`='".$this->uid."' and `id`='".$jobid."'","`id`,`name`,`job1`,`rec_time`,`urgent_time`,`urgent`,`rec`,`xuanshang`,`xsdate`");
			if($price > $statis['pay'] && $auto==false){
				echo json_encode(array('type'=>2,'msg'=>'余额不足，请先充值！'));die;
			}else{
				$this->MODEL('integral')->company_invtal($this->uid,$price,$auto,$msg,true,2,'pay',$type);
				if($server=='1'){
					if($info['autotime']>=time()){
						$autotime = $info['autotime']+$days*86400;
					}else{
						$autotime = time()+$days*86400;
					}
					$this->obj->update_once('company_job',array('autotime'=>$autotime,'autotype'=>1),"`uid`='".$this->uid."' and `id`='".$info['id']."'");
					$this->obj->update_once('company_statis',array('autotime'=>$autotime),array('uid'=>$this->uid));
					$this->obj->member_log("购买职位自动刷新功能");
				}else if($server=='2'){
					if($info['xsdate']>time()){
						$data['xsdate']=$info['xsdate']+$days*86400;
					}else{
						$data['xsdate']=strtotime("+".$days." day");
					}
					$this->obj->update_once("company_job",$data,array('uid'=>$this->uid,'id'=>$info['id']));
					$this->obj->member_log("发布竞价职位《".$info['name']."》",1,1);
				}else if($server=='3'){
					if($info['rec_time']<time()){
						$time=time()+$days*86400;
					}else{
						$time=$info['rec_time']+$days*86400;
					}
					$this->obj->update_once("company_job",array('rec'=>1,'rec_time'=>$time),array('uid'=>$this->uid,'id'=>$info['id']));
					$this->obj->member_log("发布推荐职位《".$info['name']."》",1,1);
				}else{
					if($info['urgent_time']<time()){
						$time=time()+$days*86400;
					}else{
						$time=$info['urgent_time']+$days*86400;
					}
					$this->obj->update_once("company_job",array('urgent'=>1,'urgent_time'=>$time),array('uid'=>$this->uid,'id'=>$info['id']));
					$this->obj->member_log("发布紧急职位《".$info['name']."》",1,1);
				}
				echo json_encode(array('type'=>1,'msg'=>'操作成功！'));die;
			}
		}
	}
	function jobset_action(){
		if($_GET['status']){
			if($_GET['status']==2){
				$_GET['status']=0;
			}
			$this->obj->update_once('company_job',array('status'=>intval($_GET['status'])),array('uid'=>$this->uid,'id'=>intval($_GET['id'])));
			$this->member_log("修改职位招聘状态");
			$this->get_user();
			$this->waplayer_msg("设置成功！");
		}
	}

	function jobdel_action(){
		if($_GET['id']){
				$nid=$this->obj->DB_delete_all("company_job","`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'");
				if($nid){
					$newest=$this->obj->DB_select_once("company_job","`uid`='".$this->uid."' order by lastupdate DESC","`lastupdate`");
					$this->obj->DB_delete_all("userid_job","`com_id`='".$this->uid."' and `job_id`='".(int)$_GET['id']."'");
					$this->obj->DB_delete_all("look_job","`com_id`='".$this->uid."' and `jobid`='".(int)$_GET['id']."'");
					$this->obj->DB_delete_all("fav_job","`job_id`='".(int)$_GET['id']."'"," ");
					
					$this->obj->DB_delete_all("report","`usertype`=1 and `type`=0 and `eid`='".(int)$_GET['id']."'","");
					$this->obj->update_once("company",array("jobtime"=>$newest['lastupdate']),array("uid"=>$this->uid));
					$this->obj->DB_delete_all("company_job_link","`uid`='".$this->uid."' and `jobid`='".(int)$_GET['id']."'");
					$this->member_log("删除职位记录（ID:".(int)$_GET['id']."）");
					$this->waplayer_msg("删除成功！");
				}else{
					$this->waplayer_msg("删除失败！");
				}
		}
	}
	
	function partapply_action(){
		$this->rightinfo();
		
		if($_GET['del']){
			$nid=$this->obj->DB_delete_all("part_apply","`id`='".(int)$_GET['del']."' and `comid`='".$this->uid."'");
			if($nid){
				$data['msg']="删除成功!";
				$this->member_log("删除兼职报名");
			}else{
				$data['msg']="删除失败！";
			}
			$data['url']='index.php?c=partapply';
			$this->yunset("layer",$data);
		}
		
		if((int)$_GET['id']&&(int)$_GET['status']){
			$nid=$this->obj->update_once("part_apply",array('status'=>(int)$_GET['status']),array("comid"=>$this->uid,"id"=>(int)$_GET['id']));
			if($nid){
				$this->member_log("更改兼职报名状态（ID:".(int)$_GET['id']."）");
				$this->waplayer_msg("操作成功！");
			}else{
				$this->waplayer_msg("操作失败！");
			}
		}
		$urlarr=array("c"=>"partapply","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("part_apply","`comid`='".$this->uid."'",$pageurl,"10");
		if(is_array($rows)&&$rows){
			include PLUS_PATH."/user.cache.php";
			include(CONFIG_PATH."db.data.php");
			unset($arr_data['sex'][3]);
			$this->yunset("arr_data",$arr_data);
			foreach($rows as $val){
				$jobid[]=$val['jobid'];
				$uid[]=$val['uid'];
			}
			$joblist=$this->obj->DB_select_all("partjob","`id` in(".pylode(',',$jobid).")","`id`,`name`");
			$uselist=$this->obj->DB_select_all("resume","`uid` in (".pylode(",",$uid).") and `r_status`<>'2'","`name`,`sex`,`edu`,`uid`,`birthday`,`telphone`,`def_job`,`birthday`");
		}
		foreach($rows as $key=>$val){
			foreach($joblist as $k=>$v){
				if($val['jobid']==$v['id']){
					$rows[$key]['job_name']=$v['name'];
				}
			}
			foreach($uselist as $k=>$va){
				if($val['uid']==$va['uid']){
					$rows[$key]['username']=$va['name'];
					$rows[$key]['moblie']=$va['telphone'];
					$rows[$key]['sex']=$arr_data['sex'][$va['sex']];
					$rows[$key]['edu']=$userclass_name[$va['edu']];
					$rows[$key]['age']=ceil((time()-strtotime($va['birthday']))/31104000);
					$rows[$key]['resumeid']=$va['def_job'];
					$rows[$key]['birthday']=$va['birthday'];
				}
			}
		}
		$this->yunset("rows",$rows);
		$backurl=Url('wap',array('c'=>'part'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"兼职报名");
		$this->get_user();
		$this->waptpl('partapply');
	}
	
	function hr_action(){
		$this->rightinfo();
		$urlarr=array("c"=>"hr","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("userid_job","`com_id`='".$this->uid."' ORDER BY is_browse asc,datetime desc",$pageurl,"10");
		if(is_array($rows) && !empty($rows)){
			$uid=$eid=array();
			foreach($rows as $v){
				$uid[]=$v['uid'];
				$eid[]=$v['eid'];
			}
			$userrows=$this->obj->DB_select_all("resume","`uid` in (".pylode(",",$uid).") and `r_status`<>'2'","`name`,`sex`,`edu`,`uid`,`exp`");
			$userid_msg=$this->obj->DB_select_all("userid_msg","`fid`='".$this->uid."' and `uid` in (".pylode(",",$uid).")","uid,jobid");
			$expect=$this->obj->DB_select_all("resume_expect","`id` in (".pylode(",",$eid).")","`id`,`job_classid`,`salary`");
			
			if(is_array($userrows)){
				include(PLUS_PATH."user.cache.php");
				include(PLUS_PATH."job.cache.php");
				include(CONFIG_PATH."db.data.php");
				unset($arr_data['sex'][3]);
				$this->yunset("arr_data",$arr_data);
				$expectinfo=array();
				foreach($expect as $key=>$val){
					$jobids=@explode(',',$val['job_classid']);
					$jobname=array();
					foreach($jobids as $v){
						$jobname[]=$job_name[$v];
					}
					$expectinfo[$val['id']]['jobname']=@implode('、',$jobname);
					$expectinfo[$val['id']]['salary']=$userclass_name[$val['salary']];
				}
				foreach($rows as $k=>$v){
					$rows[$k]['jobname']=$expectinfo[$v['eid']]['jobname'];
					$rows[$k]['salary']=$expectinfo[$v['eid']]['salary'];

					foreach($userrows as $val){
						if($v['uid']==$val['uid']){
							$rows[$k]['name']=$val['name'];

							$rows[$k]['edu']=$userclass_name[$val['edu']];
							$rows[$k]['exp']=$userclass_name[$val['exp']];
							$rows[$k]['sex']=$arr_data['sex'][$val['sex']];
						}
					}
					foreach($userid_msg as $val){
						if($v['uid']==$val['uid']){
							$rows[$k]['userid_msg']=1;
						}
					}
				}
			}
		}
		$this->yunset("rows",$rows);
		$backurl=Url('wap',array('c'=>'resumecolumn'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"应聘简历");
		$this->get_user();
		$this->waptpl('hr');
	}
	function hrset_action(){
		$id=(int)$_GET['id'];
		$browse=(int)$_GET['browse'];
		if($id&&$browse){
			$nid=$this->obj->update_once("userid_job",array('is_browse'=>$browse),array("com_id"=>$this->uid,"id"=>$id));
			$this->member_log("更改申请职位状态（ID:".$id."）");
			
			if($browse==4){
				$resumeuid=$this->obj->DB_select_once("userid_job","`id`='".$id."'",'eid,job_id');
				$resumeexp=$this->obj->DB_select_once("resume_expect","`id`='".$resumeuid['eid']."' and `r_status`<>'2' and `status`='1'",'uid,uname');
				$uid=$this->obj->DB_select_once("resume","`uid`='".$resumeexp['uid']."'","telphone,email");
				$comjob=$this->obj->DB_select_once("company_job","`uid`='".$this->uid."' and `id`='".$resumeuid['job_id']."'","name,com_name");
				$data['uid']=$resumeexp['uid'];
				$data['cname']=$this->username;
				$data['name']=$resumeexp['uname'];
				$data['type']="sqzwhf";
				$data['cuid']=$this->uid;
				$data['company']=$comjob['com_name'];
				$data['jobname']=$comjob['name'];
				if($this->config['sy_msg_sqzwhf']=='1'&&$uid["telphone"]&&$this->config["sy_msguser"]&&$this->config["sy_msgpw"]&&$this->config["sy_msgkey"]&&$this->config['sy_msg_isopen']=='1'){$data["moblie"]=$uid["telphone"]; }
				if($this->config['sy_email_sqzwhf']=='1'&&$uid["email"]&&$this->config['sy_email_set']=="1"){$data["email"]=$uid["email"]; }
				if($data["email"]||$data['moblie']){
          $notice = $this->MODEL('notice');
					$notice->sendEmailType($data);
          $notice->sendSMSType($data);
				}
			}
			$nid?$this->waplayer_msg("操作成功！"):$this->waplayer_msg("操作失败！");
		}
	}
	function delhr_action(){
		$nid=$this->obj->DB_delete_all("userid_job","`id`='".(int)$_GET['id']."' and `com_id`='".$this->uid."'");
		$this->member_log("删除申请职位记录（ID:".(int)$_GET['id']."）");
		$nid?$this->waplayer_msg("删除成功！"):$this->waplayer_msg("删除失败！");
	}
	function password_action(){
		$this->rightinfo();
		
		
		
		$backurl=Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"修改密码");
		$this->get_user();
		$this->waptpl('password');
	}
	
	function time_action(){
		$com=$this->obj->DB_select_once("company","`uid`='".$this->uid."'");
		$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'");
		$this->yunset("statis",$statis);
		$this->yunset("com",$com);
		
		$pser=$this->obj->DB_select_all("company_service","`display`='1'" );
		$this->yunset("pser",$pser);
		
		$banks=$this->obj->DB_select_all("bank");
		$this->yunset("banks",$banks);
		
		if($this->config['com_vip_type'] == 2){
			$where = '`type` = 1 ';
		}
		else if($this->config['com_vip_type'] == 1){
			$where = '`type` = 2';
		}
		else{
			
			$where = '`type` = 2 ';
		}

		$rows=$this->obj->DB_select_all("company_rating","`category`='1' and `display`='1' and `service_price` > 0  and {$where} order by `type` asc,`sort` desc");
		$row=$this->obj->DB_select_once("company_rating","`category`='1' and `display`='1' and `service_price` > 0  and {$where} order by `type` asc,`sort` desc");

		if($rows&&is_array($rows)){
			foreach ($rows as $k=>$v){
				$rname=array();
				if($v['job_num']>0){$rname[]='发布职位:'.$v['job_num'].'份';}
				if($v['breakjob_num']>0){$rname[]='刷新职位:'.$v['breakjob_num'].'份';}
				if($v['resume']>0){$rname[]='下载简历:'.$v['resume'].'份';}
				if($v['interview']>0){$rname[]='邀请面试:'.$v['interview'].'份';}
				if($v['part_num']>0){$rname[]='发布兼职职位:'.$v['part_num'].'份';}
				if($v['breakpart_num']>0){$rname[]='刷新兼职职位:'.$v['breakpart_num'].'份';}
				
				if($v['msg_num']>0){$rname[]='短信数:'.$v['msg_num'].'份';}
				$rows[$k]['rname']=@implode('+',$rname);
			}
		}
		$this->yunset("rows",$rows);	
		$this->yunset("row",$row);
		$this->yunset("js_def",4);
		
		
		$this->yunset('header_title',"购买会员");

		$this->get_user();
		if($this->config['com_vip_type'] == 1 || $this->config['com_vip_type'] == 0){
			$this->waptpl('member_time');
		}else if($this->config['com_vip_type'] == 2){
			$this->waptpl('member_rating');
		}
	}
	function rating_action(){
		$com=$this->obj->DB_select_once("company","`uid`='".$this->uid."'");
		$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'");
		
		$this->yunset("statis",$statis);
		$this->yunset("com",$com);
		
		$pser=$this->obj->DB_select_all("company_service","`display`='1'" );
		$this->yunset("pser",$pser);
		
		$banks=$this->obj->DB_select_all("bank");
		$this->yunset("banks",$banks);
		
 		
		if($this->config['com_vip_type'] == 2){
			$where = '`type` = 1 ';
		}
		else if($this->config['com_vip_type'] == 1){
			$where = '`type` = 2';
		}
		else{
			
			$where = '`type` = 1 ';
		}

		$rows=$this->obj->DB_select_all("company_rating","`category`='1' and `display`='1' and `service_price` > 0 and {$where} order by `type` asc,`sort` desc");
		$row=$this->obj->DB_select_once("company_rating","`category`='1' and `display`='1' and `service_price` > 0 and {$where} order by `type` asc,`sort` desc");

		if($rows&&is_array($rows)){
			foreach ($rows as $k=>$v){
				$rname=array();
				if($v['job_num']>0){$rname[]='发布职位:'.$v['job_num'].'份';}
				if($v['breakjob_num']>0){$rname[]='刷新职位:'.$v['breakjob_num'].'份';}
				if($v['resume']>0){$rname[]='下载简历:'.$v['resume'].'份';}
				if($v['interview']>0){$rname[]='邀请面试:'.$v['interview'].'份';}
				if($v['part_num']>0){$rname[]='发布兼职职位:'.$v['part_num'].'份';}
				if($v['breakpart_num']>0){$rname[]='刷新兼职职位:'.$v['breakpart_num'].'份';}
				
				if($v['msg_num']>0){$rname[]='短信数:'.$v['msg_num'].'份';}

				
				if($this->config['com_vip_type'] == 1){
					$rows[$k]['rname'] = '时间模式会员，有效时间内，发布职位、下载简历等操作不受限制！';
				}else{
					$rows[$k]['rname']=@implode('+',$rname);
				}
			}
			
 		}
		$this->yunset("rows",$rows);
		$this->yunset("row",$row);
		$this->yunset("js_def",4);
		
		
		$this->yunset('header_title',"购买会员");
		$this->get_user();
		
 		//$this->waptpl('member_rating');
		if($this->config['com_vip_type'] == 2 || $this->config['com_vip_type'] == 0){
			$this->waptpl('member_rating');
		}else if($this->config['com_vip_type'] == 1){
			$this->waptpl('member_time');
		}

	}
	function added_action(){
		$id=intval($_GET['id']);
		$banks=$this->obj->DB_select_all("bank");
		$this->yunset("banks",$banks);
		
		
		$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'");
		$rows=$this->obj->DB_select_all("company_service","`display`='1' order by sort desc" );
 		
		if($id){
			$info=$this->obj->DB_select_all("company_service_detail","`type` = '$id' order by `sort`desc");
			$service_one = $this->obj->DB_select_once("company_service_detail","`type` = '".$id."' order by `sort`desc");
		}else{
			$row=$this->obj->DB_select_once("company_service","`display`='1'  order by sort desc","id");
			$info=$this->obj->DB_select_all("company_service_detail","`type` = '".$row['id']."' order by `sort`desc");
			$service_one = $this->obj->DB_select_once("company_service_detail","`type` = '".$row['id']."' order by `sort`desc");
		}
		
		if($statis['rating']>0){
			if($statis['vip_etime']>time()){
				$days=round(($statis['vip_etime']-mktime())/3600/24) ;
				$this->yunset("days",$days);
			}
		}
		
		if ($statis){
			$rating=$statis['rating'];
			$discount=$this->obj->DB_select_once("company_rating","`id`=$rating");
			$this->yunset("discount",$discount);
		}
		$this->yunset("statis",$statis);
		
		$this->yunset("info",$info);
		$this->yunset("p_once",$service_one);
		
		$this->yunset("rows",$rows);
		$this->yunset("js_def",4);
		$this->yunset('header_title',"增值服务");
		$this->get_user();
		$this->waptpl('added');

	}
	
	function pay_action(){
 		
		if($this->config['alipay']=='1' &&  $this->config['alipaytype']=='1'){
			$paytype['alipay']='1';
		}
		$banks=$this->obj->DB_select_all("bank");
		$this->yunset("banks",$banks);
		if($this->config['bank']=='1' &&  $banks){
			$paytype['bank']='1';
		}
		
			
		if($paytype){
			$statis=$this->company_satic();
			if($_POST['usertype']=='price'){
				
				$id=(int)$_POST['id'];
				if ($id){

					$rows=$this->obj->DB_select_once("company_rating","`service_price`<>'' and `service_time`>'0' and `id`='".$id."' and `display`='1' and `category`=1 order by sort desc","name,time_start,time_end,service_price,yh_price,id");

					
					if((int)$rows['service_price'] == 0){

 					
						$ratingM =  $this->MODEL('rating');
						$value=$ratingM->rating_info($id);
				 
						$status=$this->obj->DB_update_all('company_statis',$value,"`uid`= '".$this->uid."' ");
						$this->obj->DB_update_all("company_job","`rating`= {$id} ","`uid`='".$this->uid."'");

						if($status){
							$data['msg']="会员服务购买成功！";
							$data['url']='index.php?c=com';
							$this->yunset("layer",$data);
						}else{
							$data['msg']="服务购买失败，请稍后重试！";
							$data['url']=$_SERVER['HTTP_REFERER'];
							$this->yunset("layer",$data);
						}	

					}

				}else{
					$typeWhere = "`type` = 1";
					if($this->config['com_vip_type'] == 1){
						$typeWhere = '`type` = 2';
					}
					else if($this->config['com_vip_type'] == 0){
						$typeWhere = '`type` in (1,2) ';
					}
					$rows=$this->obj->DB_select_all("company_rating","`service_price`<>'' and `service_time`>'0' and `display`='1' and `category`=1 and {$typeWhere} order by sort desc","name,time_start,time_end,service_price,yh_price,id");
				}
				$this->yunset("rows",$rows);




			}elseif($_POST['usertype']=='service'){
				if($data['msg'] == ''){
				$id=(int)$_POST['id'];
				if($id){
					$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'");
					if ($statis){
						$rating=$statis['rating'];
						$discount=$this->obj->DB_select_once("company_rating","`id`='".$rating."'");
						$this->yunset("discount",$discount);
					}
					$rows=$this->obj->DB_select_once("company_service_detail","`id`='".$id."'","type,service_price,id");
					if ($rows['type']){
						$service=$this->obj->DB_select_once("company_service","`id`='".$rows['type']."'");
						$this->yunset("service",$service);
					}
					$this->yunset("rows",$rows);
				}else{
					$data['msg']="请选择套餐！";
					$data['url']=$_SERVER['HTTP_REFERER'];
					$this->yunset("layer",$data);
				}}else{
					$data['url']=$_SERVER['HTTP_REFERER'];
					$this->yunset("layer",$data);
				}

			}elseif($_GET['id']){
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
			$this->yunset("js_def",4);
		}else{
			$data['msg']="暂未开通手机支付，请移步至电脑端充值！";
			$data['url']=$_SERVER['HTTP_REFERER'];
			$this->yunset("layer",$data);
		}
		$nopayorder=$this->obj->DB_select_num("company_order","`uid`=".$this->uid." and `order_state`=1");
		$this->yunset('nopayorder',$nopayorder);
		$this->yunset($this->MODEL('cache')->GetCache(array('integralclass')));
		
		
		$this->yunset('header_title',"充值积分");
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
			$statis=$this->company_satic();
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
 			$this->yunset("paytype",$paytype);
 			$this->yunset("js_def",4);
		}else{
			$data['msg']="暂未开通手机支付，请移步至电脑端充值！";
			$data['url']=$_SERVER['HTTP_REFERER'];
			$this->yunset("layer",$data);
		}


		$this->yunset('header_title',"订单确认");
		$this->get_user();
		$this->waptpl('payment');
	}

	
	function company_satic(){
		$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'"); 
		if($statis['rating']){
			$rating=$this->obj->DB_select_once("company_rating","`id`='".$statis['rating']."'"); 
		}
		if($statis['vip_etime'] < time()){
			if($statis['vip_etime'] > '1'){ 
				$nums=0;
			}else if($statis['vip_etime'] < '1' && $statis['rating']!="0"){
				$nums=1;
			}else{
				$nums=0;
			}
			if($nums == 0){
				if($this->config['com_vip_done']=='0'){
					$data['job_num']=$data['down_resume']=$data['invite_resume']=$data['editjob_num']=$data['breakjob_num']=$data['part_num']=$data['editpart_num']=$data['breakpart_num']=$data['zph_num']='0';
					$data['oldrating_name']=$statis['rating_name'];
					$statis['rating_name']=$data['rating_name']="过期会员";
					$statis['rating_type']=$statis['rating']=$data['rating_type']=$data['rating']="0"; 					
					$where['uid']=$this->uid;
					$this->obj->DB_update_all("company_job","`rating`='0'","`uid`='".$this->uid."'");
					$this->obj->update_once("company_statis",$data,$where);
				}elseif ($this->config['com_vip_done']=='1'){
					$ratingM = $this->MODEL('rating');
					$rat_value=$ratingM->rating_info();
					$this->obj->DB_update_all("company_job","`rating`='".$this->config['com_rating']."'","`uid`='".$this->uid."'");
					$this->obj->DB_update_all("company_statis",$rat_value,"`uid`='".$this->uid."'");
				}
			}
		}
		if($statis['autotime']>=time()){
			$statis['auto'] = 1;
		}
		
		if($statis['vip_etime']>time() || $statis['vip_etime']==0){
			if($statis['rating_type']=="2"){
				$addjobnum=$addpartjobnum=$editjobnum=$editpartjobnum='1';
			}else if($statis['rating_type']=="1"){
				if($statis['job_num']>0){
					$addjobnum='1';
				}else{
 					$addjobnum='2';
				}
				if($statis['part_num']>0){
					$addpartjobnum='1';
				}else{
					$addpartjobnum='2';
				}
			}else{
				$addjobnum=$addpartjobnum=$editjobnum=$editpartjobnum='0';
			}
		}else {
			$addjobnum=$addpartjobnum='0';
		}
		$statis['addjobnum']=$addjobnum;
		$statis['addpartjobnum']=$addpartjobnum;
		$statis['pay_format']=number_format($statis['pay'],2);
		$statis['integral_format']=number_format($statis['integral']);
		$this->yunset("addjobnum",$addjobnum);
		$this->yunset("addpartjobnum",$addpartjobnum);
		$this->yunset("statis",$statis);
		$this->yunset("rating",$rating);
		return $statis;
	}


	function getOrder_action(){
		if($_POST){
       		$M=$this->MODEL('compay');
			if($_POST['server']=='autojob'){
 				$return = $M->buyAutoJob($_POST);
			}elseif ($_POST['server']=='zdjob'){
				$return = $M->buyZdJob($_POST);
			}elseif ($_POST['server']=='ujob'){
				$return = $M->buyUrgentJob($_POST);
			}elseif ($_POST['server']=='recjob'){
				$return = $M->buyRecJob($_POST);
			}elseif ($_POST['server']=='sxjob'){
				$return = $M->buyRefreshJob($_POST);
			}elseif ($_POST['server']=='issue'){
				$return = $M->buyIssueJob($_POST);
			}elseif ($_POST['server']=='issuepart'){
				$return = $M->buyIssuePart($_POST);
			}elseif ($_POST['server']=='downresume'){
				$return = $M->buyDownresume($_POST);
			}elseif ($_POST['server']=='invite'){
				$return = $M->buyInviteResume($_POST);
			}elseif ($_POST['server']=='sxpart'){
				$return = $M->buyRefreshPart($_POST);
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
				
				if($return['error']){
					$data['msg']=$return['error'];
				}else{
					$data['msg']="提交失败，请重新提交订单！";
				}
				
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

	function dkzf_action(){
		if($_POST){
   			$M=$this->MODEL('jfdk');
			if ($_POST['jobautoids']){
				$return = $M->buyAutoJob($_POST);
			}elseif($_POST['zdjobid']){		
				$return = $M->buyZdJob($_POST);
			}elseif ($_POST['recjobid']){
				$return = $M->buyRecJob($_POST);
			}elseif ($_POST['ujobid']){
				$return = $M->buyUrgentJob($_POST);
			}elseif ($_POST['sxjobid']){
				$return = $M->buyRefreshJob($_POST);
			}elseif ($_POST['eid']){
				$return = $M->downresume($_POST);
			}elseif ($_POST['issuejob']){
				$return = $M->buyIssueJob($_POST);
			}elseif ($_POST['issuepart']){
				$return = $M->buyIssuePart($_POST);
			}elseif ($_POST['invite']){
				$return = $M->buyInviteResume($_POST);
			}elseif($_POST['tcid']){
				$return = $M->buyPackOrder($_POST);
			}elseif($_POST['id']){
				$return = $M->buyVip($_POST);
			}elseif ($_POST['sxpartid']){
				$return = $M->buyRefreshPart($_POST);
			}
			if($return['status']==1){
				
				echo json_encode(array('error'=>0,'msg'=>$return['msg']));
			}else{
				
				echo json_encode(array('error'=>1,'msg'=>$return['error'],'url'=>$return['url']));
			}
		}else{
			echo json_encode(array('error'=>1,'msg'=>'参数错误，请重试！'));
		}
	}
	function dingdan_action(){
		$data['msg']="参数不正确，请正确填写！";
		$data['url']=$_SERVER['HTTP_REFERER'];
		
		if($_POST['price']){
			
			$statis = $this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'","`integral`");
			
			if($_POST['comvip']){
				$comvip=(int)$_POST['comvip'];
				$ratinginfo =  $this->obj->DB_select_once("company_rating","`id`='".$comvip."'");
				
				$dkjf=(int)$_POST['dkjf'];
				
				if($dkjf >= (int)$statis['integral']){
					$dkjf = $statis['integral'];
				}
				
				if($dkjf){
					$price_ = $dkjf / $this->config['integral_proportion'];
				}else{
					$price_ = 0;
				}
				
				if($ratinginfo['time_start']<time() && $ratinginfo['time_end']>time()){
					$price = $ratinginfo['yh_price'] - $price_ ;
				}else{
					$price = $ratinginfo['service_price'] - $price_;
				}
				
				$data['type']='1';

			}elseif($_POST['comservice']){
				
 				
				$id=(int)$_POST['comservice'];
				
				$dkjf=(int)$_POST['dkjf'];
				
				if($dkjf >= (int)$statis['integral']){
					$dkjf = (int)$statis['integral'];
				}
				
				if($dkjf){
					$price_ = $dkjf / $this->config['integral_proportion'];
				}else{
					$price_ = 0;
				}
				
				$price=$_POST['price'] - $price_ ;

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
			$data['order_state']="1";
			$data['order_type']=$_POST['paytype'];
			$data['order_remark']=trim($_POST['remark']);
			$data['uid']=$this->uid;
			$data['rating']=$_POST['comvip']?$_POST['comvip']:$_POST['comservice'];
			$data['integral']=$_POST['price_int'];
			
			if(is_uploaded_file($_FILES['pic']['tmp_name'])){
			    $UploadM=$this->MODEL('upload');
			    $upload=$UploadM->Upload_pic(APP_PATH."/data/upload/order/",false);
			    $pictures=$upload->picture($_FILES['pic']);
			    $pic=str_replace(APP_PATH."/data/upload/order/","./data/upload/order/",$pictures);
			    $data['order_pic']=$pic;
			}
			
			$id=$this->obj->insert_into("company_order",$data);
			
			if($id){
				
				
				if($_POST['comservice']){
					$this->MODEL('integral')->company_invtal($this->uid,$dkjf,$auto,"购买增值包",true,2,'integral',11);
				}else if($_POST['comvip']){
					$this->MODEL('integral')->company_invtal($this->uid,$dkjf,$auto,"购买会员",true,2,'integral',27);
				}
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
				
				$statis = $this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'","`integral`");
				
				if($_POST['price']){
					if($_POST['comvip']){
						$comvip=(int)$_POST['comvip'];
						
						$ratinginfo =  $this->obj->DB_select_once("company_rating","`id`='".$comvip."'");
						
						$dkjf=(int)$_POST['dkjf'];
						
						if($dkjf >= (int)$statis['integral']){
							$dkjf = $statis['integral'];
						}
						
						if($dkjf){
							$price_ = $dkjf / $this->config['integral_proportion'];
						}else{
							$price_ = 0;
						}
						
						if($ratinginfo['time_start']<time() && $ratinginfo['time_end']>time()){
							$price = $ratinginfo['yh_price'] - $price_ ;
						}else{
							$price = $ratinginfo['service_price'] - $price_;
						}
						 
						$data['type']='1';
					}elseif($_POST['comservice']){

						$id=(int)$_POST['comservice'];
				
						$dkjf=(int)$_POST['dkjf'];
						
						if($dkjf >= (int)$statis['integral']){
							$dkjf = (int)$statis['integral'];
						}
						
						if($dkjf){
							$price_ = $dkjf / $this->config['integral_proportion'];
						}else{
							$price_ = 0;
						}
						
						$price=$_POST['price'] - $price_ ;
		
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
			$this->get_user();
			$this->waptpl('payment');
	}
	function look_job_action(){
		$urlarr['c']='look_job';
		$urlarr["page"]="{{page}}";
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("look_job","`com_id`='".$this->uid."' and `com_status`='0' order by datetime desc",$pageurl,"10");
		if(is_array($rows))
		{
			foreach($rows as $v)
			{
				$uid[]=$v['uid'];
				$jobid[]=$v['jobid'];
			}
			$cjob=$this->obj->DB_select_all("company_job","`id`in(".@implode(',',$jobid).")","`name`,`id`");
			$resume=$this->obj->DB_select_all("resume","`uid` in (".pylode(",",$uid).")","`uid`,`name`,`edu`,`exp`,`sex`,`def_job` as `eid`");
			$userid_msg=$this->obj->DB_select_all("userid_msg","`fid`='".$this->uid."' and `uid` in (".pylode(",",$uid).")","uid");
			include(PLUS_PATH."user.cache.php");
			include(PLUS_PATH."job.cache.php");
			include(CONFIG_PATH."db.data.php");
			unset($arr_data['sex'][3]);
			$this->yunset("arr_data",$arr_data);
			foreach($resume as $val){
				$eid[]=$val['eid'];
			}
			$expect=$this->obj->DB_select_all("resume_expect","`id` in (".pylode(",",$eid).")","`id`,`uid`,`salary`,job_classid");
			foreach($rows as $key=>$val)
			{
				foreach($expect as $v){
					if($val['uid']==$v['uid']){
						$rows[$key]['resume_id']=$v['id'];
						$rows[$key]['salary']=$userclass_name[$v['salary']];
						if($v['job_classid']!=""){
							$job_classid=@explode(",",$v['job_classid']);
							$rows[$key]['jobname']=$job_name[$job_classid[0]];
						}
					}
				}
				foreach($resume as $va)
				{
					if($val['uid']==$va['uid'])
					{
						$rows[$key]['sex']=$arr_data['sex'][$va['sex']];
						$rows[$key]['exp']=$userclass_name[$va['exp']];
						$rows[$key]['edu']=$userclass_name[$va['edu']];
						$rows[$key]['name']=$va['name'];
					}
				}
				foreach($userid_msg as $va)
				{
					if($val['uid']==$va['uid'])
					{
						$rows[$key]['userid_msg']=1;
					}
				}
				foreach($cjob as $va)
				{
					if($val['jobid']==$va['id'])
					{
						$rows[$key]['comjob']=$va['name'];
					}
				}
			}
		}
		$this->yunset("rows",$rows);
		$this->yunset("js_def",5);
		$backurl=Url('wap',array('c'=>'resumecolumn'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"谁看过我");
		$this->get_user();
		$this->waptpl('look_job');
	}
	function lookresumedel_action(){
		if($_GET['id']){
			$nid=$this->obj->DB_update_all("look_resume","`com_status`='1'","`id`='".(int)$_GET['id']."' and `com_id`='".$this->uid."'");
			if($nid){
				$this->member_log("删除已浏览简历记录（ID:".(int)$_GET['id']."）");
				$this->waplayer_msg('删除成功！');
			}else{
				$this->waplayer_msg('删除失败！');
			}
		}
	}
	function lookjobdel_action(){
		if($_GET['id']){
			$nid=$this->obj->DB_update_all("look_job","`com_status`='1'","`id`='".(int)$_GET['id']."' and `com_id`='".$this->uid."'");
			if($nid){
				$this->member_log("删除已浏览简历记录（ID:".(int)$_GET['id']."）");
				$this->waplayer_msg('删除成功！');
			}else{
				$this->waplayer_msg('删除失败！');
			}
		}
	}

	function look_resume_action(){
		$urlarr['c']='look_resume';
		$urlarr["page"]="{{page}}";
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("look_resume","`com_id`='".$this->uid."' and `com_status`='0' order by datetime desc",$pageurl,"10");
		if(is_array($rows)){
			foreach($rows as $v){
				$resume_id[]=$v['resume_id'];
				$uid[]=$v['uid'];
			}
			$resume=$this->obj->DB_select_alls("resume","resume_expect","a.uid=b.uid and b.`id` in (".pylode(",",$resume_id).")","a.`name`,a.`sex`,a.`exp`,a.`edu`,a.`birthday`,b.`id`,b.job_classid,b.`salary`");
			$userid_msg=$this->obj->DB_select_all("userid_msg","`fid`='".$this->uid."' and `uid` in (".pylode(",",$uid).")","uid");
			if(is_array($resume)){
				include(PLUS_PATH."user.cache.php");
				include(PLUS_PATH."job.cache.php");
				include(CONFIG_PATH."db.data.php");
				unset($arr_data['sex'][3]);
				$this->yunset("arr_data",$arr_data);
				$age=date("Y",time());
				$time=date("Y",0);
				foreach($rows as $key=>$val){
					foreach($resume as $va){
						if($val['resume_id']==$va['id']){
							$rows[$key]['name']=$va['name'];
							$rows[$key]['salary']=$userclass_name[$va['salary']];
							$rows[$key]['birthday']=$va['birthday'];
							$rows[$key]['sex']=$arr_data['sex'][$va['sex']];
							$rows[$key]['exp']=$userclass_name[$va['exp']];
							$rows[$key]['edu']=$userclass_name[$va['edu']];
							if($va['job_classid']!=""){
								$job_classid=@explode(",",$va['job_classid']);
								$rows[$key]['jobname']=$job_name[$job_classid[0]];
							}
						}
					}
					foreach($userid_msg as $va){
						if($va['uid']&&$val['uid']&&$val['uid']==$va['uid']){
							$rows[$key]['userid_msg']=1;
						}
					}
				}
			}
		}
		$this->yunset("age",$age);
		$this->yunset("time",$time);
		$this->yunset("rows",$rows);
		$this->yunset("js_def",5);
		$backurl=Url('wap',array('c'=>'resumecolumn'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"浏览简历");
		$this->get_user();
		$this->waptpl('look_resume');
	}
	
	
	function talent_pool_remark_action()
	{
		if($_POST['remark']=="")
		{
			$this->ACT_layer_msg("备注内容不能为空！",8,$_SERVER['HTTP_REFERER']);
		}else{
			$nid=$this->obj->DB_update_all("talent_pool","`remark`='".$_POST['remark']."'","`id`='".(int)$_POST['id']."' and `cuid`='".$this->uid."'");
			if($nid)
			{
				$this->member_log("备注人才".$_POST['r_name']);
				
				$data['msg']="备注成功！";
				$data['url']=$this->config['sy_weburl'].'/wap/member/index.php?c=talent_pool';
			}else{
				
				$data['msg']="备注失败！";
				$data['url']=$this->config['sy_weburl'].'/wap/member/index.php?c=talent_pool';
			}
		}
		$this->yunset("layer",$data);
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		
		$this->get_user();
		$this->waptpl('talent_pool');
	}
	function talentpooldel_action(){
		if($_GET['id']){
			$nid=$this->obj->DB_delete_all("talent_pool","`id`='".(int)$_GET['id']."' and `cuid`='".$this->uid."'");
			if($nid){
				$this->member_log("删除收藏简历人才（ID:".(int)$_GET['id']."）");
				$this->waplayer_msg('删除成功！');
			}else{
				$this->waplayer_msg('删除失败！');
			}
		}
	}
	function talent_pool_action(){
		$where="`cuid`='".$this->uid."'";
		$urlarr['c']='talent_pool';
		$urlarr["page"]="{{page}}";
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("talent_pool",$where."  order by id desc",$pageurl,"10");
		if(is_array($rows)) {
			foreach($rows as $v) {
				$uid[]=$v['uid'];
				$eid[]=$v['eid'];
			}
			$resume=$this->obj->DB_select_alls("resume","resume_expect","a.uid=b.uid and a.`r_status`<>'2' and a.uid in (".pylode(',',$uid).")","a.`name`,a.`uid`,a.`sex`,a.`birthday`,b.`edu`,a.`exp`,b.`job_classid`,b.id as eid,b.salary");
			$user=$this->obj->DB_select_all("resume","`birthday`limit 2");

			$userid_msg=$this->obj->DB_select_all("userid_msg","`fid`='".$this->uid."' and `uid` in (".pylode(",",$uid).")","uid");
			if(is_array($resume)) {
				include(PLUS_PATH."user.cache.php");
				include(PLUS_PATH."job.cache.php");
				include(CONFIG_PATH."db.data.php");
				unset($arr_data['sex'][3]);
				$this->yunset("arr_data",$arr_data);
				$age=date("Y",time());
				$time=date("Y",0);
				foreach($rows as $key=>$val) {
					foreach($resume as $va) {
						if($val['uid']==$va['uid'])
						{
							$rows[$key]['birthday']=$va['birthday'];
							$rows[$key]['eid']=$va['eid'];
							$rows[$key]['name']=$va['name'];
							$rows[$key]['sex']=$arr_data['sex'][$va['sex']];
							$rows[$key]['exp']=$userclass_name[$va['exp']];
							$rows[$key]['edu']=$userclass_name[$va['edu']];
							if($va['job_classid']!="")
							{
								$job_classid=@explode(",",$va['job_classid']);
								$rows[$key]['jobname']=$job_name[$job_classid[0]];
							}
						}
					}
					foreach($user as $value){
						if($val['uid']==$value['uid']){
							$rows[$key]['age']=$user['age'];
						}
					}
					foreach($userid_msg as $va)
					{
						if($val['uid']==$va['uid'])
						{
							$rows[$key]['userid_msg']=1;
						}
					}
				}
			}
		}
		$this->yunset("age",$age);
		$this->yunset("time",$time);
		$this->yunset("rows",$rows);
		$this->company_satic();
		$this->yunset("js_def",5);
		if($_GET['type']){
			$backurl=Url('wap',array(),'member');
		}else{
			$backurl=Url('wap',array('c'=>'resumecolumn'),'member');
		}
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"收藏人才");
		$this->get_user();
		$this->waptpl('talent_pool');
	}
	
	

	
	
	function invite_action(){
		$urlarr['c']='invite';
		$urlarr["page"]="{{page}}";
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("userid_msg"," `fid`='".$this->uid."' order by id desc",$pageurl,"10");
		if(is_array($rows) && !empty($rows)){
			foreach($rows as $v){
				$uid[]=$v['uid'];
			}
			$resume=$this->obj->DB_select_all("resume","`uid` in (".pylode(",",$uid).") and `r_status`<>'2'","`uid`,`name`,`exp`,`sex`,`edu`,`def_job` as `eid`");
			foreach($resume as $val){
				$eid[]=$val['eid'];
			}
			$expect=$this->obj->DB_select_all("resume_expect","`id` in (".pylode(",",$eid).")","`salary`,`id`,`job_classid`");
			if(is_array($resume)){
				$user=array();
				include(PLUS_PATH."user.cache.php");
				include(PLUS_PATH."job.cache.php");
				include(CONFIG_PATH."db.data.php");
				unset($arr_data['sex'][3]);
				$this->yunset("arr_data",$arr_data);
				foreach($resume as $val){
					foreach($expect as $v){
						if($v['id']==$val['eid']){
							$user[$val['uid']]['salary']=$userclass_name[$v['salary']];
							if($v['job_classid']!=""){
								$job_classid=@explode(",",$v['job_classid']);
								$user[$val['uid']]['jobname']=$job_name[$job_classid[0]];
							}
						}
					}

					$user[$val['uid']]['eid']=$val['eid'];
					$user[$val['uid']]['name']=mb_substr($val['name'],0,8);
					$user[$val['uid']]['exp']=$userclass_name[$val['exp']];
					$user[$val['uid']]['edu']=$userclass_name[$val['edu']];
					$user[$val['uid']]['sex']=$arr_data['sex'][$val['sex']];
				}
			}

			$this->yunset("user",$user);
		}
		$this->yunset("rows",$rows);
		$this->yunset("js_def",5);
		$backurl=Url('wap',array('c'=>'resumecolumn'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"面试邀请");
		$this->get_user();
		$this->waptpl('invite');
	}
	
	function invite_del_action(){
		if($_GET['id']){
			$nid=$this->obj->DB_delete_all("userid_msg","`id`='".(int)$_GET['id']."' and `fid`='".$this->uid."'");
			if($nid){
				$this->member_log("删除已邀请面试的人才",4,3);
				$this->waplayer_msg('删除成功！');
			}else{
				$this->waplayer_msg('删除失败！');
			}
		}
	}
	function part_action(){
		$this->rightinfo();
		$urlarr=array("c"=>"part","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("partjob","`uid`='".$this->uid."'",$pageurl,"10");
		if(is_array($rows)){
			foreach($rows as $k=>$v){
				$rows[$k]['applynum']=$this->obj->DB_select_num("part_apply","`jobid`='".$v['id']."'");
			}
		}
		$this->company_satic();
		$this->yunset("rows",$rows);
		$backurl=Url('wap',array('c'=>'jobcolumn'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"兼职管理");
		$this->get_user();
		$this->waptpl('part');
	}
	function partadd_action(){
		include(CONFIG_PATH."db.data.php");
		$this->yunset("arr_data",$arr_data);
		$statics=$this->company_satic();
		if($_GET['id']){
			$row=$this->obj->DB_select_once("partjob","`uid`='".$this->uid."' and `id`='".(int)$_GET['id']."'");
			$row['work']=$row['worktime'];
			$row['worktime']=explode(',', $row['worktime']);
			$arr_data1=$arr_data['sex'][$row['sex']];
			$this->yunset("arr_data1",$arr_data1);
			$row['content_t']=strip_tags($row['content']);
			$this->yunset("row",$row);
		}else{
			if($this->config['integral_partjob']!='0'){
				if( $statics['addltjobnum'] == 2){
					$data['msg']="您的套餐已用完！";
					$data['url']='index.php?c=rating';
				}
			}else{
				if($statics['addpartjobnum']==2){ 
					$this->obj->DB_update_all("company_statis","`part_num` = '1'","`uid`='".$this->uid."'");
				}elseif($statics['addpartjobnum']==0){ 
					$data['msg']="您的会员已到期！";
					$data['url']='index.php?c=rating';
				}
			}
		}
		if($_POST['submit']){
			$_POST['content']=str_replace(array("&amp;","background-color:#ffffff","background-color:#fff","white-space:nowrap;"),array("&",'background-color:','background-color:','white-space:'),$_POST['content']);
			$_POST['sdate']=strtotime($_POST['sdate']);
			
			if($_POST['timetype']!='1'){
				$_POST['edate']="";
				$_POST['deadline']="";
			}else{
				$_POST['edate']=strtotime($_POST['edate']);
				$_POST['deadline']=strtotime($_POST['deadline']);
			}
			$_POST['state'] = $this->config['com_partjob_status'];
			$id=(int)$_POST['id'];
			unset($_POST['submit']);
			unset($_POST['id']);
			if(!$id){
				$_POST['addtime'] = time();
				$_POST['uid'] = $this->uid;
				$_POST['lastupdate'] = time();
				$data['msg']=$this->get_com(7);
				if($data['msg']==''){
					$company=$this->obj->DB_select_once("company","`uid`='".$this->uid."'");
					$_POST['com_name']=$company['name'];

					
					if(!isset($_POST['state']) || ($_POST['state'] != 0 && $this->usertype == 2)){
						$member = $this->obj->DB_select_once("member", "`uid`='{$this->uid}'", "`status`");
						if($member['status'] != 1){
							$_POST['state'] = 0;
						}
					}

					$nid=$this->obj->insert_into("partjob",$_POST);
					$name="添加兼职职位";
					if($nid){
						$state_content = "新发布了兼职职位 <a href=\"".$this->config['sy_weburl']."/part/index.php?c=show&id=$nid\" target=\"_blank\">".$_POST['name']."</a>。";
						$this->addstate($state_content,2);
						$nid?$data['msg']=$name."成功！":$data['msg']=$name."失败！";
					}
				}
			}else{
				$job=$this->obj->DB_select_once("partjob","`id`='".$id."' and `uid`='".$this->uid."'","state");
				 
				if($data['msg']==''){
					$where['id']=$id;
					$where['uid']=$this->uid;
					$nid=$this->obj->update_once("partjob",$_POST,$where);
					$name="更新兼职职位";
					$nid?$data['msg']=$name."成功！":$data['msg']=$name."失败！";
				}
			}
			$data['url']='index.php?c=part';
			echo json_encode($data);die;
		}
		$this->rightinfo();
		$this->yunset("layer",$data);
		$this->yunset($this->MODEL('cache')->GetCache(array('city','part')));
		$morning=array('0101','0201','0301','0401','0501','0601','0701');
		$noon=array('0102','0202','0302','0402','0502','0602','0702');
		$afternoon=array('0103','0203','0303','0403','0503','0603','0703');
		$this->yunset(array('morning'=>$morning,'noon'=>$noon,'afternoon'=>$afternoon));
		$this->yunset("today",date("Y-m-d"));
		
		
		$this->yunset('header_title',"发布兼职");
		$this->get_user();
		$this->waptpl('partadd');
	}
	function partdel_action(){
		if($_GET['id']){
			$nid=$this->obj->DB_delete_all("partjob","`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'");
			if($nid){
				$this->obj->DB_delete_all("part_collect","`jobid`='".(int)$_GET['id']."'","");
				$this->obj->DB_delete_all("part_apply","`jobid`='".(int)$_GET['id']."'","");
				$this->member_log("删除兼职");
				$this->waplayer_msg('删除成功！');
			}else{
				$this->waplayer_msg('删除失败！');
			}
		}
	}
	function photo_action(){
		if($_POST['submit']){
			$pic=$this->wap_up_pic($_POST['uimage'],'company');
			if($pic['errormsg']){echo 2;die;}
			if($pic['re']){
				$company=$this->obj->DB_select_once("company","`uid`='".$this->uid."'","`logo`");
				if(!$company['logo']){
					$this->MODEL('integral')->get_integral_action($this->uid,"integral_avatar","上传LOGO");
				}
				unlink_pic(APP_PATH.$company['logo']);
				$photo="./data/upload/company/".date('Ymd')."/".$pic['new_file'];
				$this->obj->DB_update_all("company","`logo`='".$photo."'","`uid`='".$this->uid."'");
				$this->obj->DB_update_all("company_job","`com_logo`='".$photo."'","`uid`='".$this->uid."'");
				$this->obj->DB_update_all("answer","`pic`='".$photo."'","`uid`='".$this->uid."'");
				$this->obj->DB_update_all("question","`pic`='".$photo."'","`uid`='".$this->uid."'");
				echo 1;die;
			}else{
				unlink_pic(APP_PATH."data/upload/company/".date('Ymd')."/".$pic['new_file']);
				echo 2;die;
			}
		}else{
			$company=$this->obj->DB_select_once("company","`uid`='".$this->uid."'","`logo`");
			if(!$company['logo'] || !file_exists(str_replace('./',APP_PATH,$company['logo']))){
				$company['logo']=$this->config['sy_weburl']."/".$this->config['sy_unit_icon'];
			}else{
				$company['logo']="";
			}
			$this->yunset("company",$company);
			if($_GET['t']){
				$backurl=Url('wap',array(),'member');
			}else if($_GET['type']){
				$backurl=Url('wap',array('c'=>'integral'),'member');
			}else{
				$backurl=Url('wap',array('c'=>'info'),'member');
			}
			$this->yunset('backurl',$backurl);
			
			$this->yunset('header_title',"企业LOGO");
			
			$this->get_user();
			$this->waptpl('photo');
		}
	}

	function comcert_action(){
		
		if($_POST['submit']){
			$comname=$this->obj->DB_select_num('company',"`uid`<>'".$this->uid."' and `name`='".$_POST['name']."'","`uid`");
            
            $row=$this->obj->DB_select_once("company_cert","`uid`='".$this->uid."' and type='3'");
            
            if($_POST['name']==""){
				$data['msg']='企业全称不能为空！';
			}elseif($comname){
				$data['msg']='企业全称已存在！';

			}elseif(!$_POST['preview']&&!$row['check']){

				$data['msg']='请上传营业执照！';
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
						if($row['check']){
							unlink_pic(APP_PATH.$row['check']);
						}
					}
				}else{
					$photo=$row['check'];
				}
			}
			
			if($data['msg']==""){
				if($this->config['com_cert_status']=="1"){
					$sql['status']=0;
				}else{
					$sql['status']=1;
				}
				$this->obj->DB_update_all("company","`name`='".$_POST['name']."',`yyzz_status`='".$sql['status']."'","`uid`='".$this->uid."'");
				$sql['step']=1;
				$sql['check']=$photo;
				$sql['check2']="0";
				$sql['ctime']=mktime();
				$company=$this->obj->DB_select_once("company_cert","`uid`='".$this->uid."'  and type='3'","`check`");
				if(is_array($company)){
					$where['uid']=$this->uid;
					$where['type']='3';
					$this->obj->update_once("company_cert",$sql,$where);
					$this->obj->member_log("更新营业执照");
				}else{
					$sql['uid']=$this->uid;
					$sql['did']=$this->userdid;
					$sql['type']=3;
					$this->obj->insert_into("company_cert",$sql);
					$this->obj->member_log("上传营业执照");
					if($this->config['com_cert_status']!="1"){
						$this->MODEL('integral')->get_integral_action($this->uid,"integral_comcert","认证营业执照");
					}
				}
				$data['msg']='上传营业执照成功！';
				$data['url']='index.php?c=set';
			}else{
				$data['msg']=$data['msg'];
				$data['url']='index.php?c=comcert';
			}
		}
		$company=$this->obj->DB_select_once("company","`uid`='".$this->uid."'","`name`");
		
		$cert=$this->obj->DB_select_once("company_cert","`uid`='".$this->uid."' and `type`='3'");
		if($cert['check'] && file_exists(str_replace('./',APP_PATH,$cert['check']))){
			$cert['check']=str_replace('./',$this->config['sy_weburl'].'/',$cert['check']);
		}else{
			$cert['check']="";
		}
		$this->yunset("company",$company);
		$this->yunset("cert",$cert);
		$this->yunset("layer",$data);
		
		$backurl=Url('wap',array('c'=>'binding'),'member');
		$this->yunset("backurl",$backurl);
		
		$backurl=Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		
		$this->yunset('header_title',"营业执照");
		$this->get_user();
		$this->waptpl('comcert');
	}

	function binding_action(){
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
					
					$this->obj->DB_update_all("resume","`moblie_status`='0'","`telphone`='".$row['check']."'");
					$this->obj->DB_update_all("company","`moblie_status`='0'","`linktel`='".$row['check']."'");
					
					$this->obj->DB_update_all("member","`moblie`='".$row['check']."'","`uid`='".$this->uid."'");
					$this->obj->DB_update_all("company","`linktel`='".$row['check']."',`moblie_status`='1'","`uid`='".$this->uid."'");
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
			if($_GET['type']=="moblie")
			{
				$this->obj->DB_update_all("company","`moblie_status`='0'","`uid`='".$this->uid."'");
			}
			if($_GET['type']=="email")
			{
				$this->obj->DB_update_all("company","`email_status`='0'","`uid`='".$this->uid."'");
			}
			if($_GET['type']=="wxid")
			{
				$this->obj->DB_update_all("member","`wxid`='',`wxopenid`='',`unionid`=''","`uid`='".$this->uid."'");
			}
			if($_GET['type']=="qqid")
			{
				$this->obj->DB_update_all("member","`qqid`=''","`uid`='".$this->uid."'");
			}
			if($_GET['type']=="sinaid")
			{
				$this->obj->DB_update_all("member","`sinaid`=''","`uid`='".$this->uid."'");
			}
			$this->waplayer_msg('解除绑定成功！');
		}
		$member=$this->obj->DB_select_once("member","`uid`='".$this->uid."'");
		$this->yunset("member",$member);
		$company=$this->obj->DB_select_once("company","`uid`='".$this->uid."'");
		$this->yunset("company",$company);
		 
		if($company['yyzz_status']!=1){
			$cert=$this->obj->DB_select_once("company_cert","`uid`='".$this->uid."' and `type`='3'","`id`,`status`,`statusbody`");
			$this->yunset("cert",$cert);
		}

		$this->rightinfo();
		$backurl=Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"社交账号绑定");
		$this->get_user();
		$this->waptpl('binding');
	}
	function bindingbox_action(){
		$member=$this->obj->DB_select_once("member","`uid`='".$this->uid."'");
		$this->yunset("member",$member);
		$this->rightinfo();
		$backurl=Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"账户绑定");
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
		$this->yunset('header_title',"修改用户名");
		$this->get_user();
		$this->waptpl('setname');
	}

	function reward_list_action(){
		$urlarr['c']='reward_list';
		$urlarr["page"]="{{page}}";	
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("change","`uid`='".$this->uid."' order by id desc",$pageurl,"10");		
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
		
		$dh = $sh = $wtg =0;
		if(is_array($rows)){
			foreach($rows as $value){
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
		
		$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'","rating_name,integral");
		$statis[integral]=number_format($statis[integral]);
		$this->yunset("statis",$statis);
		$this->yunset('rows',$rows);
		$backurl=Url('wap',array('c'=>'integral'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"兑换记录");
		$this->get_user();
		$this->waptpl('reward_list');
	}

	function delreward_action(){
		if($this->usertype!='2' || $this->uid==''){
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
	function paylog_action(){
		include(CONFIG_PATH."db.data.php");
		$this->yunset("arr_data",$arr_data);
		$urlarr=array("c"=>"paylog","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$where="`uid`='".$this->uid."' order by order_time desc";
		$rows=$this->get_page("company_order",$where,$pageurl,"10");
		$this->yunset("rows",$rows);
		
		$this->yunset('header_title',"财务明细");
		$this->get_user();
		$this->waptpl('paylog');
	}

	function delpaylog_action(){
		if($this->usertype!='2' || $this->uid==''){
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
		if ($_GET['type']==1){
			$this->yunset('backurl',Url('wap',array('c'=>'com'),'member'));
		}else{
			$backurl=Url('wap',array('c'=>'integral'),'member');			
		}
		$this->yunset('backurl',$backurl);
		$this->yunset("rows",$rows);
		$this->yunset('header_title',"财务明细");
		$this->get_user();
		$this->waptpl('consume');
	}

	function down_action(){
		$where="`comid`='".$this->uid."'";
		$urlarr['c']='down';
		$urlarr["page"]="{{page}}";
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("down_resume","$where order by id desc",$pageurl,"10");
		if(is_array($rows)&&$rows){
			if(empty($resume)){
				foreach($rows as $v){
					$uid[]=$v['uid'];
					$eid[]=$v['eid'];
				}
				$resume=$this->obj->DB_select_alls("resume","resume_expect","a.uid=b.uid and a.`r_status`<>'2' and a.uid in (".@implode(",",$uid).") and b.id in (".@implode(",",$eid).")","a.`name`,a.`uid`,a.`exp`,a.`sex`,a.`edu`,b.`id`,b.`minsalary`,b.`maxsalary`,b.`job_classid`,b.`height_status`");
			}
			$userid_msg=$this->obj->DB_select_all("userid_msg","`fid`='".$this->uid."' and `uid` in (".pylode(",",$uid).")","uid");
			if(is_array($resume)){
				include(PLUS_PATH."user.cache.php");
				include(PLUS_PATH."job.cache.php");
				include(CONFIG_PATH."db.data.php");
				unset($arr_data['sex'][3]);
				$this->yunset("arr_data",$arr_data);
				foreach($rows as $key=>$val){
					foreach($resume as $va){
						if($val['eid']==$va['id']){
							$rows[$key]['name']=$va['name'];
							$rows[$key]['sex']=$arr_data['sex'][$va['sex']];
							$rows[$key]['exp']=$userclass_name[$va['exp']];
							$rows[$key]['edu']=$userclass_name[$va['edu']];
							$rows[$key]['minsalary']=$va['minsalary'];
							$rows[$key]['maxsalary']=$va['maxsalary'];
							if($va['job_classid']!=""){
								$job_classid=@explode(",",$va['job_classid']);
								$rows[$key]['jobname']=$job_name[$job_classid[0]];
							}
						}
					}
					foreach($userid_msg as $va){
						if($val['uid']==$va['uid']){
							$rows[$key]['userid_msg']=1;
						}
					}
				}
			}
		}
		$this->yunset("rows",$rows);
		$backurl=Url('wap',array('c'=>'resumecolumn'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"下载简历");
		$this->get_user();
		$this->waptpl('down');
	}

	function downdel_action(){
		if($_GET['id']){
			$nid=$this->obj->DB_delete_all("down_resume","`id`='".(int)$_GET['id']."' and `comid`='".$this->uid."'"," ");
			if($nid){
				$this->member_log("删除已下载简历记录（ID:".(int)$_GET['id']."）");
				$this->waplayer_msg('删除成功！');
			}else{
				$this->waplayer_msg('删除失败！');
			}
		}
	}
	
	function ajax_refresh_job_action()
	{
		if(!isset($_POST['jobid'])){
			exit;
		}

		$jobid = $_POST['jobid'];
		
		$statis = $this->company_satic();

		$msg = '';
		
		
		$companyM = $this->MODEL('company');
		$result = $companyM->comVipDayActionCheck('refreshjob',$this->uid);
		if($result['status']!=1){
			echo json_encode($result);
			exit;
		}
		 
 		$M=$this->MODEL('comtc');
 		
 		$return = $M->refresh_job($_POST);
 		
 		if($return['status']==1){
			
			$data['msg']=$return['msg']." !";
			$data['error']=1;
			echo json_encode($data);
			exit;
		}else if($return['status']==2){
			
			$data['msg']=$return['msg']." !";
			$data['error']=2;
			echo json_encode($data);
			exit;
		}else{
			
			if($return['url']){
				$data['url'] = $return['url'];
			}
			$data['msg']=$return['msg'];
			$data['error']=3;
			echo json_encode($data);
 			exit;
		}
		$data['msg'] = $msg;
		echo json_encode($data);
		exit;
	}
    
    function ajax_refresh_part_action(){
		if(!isset($_POST['partid'])){
			exit;
		}

		$partid = $_POST['partid'];
		
		$statis = $this->company_satic();

		$msg = '';
		
		
		$companyM = $this->MODEL('company');
		$result = $companyM->comVipDayActionCheck('refreshpart',$this->uid);
		if($result['status']!=1){
		    echo json_encode($result);
		    exit();
		}
		 
 		$M=$this->MODEL('comtc');
 		
 		$return = $M->refresh_part($_POST);
 		
 		if($return['status']==1){
			
			$data['msg']=$return['msg']." !";
			$data['error']=1;
			echo json_encode($data);
			exit;
		}else if($return['status']==2){
			
			$data['msg']=$return['msg']." !";
			$data['error']=2;
			echo json_encode($data);
			exit;
		}else{
			
			if($return['url']){
				$data['url'] = $return['url'];
			}
			$data['msg']=$return['msg'];
			$data['error']=3;
			echo json_encode($data);
 			exit;
		}
		$data['msg'] = $msg;
		echo json_encode($data);
		exit;
	}

    function special_action(){
        $urlarr=array("c"=>"special","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
        
		$rows=$this->get_page("special_com","`uid`='".$this->uid."' ORDER BY `time` DESC",$pageurl,"10");
        if($rows&&is_array($rows)){
			$uid=array();
			foreach($rows as $val){
				$sid[]=$val['sid'];
			}
			$special=$this->obj->DB_select_all("special","`id` in(".pylode(',',$sid).")","id,title,intro");
			foreach($rows as $key=>$val){
				foreach($special as $v){
					if($val['sid']==$v['id']){
						$rows[$key]['title']=$v['title'];
						$rows[$key]['intro']=$v['intro'];
					}
				}
			}
		}
		$backurl=Url('wap',array('c'=>'jobcolumn'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset("header_title","专题招聘");
		$this->yunset("rows",$rows);
        $this->waptpl('special');
    }
    function delspecial_action(){
        $IntegralM=$this->MODEL('integral');
		$id=$this->obj->DB_select_once("special_com","`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."' and `status`=0","uid,integral");
		if($id&&$id['integral']>0){
			$IntegralM->company_invtal($id['uid'],$id['integral'],true,"取消专题招聘报名，退还".$this->config['integral_pricename'],true,2,'integral');
		}
		$delid=$this->obj->DB_delete_all("special_com","`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'"," ");
		if($delid){
			$this->obj->member_log("删除专题报名");
			$this->layer_msg('删除成功！',9,0,$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg('删除失败！',8,0,$_SERVER['HTTP_REFERER']);
		}
	}
	function zhaopinhui_action(){
		$urlarr=array("c"=>"zhaopinhui","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("zhaopinhui_com","`uid`='".$this->uid."' ORDER BY `ctime` DESC",$pageurl,"10");
		
		if(is_array($rows)){
			foreach($rows as $key=>$v){
				$zphid[]=$v['zid'];
				$jobids[]=$v['jobid'];
			}
			$jobids=@implode(',', $jobids);
			$jobid=array_unique(@explode(',', $jobids));
			
			$zhaopinhui=$this->obj->DB_select_all("zhaopinhui","`id` in (".pylode(',',$zphid).")","`id`,`title`,`address`,`starttime`,`endtime`");
			$job=$this->obj->DB_select_all("company_job","`id` in (".pylode(',',$jobid).")","`id`,`name`");
			$space=$this->obj->DB_select_all("zhaopinhui_space");
			$spaces=array();
			foreach($space as $val){
				$spacename[$val['id']]=$val['name'];
			}
			$jobs=array();
			foreach($rows as $k=>$v){
				foreach($zhaopinhui as $val){
					if($v['zid']==$val['id']){
						$rows[$k]['title']=$val['title'];
						$rows[$k]['address']=$val['address'];
						$rows[$k]['starttime']=$val['starttime'];
						$rows[$k]['endtime']=$val['endtime'];
					}
				}
				$rows[$k]['sidname']=$spacename[$v['sid']];
				$rows[$k]['bidname']=$spacename[$v['bid']];
				$rows[$k]['cidname']=$spacename[$v['cid']];
				
				$jobs=@explode(',', $v['jobid']);
				$jobname=array();
				if($jobs){
					foreach($job as $val){
						foreach ($jobs as $vv){
							if($vv==$val['id']){
								$jobname[]=$val['name'];
							}
						}
						$rows[$k]['jobname']=@implode(',', $jobname);
					}
				}
			}
		}
		$backurl=Url('wap',array('c'=>'jobcolumn'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset("rows",$rows);
		$this->yunset("header_title","招聘会记录");
		$this->waptpl('zhaopinhui');
	}
	function delzph_action(){
		$IntegralM=$this->MODEL('integral');
		$row=$this->obj->DB_select_once("zhaopinhui_com","`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'","`price`,`status`");
		$delid=$this->obj->DB_delete_all("zhaopinhui_com","`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'"," ");
		if($delid){
			if($row['status']==0 && $row['price']>0){
				$IntegralM->company_invtal($this->uid,$row['price'],true,"退出招聘会",true,2,'integral');
			}
			$this->obj->member_log("退出招聘会");
			$this->layer_msg('退出成功！',9,0,$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg('退出失败！',8,0,$_SERVER['HTTP_REFERER']);
		}
	}
	
	function set_action(){
		$company = $this->obj->DB_select_once("company","`uid`='".$this->uid."'");
		$this->yunset("company",$company);
		
		$cert=$this->obj->DB_select_once("company_cert","`uid`='".$this->uid."' and type='3'");
		$this->yunset("cert",$cert);
		
		$info = $this->obj->DB_select_once("member","`uid`='".$this->uid."'","`restname`");
		if($info['restname']=="0"){
			$this->yunset("setname",1);
		}
		
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"账户设置");
		$this->waptpl('set');
	}
	
	function sysnews_action(){	
		
		$userid_job=$this->obj->DB_select_once("userid_job","`com_id`='".$this->uid."' and `is_browse`='1' order by datetime desc","`job_name`,`uid`,`datetime`");
 		$resume=$this->obj->DB_select_once("resume","`uid`='".$userid_job['uid']."'","`name`");
 		$userid_job['name'] = $resume['name'];
 		$this->yunset('userid',$userid_job);
 		$userid_jobnum=$this->obj->DB_select_num("userid_job","`com_id`='".$this->uid."'and `is_browse`='1'");
 		$this->yunset('userid_jobnum',$userid_jobnum);
		
		$sxrows=$this->obj->DB_select_once("sysmsg","`fa_uid`='".$this->uid."' order by ctime desc");
		$this->yunset("sxrows",$sxrows);
		$sxnum=$this->obj->DB_select_num("sysmsg","`fa_uid`='".$this->uid."'and `remind_status`='0'");
		$this->yunset('sxnum',$sxnum);
 		
 		
	    $jobrows=$this->obj->DB_select_once("msg","`job_uid`='".$this->uid."' and `del_status`<>'1' order by datetime desc");
		$this->yunset('jobrows',$jobrows);
		
		$jobnum=$this->obj->DB_select_num("msg","`job_uid`='".$this->uid."'and `reply`=''");
		$this->yunset('jobnum',$jobnum);
		
        $this->yunset('header_title',"系统消息");
		
    	$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('sysnews');
		
		
		
	}
	
	function msg_action(){
		$urlarr=array("c"=>"msg","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$rows=$this->get_page("msg","`job_uid`='".$this->uid."' and `del_status`<>'1' order by datetime desc",$pageurl,"15");
		if(is_array($rows)&&$rows){
			foreach($rows as $key=>$val){
				$rows[$key]['content']=strip_tags(trim($val['content']));
				$uid[]=$val['uid'];
			}
			$resume=$this->obj->DB_select_all("resume_expect","`uid` in (".@implode(",",$uid).")","`id`,`uid`");
		    if(is_array($resume)){
		    	foreach($rows as $k=>$v){		    		
		    		foreach($resume as $val){	    			
		    			if($v['uid']==$val['uid']){		    				
		    				$rows[$k]['did']=$val['id'];			    					    				
		    			}
		    		}
		    	}
		    }		
		}		
		$this->obj->DB_update_all("msg","`com_remind_status`='1'","`job_uid`='".$this->uid."' and `com_remind_status`='0'");
		        
        if($_POST['submit']){
			if($_POST['reply']==""){
				$this->waptpl('msg');
			}else{
				$data['reply']=$_POST['reply'];
				$data['reply_time']=time();
				$data['user_remind_status']='0';
				$where['id']=(int)$_POST['id'];
				$where['job_uid']=$this->uid;
				$nid=$this->obj->update_once("msg",$data,$where);	 			
	 			if($nid){
	 				$this->obj->member_log("回复企业评论");
	 				$data['msg']='回复成功';
	 				$data['url']='index.php?c=msg';
	 			}else{
	 				$data['msg']='添加失败';
	 			}
 				$this->yunset("layer",$data);
 				$this->waptpl('msg');
			}
		} 
		$this->yunset("rows",$rows);
        $backurl=Url('wap',array('c'=>'sysnews'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"求职咨询");
        $this->waptpl('msg');
	}
	
	function delmsg_action(){
	    if($_GET['id']){
			$nid=$this->obj->DB_delete_all("msg","`id`='".$_GET['id']."' and `job_uid`='".$this->uid."'");
 			if($nid){
 				$this->obj->member_log("删除求职咨询");
 				$this->layer_msg('删除成功!');
 			}else{
 				$this->layer_msg('删除失败！');
 			}
		}
	}
    
    function sxnews_action(){
    	$urlarr=array("c"=>"sysnews","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$rows = $this->get_page("sysmsg","`fa_uid`='".$this->uid."' order by id desc",$pageurl,"15");
		if(is_array($rows)){
			$patten = array("\r\n", "\n", "\r");
			foreach($rows as $key=>$value){
			
				$rows[$key]['content_all'] = str_replace($patten, "<br/>", $value['content']);
			}
		}
		$this->yunset("rows",$rows);
		$backurl=Url('wap',array('c'=>'sysnews'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"系统消息");
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
		if ($_GET['id']){
            $nid=$this->obj->DB_delete_all("sysmsg","`id`='".$_GET['id']."' and `fa_uid`='".$this->uid."'");
 			if($nid){
 				$this->obj->member_log("删除系统消息");
 				$this->layer_msg('删除成功！');
 			}else{
 				$this->layer_msg('删除失败！');
 			}
		}
	}
	
	function attention_me_action(){
	    
	    
		$whereAtn = "`sc_uid` = '".$this->uid ."'";
		$users = $this->obj->DB_select_all("atn",$whereAtn);
		
				
		if(is_array($users)){
			foreach($users as $v){
				$uids[] = $v['uid'];
			}
		}
		
		
		$whereResume = "`uid` in (".pylode(',',$uids) .") ";
		
		$defineJobs = $this->obj->DB_select_all("resume",$whereResume," `uid`,`name`,`def_job`,`birthday`");
		
		
		if(is_array($defineJobs)){
			foreach($defineJobs as $v){
				$defineJobsId[] = $v['def_job'];
			}
		}
 		 
		$urlarr=array("c"=>"attention_me","page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		
		
		$whereResumeExpect = " `id` in (".pylode(',',$defineJobsId) .") ";
		
 		$resume = $this->get_page("resume_expect",$whereResumeExpect,$pageurl,"5","`id`,`job_classid`,`exp`,`edu`,`minsalary`,`maxsalary`,`uid`");
 		
		
		if(is_array($resume)){
			foreach($resume as $k => $v){
				foreach($users as $u){
					if($v['uid'] == $u["uid"]){
						$resume[$k]['time'] = $u["time"];
						break;
					}
				}
				foreach($defineJobs as $d){
					if($v['uid'] == $d['uid']){
						$resume[$k]['username'] = $d['name'];
						$resume[$k]['birthday'] = $d['birthday'];
						break;
					}
				}
			}
		}
		
		
		$userid_msg=$this->obj->DB_select_all("userid_msg","`fid`='".$this->uid."' and `uid` in (".pylode(",",$uids).")","uid");
		
		if(is_array($resume) && !empty($resume)){
			include(PLUS_PATH."user.cache.php");
			include(PLUS_PATH."job.cache.php");
			foreach($resume as $key=>$val){
				
				$resume[$key]['exp']=$userclass_name[$val['exp']];
				$resume[$key]['edu']=$userclass_name[$val['edu']];
				$resume[$key]['minsalary']=$val['minsalary'];
				$resume[$key]['maxsalary']=$val['maxsalary'];
				if($val['job_classid']!="")
				{
					$job_classid=@explode(",",$val['job_classid']);
					$resume[$key]['jobname']=$job_name[$job_classid[0]];
				}
				
				foreach($userid_msg as $va)
				{
					if($val['uid']==$va['uid'])
					{
						$resume[$key]['userid_msg']=1;
					}
				}
			}
		}
		
		$JobM=$this->MODEL("job");
		$company_job=$JobM->GetComjobList(array("uid"=>$this->uid,"state"=>1," `r_status`<>'2' and `status`<>'1'"),array("field"=>"`name`,`id`"));
		$this->yunset("company_job",$company_job);
		
		$this->yunset('rows',$resume);
		
		$age=date("Y",time());
		$time=date("Y",0);
		
		$this->yunset("age",$age);
		$this->yunset("time",$time);
	    $backurl=Url('wap',array('c'=>'resumecolumn'),'member');
	    $this->yunset('backurl',$backurl);
		$this->yunset('header_title',"关注我的人才");

	    $this->waptpl('attention_me');
	}
	
	function atnmedel_action(){
		if($_GET['id']){
			$resume = $this->obj->DB_select_once("resume_expect","`id`='".$_GET['id']."'","`uid`");
 			
			$nid=$this->obj->DB_delete_all("atn","`uid`='".$resume['uid']."' and `sc_uid`='".$this->uid."'");
			
			if($nid){
				$this->member_log("删除关注我的人才（UID:".$resume['uid']."）");
				$this->waplayer_msg('删除成功！');
			}else{
				$this->waplayer_msg('删除失败！');
			}
		}
	}

	function searchcom_action(){
		$company=$this->obj->DB_select_all("company","`name` like '%".$this->stringfilter(trim($_POST['name']))."%' ","`uid`,`name`");
		
		if($company&&is_array($company)){
			$html="";
			foreach($company as $val){
				$html.="<div class='mui-input-row mui-radio mui-left'>
						<label>".$val['name']."</label>
						<input name='cuid' type='radio' value='".$val['uid']."'>
					</div>";
				
			}
		}else{
			$html=1;
		}
		echo $html;die;
	}

	function finance_action(){	    
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'");
		$this->yunset("statis",$statis);
		$this->yunset('header_title',"财务管理");
		$this->waptpl('finance');
	}
	function integral_action(){
		$baseInfo			= false;	
		$logo				= false;	
		$signin			    = false;	
		$emailChecked		= false;	
		$phoneChecked		= false;	
		$pay_remark         =false;
		$question        	=false;		
		$answer       		=false;		
		$answerpl           =false;		
		
		$map				= false;	
		$banner				= false;	
		$yyzz				= false;	
		
		$row = $this->obj->DB_select_once("company",'`uid` = '.$this->uid,
			"`name`,`hy`,
			`logo`,`email_status`,`moblie_status`,
			`x`,`y`,
			`firmpic`,
			`yyzz_status`");
		$ban= $this->obj->DB_select_once("banner","`uid`='".$this->uid."'","`pic`");
		$row['firmpic']=$ban['pic'];
		if(is_array($row) && !empty($row)){
			if($row['name'] != '' && $row['hy'] != '' )
				$baseInfo = true;
			
			if($row['logo'] != '') $logo = true;
			if($row['email_status'] != 0) $emailChecked = true;
			if($row['moblie_status'] != 0) $phoneChecked = true;
			if($row['x'] != 0 && $row['y'] != 0) $map = true;
			if($row['firmpic'] != '') $banner = true;
			if($row['yyzz_status'] != 0) $yyzz = true;
			
		}
		$date=date("Ymd");
		$reg=$this->obj->DB_select_once("member_reg","`uid`='".$this->uid."' and `usertype`='".$this->usertype."' and `date`='".$date."'");
		if($reg['id']){
		    $signin = true;
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
			'logo'			=>$logo,
		    'signin'		=>$signin,
			'emailChecked'	=>$emailChecked,
			'phoneChecked'	=>$phoneChecked,
			'question'	    =>$question,
			'answer'	    =>$answer,
			'answerpl'	    =>$answerpl,
			'map'			=> $map,	
			'banner'		=> $banner,	
			'yyzz'			=> $yyzz	
		);
		$this->yunset("statusList",$statusList);
		$statis=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'","`integral`");
		$this->yunset("statis",$statis);
		if($_GET['type']){
			$backurl=Url('wap',array('c'=>'finance'),'member');
		}else{
			$backurl=Url('wap',array(),'member');
		}
		
		$reg_url = Url('wap',array('c'=>'register','uid'=>$this->uid));
		$this->yunset('reg_url', $reg_url);
		
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"积分管理");
		$this->waptpl('integral');
	}
	
	function resumecolumn_action(){
		
		
		$sqnum=$this->obj->DB_select_num("userid_job","`com_id`='".$this->uid."'");
		$this->yunset('sqnum',$sqnum);
		
		$userid_jobnum=$this->obj->DB_select_num("userid_job","`com_id`='".$this->uid."'and `is_browse`='1'");
 		$this->yunset('userid_jobnum',$userid_jobnum);
 		
		
		
		$userid_msgnum=$this->obj->DB_select_num("userid_msg","`fid`='".$this->uid."'");
 		$this->yunset("invitenum",$userid_msgnum);
		
		
	    $looknum=$this->obj->DB_select_num("look_resume","`com_id`='".$this->uid."'and `com_status`='0'");
	    $this->yunset("looknum",$looknum);
	    
	    
	    $talentnum=$this->obj->DB_select_num("talent_pool","`cuid`='".$this->uid."'");
	    $this->yunset("talentnum",$talentnum);
	    
	    
	    $downnum=$this->obj->DB_select_num("down_resume","`comid`='".$this->uid."'");
	    $this->yunset("downnum",$downnum);
	    
	    
	    $atnnum=$this->obj->DB_select_num("atn","`sc_uid`='".$this->uid."'");
 	    $this->yunset("atnnum",$atnnum);
	    
	    
	    $lookjobnum=$this->obj->DB_select_num("look_job","`com_id`='".$this->uid."' and `com_status`='0'");
	    $this->yunset("lookjobnum",$lookjobnum);
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"简历管理");

		$this->waptpl('resumecolumn');
	}
	
    function jobcolumn_action(){
		$member=$this->obj->DB_select_once("member","`uid`='".$this->uid."'");
		$this->yunset("member",$member);
		$this->rightinfo();
		$backurl=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
 		$this->yunset("header_title","职位管理");
		$this->get_user();
		$this->company_satic();
		$this->waptpl('jobcolumn');
	}
	
	function integral_reduce_action(){
		$backurl=Url('wap',array('c'=>'integral'),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->yunset('header_title',"消费规则");
		$this->waptpl('integral_reduce');
	}
	
	
	function banner_action(){
		
		if($_POST['submit']){
			if($_POST['preview']){
				
				$UploadM =$this->MODEL('upload');
				$upload  =$UploadM->Upload_pic(APP_PATH."/data/upload/company/",false);
				
				$pic     =$upload->imageBase($_POST['preview']);
				
				$picmsg  = $UploadM->picmsg($pic,$_SERVER['HTTP_REFERER']);
				if($picmsg['status']==$pic){
					$data['msg']=$picmsg['msg'];
 				}else{
					
					$photo=str_replace(APP_PATH."/data/upload/company/","./data/upload/company/",$pic);
					$datap['uid']=$this->uid;
					$datap['pic']=$photo;
				}

			}
			
			if($data['msg']==""){
				$row=$this->obj->DB_select_once("banner","`uid`='".$this->uid."'");
				
				if($row['id']){
					if($row['pic']){
						unlink_pic(APP_PATH.$row['pic']);
					}
					$nid=$this->obj->update_once("banner",$datap,array('id'=>$row['id']));
				}else{
					$nid=$this->obj->insert_into("banner",$datap);
				}
				
				if($nid){
					$this->obj->member_log("上传企业横幅");
							
					if(!$row['id']){
						$IntegralM=$this->MODEL('integral');
						$IntegralM->get_integral_action($this->uid,"integral_banner","上传企业横幅");
					}
							
					$data['msg']="设置成功！";
					$data['url']='index.php?c=integral';
				}else{
					$data['msg']="设置失败！";
					$data['url']='index.php?c=banner';
				}
				
			}else{
				
				$data['msg']=$data['msg'];
				$data['url']='index.php?c=banner';
				
			}
			
		}
		
		$banner=$this->obj->DB_select_once("banner","`uid`='".$this->uid."'");
		
		if($banner['pic'] && file_exists(str_replace('./',APP_PATH,$banner['pic']))){
			$banner['pic']=str_replace('./',$this->config['sy_weburl'].'/',$banner['pic']);
		}else{
			$banner['pic']='';
		}
		
		$this->yunset("banner",$banner);
		$this->yunset("layer",$data);
		$backurl=Url('wap',array('c'=>'integral'),'member');
		$this->yunset("backurl",$backurl);
		$this->yunset('header_title',"企业横幅");
		$this->waptpl('banner');
	}
	
	function show_action(){
		$urlarr['c']="show";
		$urlarr["page"]="{{page}}";
		$pageurl=Url('wap',$urlarr,'member');
		$rows = $this->get_page("company_show","`uid`='".$this->uid."' order by sort desc",$pageurl,"12","`title`,`id`,`picurl`");
		
		if($rows&&is_array($rows)){
			foreach($rows as $k=>$v){
				$rows[$k]['picurl']=str_replace('./','/',$v['picurl']);
			}
		}
		$this->yunset("rows",$rows);
		$company=$this->obj->DB_select_once("company","`uid`='".$this->uid."'","`name`");
		$this->yunset("company",$company);
		
		$this->yunset("js_def",2);
		$backurl=Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"企业环境");
		$this->waptpl('show');
	}
	
	function del_action(){
		if($_POST['id']==""){
			$data=3;
		}else{
			$row=$this->obj->DB_select_once("company_show","`id`='".(int)$_POST['id']."' and `uid`='".$this->uid."'","`picurl`");
			if(is_array($row)){
				unlink_pic(".".$row['picurl']);
				$oid=$this->obj->DB_delete_all("company_show","`id`='".(int)$_POST['id']."' and `uid`='".$this->uid."'");
			}
			if($oid){
				$this->obj->member_log("删除企业环境展示");
				$data=1;
			}else{
				$data=2;
			}
		}
		echo json_encode($data);die;
	}
	
	function addshow_action(){
		if($_POST['submit']){
			
			if($_POST['preview']){
				
				$UploadM =$this->MODEL('upload');
				$upload  =$UploadM->Upload_pic(APP_PATH."/data/upload/show/",false);
				
				$pic     =$upload->imageBase($_POST['preview']);
				
				$picmsg  = $UploadM->picmsg($pic,$_SERVER['HTTP_REFERER']);
				if($picmsg['status']==$pic){
					$data['msg']=$picmsg['msg'];
 				}else{
 					$photo=str_replace(APP_PATH."/data/upload/show/","./data/upload/show/",$pic);
 					
 					$picurl=$photo;
				}
			}
			
			if($data['msg']==""){
				$datashow=array(
					'title'=>$_POST['title'],
					'uid'=>$this->uid,
					'ctime'=>time()
				);
				$companyM = $this->MODEL('company');
				if($_POST['id']){
					$row=$this->obj->DB_select_once("company_show","`id`='".$_GET['id']."' and `uid`='".$this->uid."'");
					if(!$picurl){
						$datashow['picurl']=$row['picurl'];
					}elseif($picurl!=$row['picurl']){
						if($row['picurl']){
							unlink_pic(APP_PATH.$row['picurl']);
						}
						$datashow['picurl']=$picurl;
					}
					$nid = $companyM->UpdateShow($datashow,array('id'=>intval($_POST['id']),'uid'=>$this->uid));
					if($nid){
						$data['msg']='更新成功！';
						$data['url']='index.php?c=show';
					}else{
						$data['msg']='更新失败！';
						$data['url']='index.php?c=show';
					}
				}else{
					if(!$picurl){
						$data['msg']='请上传企业环境！';
					}else{
						$datashow['picurl']=$picurl;
						$id = $companyM->AddCompanyShow($datashow);
						if($id){
							$data['msg']='上传成功！';
							$data['url']='index.php?c=show';
						}else{
							$data['msg']='上传失败！';
							$data['url']='index.php?c=show';
						}
					}
				}
			}else{
				$data['msg']=$data['msg'];
				$data['url']='index.php?c=show';
			}
		}else{
			if($_GET['id']){
				$row=$this->obj->DB_select_once("company_show","`id`='".$_GET['id']."' and `uid`='".$this->uid."'");
				$row['picurl']=str_replace('./','/',$row['picurl']);
				$this->yunset("row",$row);
			}
		}
		$company=$this->obj->DB_select_once("company","`uid`='".$this->uid."'","`name`");
		$this->yunset("company",$company);
		
		$this->yunset("layer",$data);
		$backurl=Url('wap',array('c'=>'show'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"企业环境");
		$this->get_user();
		$this->waptpl('addshow');
	}
	
}
	
?>