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
class company extends common{
	
	function public_action(){
		$now_url=@explode("/",$_SERVER['REQUEST_URI']);
		$now_url=$now_url[count($now_url)-1];
		$this->yunset("now_url",$now_url);
		$company=$this->obj->DB_select_once("company","`uid`='".$this->uid."'");
		if($company['logo']){
			$company['logo']=str_replace("./",$this->config['sy_weburl']."/",$company['logo']);
		}else{
			$company['logo']=$this->config['sy_weburl'].'/'.$this->config['sy_unit_icon'];
		}
		$atn=$this->obj->DB_select_once("atn","`uid`='".$this->uid."'");
		$guweninfo=$this->obj->DB_select_once("company_consultant","`id` ='".$company['conid']."'");
		if($guweninfo['logo']&&file_exists(str_replace('./',APP_PATH,$guweninfo['logo']))){
		    $guweninfo['logo']=str_replace("./",$this->config['sy_weburl']."/",$guweninfo['logo']);
		}else{
		    $guweninfo['logo']=$this->config['sy_weburl'].'/'.$this->config['sy_guwen'];
		}
		$qq=explode(',', $this->config['sy_qq']);
		$this->yunset('kfqq',$qq[0]);
		
		include(PLUS_PATH."menu.cache.php");
		$report=$this->obj->DB_select_once("report","p_uid='".$this->uid."' order by inputtime desc");
		$this->yunset("report",$report);
		$this->yunset("company",$company);
		$this->yunset("atn",$atn);
		$this->yunset("guweninfo",$guweninfo);
		
		$rating_1=$this->obj->DB_select_all("company_rating","`category`='1' and `service_price` > 0 and `display`='1' and `type`='1' order by `type` asc,`sort` desc");
		$this->yunset("rating_1",$rating_1);
		
		$rating_2=$this->obj->DB_select_all("company_rating","`category`='1' and `service_price` > 0 and `display`='1' and `type`='2' order by `type` asc,`sort` desc");
		
		$this->yunset("rating_2",$rating_2);
		
		$added=$this->obj->DB_select_all("company_service","`display`='1' order by sort desc");
		
		if(is_array($added)){
			foreach($added as $val){
				$types[] = $val['id'];
			}
			$service_detail = $this->obj->DB_select_all("company_service_detail"," `type` in(".pylode(',',$types).") order by sort desc");
 			
			foreach($added as $key=>$val){
				foreach($service_detail as $v){
					if($val['id'] == $v['type']){
						$added[$key]['service_price'][]= $v['service_price'];
						$added[$key]['resume'][]= $v['resume'];
						$added[$key]['interview'][]= $v['interview'];
						$added[$key]['job_num'][]= $v['job_num'];
						$added[$key]['breakjob_num'][]= $v['breakjob_num'];
						$added[$key]['part_num'][]= $v['part_num'];
						$added[$key]['breakpart_num'][]= $v['breakpart_num'];
						
 					} 
				}
			}
 		}
		
		
		$this->yunset("added",$added);

		
		$newResumeNum = $this->obj->DB_select_num('userid_job', "`com_id`='{$this->uid}' and is_browse=1");
		$this->yunset('newResumeNum', $newResumeNum);
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
			if($nums==0){
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
				$addjobnum=$addpartjobnum='1';
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
				$addjobnum=$addpartjobnum='0';
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

	
	function get_com($type,$statis=array()){
		if($statis['uid']==''){
			$statis=$this->company_satic();
		}

		if($statis['rating_type']=="" && $statis['rating']){
			
			$rating=$this->obj->DB_select_once("company_rating","`id`='".$statis['rating']."'");
			$this->obj->DB_update_all("company_statis","`rating_type`='".$rating['type']."'","`uid`='".$this->uid."'");
			$statis['rating_type']=$rating['type'];
			
		}
		if($statis['rating_type'] && $statis['rating']){
			if($type==1){
				if($statis['rating_type']=='1' && $statis['job_num']>0 && ($statis['vip_etime']<1 || $statis['vip_etime']>=time())){
					$value="`job_num`=`job_num`-1";
				}elseif($statis['rating_type']=='2' && ($statis['vip_etime']>time() || $statis['vip_etime']=='0')){
					$value="";
				}else{
					$this->ACT_layer_msg("你的套餐已用完！",8,"index.php?c=right");
				}
			}elseif($type==4){
				if($statis['rating_type']=='2' && ($statis['vip_etime']>time() || $statis['vip_etime']=='0')){
					$value="";
				}else{
					$this->ACT_layer_msg("你的套餐已用完！",8,"index.php?c=right");
				}
			}elseif($type==7){
				if($statis['rating_type']=='1' && $statis['part_num']>0 && ($statis['vip_etime']<1 || $statis['vip_etime']>=time())){
					$value="`part_num`='".($statis['part_num']-1)."'";
				}else if($statis['rating_type']=='2' && ($statis['vip_etime']>time() || $statis['vip_etime']=='0')){
					$value="";
				}else{
					$this->ACT_layer_msg("你的套餐已用完！",8,"index.php?c=right");
				}
			} 
			if($value){
				$this->obj->DB_update_all("company_statis",$value,"`uid`='".$this->uid."'");
			}
		}else{
			$this->ACT_layer_msg("你的会员已经到期！请先购买会员！",8,"index.php?c=right");
		}
	}
	
	function com_tpl($tpl){
		$this->yuntpl(array('member/com/'.$tpl));
	}
	
	function get_user(){
		$rows=$this->obj->DB_select_once("company","`uid`='".$this->uid."'");
		if(!$rows['name'] || !$rows['address'] || !$rows['pr']){
			$this->ACT_msg("index.php?c=info","请先完善企业资料！");
		}
		return $rows;
	}
	
	function job(){
		
		if($_GET['r_uid']){
			if($_GET['r_reason']==""){
				$this->ACT_layer_msg("举报内容不能为空！",8,"index.php?c=down");
			}
			$data['p_uid']=(int)$_GET['r_uid'];
			$data['inputtime']=time();
			$data['c_uid']=$this->uid;
			$data['did']=$this->userid;
			$data['eid']=(int)$_GET['eid'];
			$data['r_name']=$_GET['r_name'];
			$data['usertype']=(int)$this->usertype;
			$data['username']=$this->username;
			$data['r_reason']=$_GET['r_reason'];
			$haves=$this->obj->DB_select_once("report","`p_uid`=".$data['p_uid']." and `c_uid`=".$data['c_uid']." and `usertype`=".$data['usertype']."","id");
			if(is_array($haves)){
				$this->ACT_layer_msg("您已经举报过该用户！",8,"index.php?c=down");
			}else{
				$nid=$this->obj->insert_into("report",$data);
				if($nid){
					$this->obj->member_log("举报人才《".$_GET['r_name']."》");
					$this->ACT_layer_msg("操作成功！",9,"index.php?c=down");
				}else{
					$this->ACT_layer_msg("操作失败！",8,"index.php?c=down");
				}
			}
		}
     
		
		
		if($_POST['status'] && ($_POST['id']|| is_array($_POST['id']))){
			if(is_array($_POST['id'])){
				$id=pylode(",",$_POST['id']);
			}else if($_POST['id']){
				$id=(int)$_POST['id'];
			}
			$where="`uid`='".$this->uid."' and `id` in (".$id.")";

			

			if($_POST['status']==2){
				$_POST['status']=0;
			}
			$nid=$this->obj->update_once("company_job",array("status"=>(int)$_POST['status']),$where);
			if($nid){
			
				$this->obj->member_log("修改职位发布状态");
				echo 1;die;
			}else{
				echo 2;die;
			}
		}
		
		if($_GET['del'] || is_array($_POST['checkboxid'])){
			if(is_array($_POST['checkboxid'])){
				$layer_type=1;
				$delid=pylode(",",$_POST['checkboxid']);
			}else if($_GET['del']){
				$layer_type=0;
				$delid=(int)$_GET['del'];
			}
			
			
				$nid=$this->obj->DB_delete_all("company_job","`uid`='".$this->uid."' and `id` in (".$delid.")"," ");
				$this->obj->DB_delete_all("company_job_link","`uid`='".$this->uid."' and `jobid` in (".$delid.")"," ");
				
				if($nid){

					$newest=$this->obj->DB_select_once("company_job","`uid`='".$this->uid."' order by lastupdate DESC","`lastupdate`");

					$this->obj->DB_delete_all("userid_job","`com_id`='".$this->uid."' and `job_id` in (".$delid.")"," ");
					$this->obj->DB_delete_all("look_job","`com_id`='".$this->uid."' and `jobid` in (".$delid.")"," ");
					$this->obj->DB_delete_all("fav_job","`job_id` in (".$delid.")"," ");
					$this->obj->DB_delete_all("user_entrust_record","`jobid` in (".$delid.") and `comid`='".$this->uid."'","");
					$this->obj->DB_delete_all("report","`usertype`=1 and `type`=0 and `eid` in (".$delid.")","");
					
					$this->obj->update_once("company",array("jobtime"=>$newest['lastupdate']),array("uid"=>$this->uid));
					$this->obj->member_log("删除职位",1,3);
					$this->layer_msg('删除成功！',9,$layer_type,$_SERVER['HTTP_REFERER']);
				}else{
					$this->layer_msg('删除失败！',8,$layer_type,$_SERVER['HTTP_REFERER']);
				}
		}
	}
	function wnameup($namekey,$wname,$type){
		$wanmeinfo = $this->obj->DB_select_all("company_statis","`$namekey`='$wname' AND `uid`<>'".$this->uid."'");
		if(is_array($wanmeinfo)&&!empty($wanmeinfo))
		{
			$this->ACT_layer_msg("该帐号已经被绑定，如有人恶意绑定请想管理员申诉！",8,"index.php?c=Web&type=".$type);
		}else{
			$this->obj->update_once("company_statis",array($namekey=>$wname),array("uid"=>$this->uid));
		}
	}
	
	function logout_action(){
		$this->logout();
	}
	
	function HandleError($message){
		echo $message;
	}
	
	function CreateFirstName($file_extension ){
		$num=date('mdHis').rand(1,100);
		$fileName=$num.".".$file_extension;
		return $fileName;
	}
	
	function CreateNextName($file_extension,$file_dir){
		$fileName_arr = scandir($file_dir,1);
		$fileName=$fileName_arr[0];
		$aa=floatval($fileName);
		$num=0;
		$num=(1+$aa);
		if(empty($aa)){
			$num = date('mdHis').rand(1,100);
		}
		return $num.".".$file_extension;
	}
	
	function createdatefilename($file_extension){
		date_default_timezone_set('PRC');
		return date('mdHis').rand(1,100).".".$file_extension;
	}
	
	function create_folders($dir){
    return is_dir($dir) or ($this->create_folders(dirname($dir)) and mkdir($dir, 0777));
  }
  
	
	function part(){
		 
		if($_POST['gotimeid']){
			$_POST['day']=intval($_POST['day']);
			if($_POST['day']<1){
 				$this->ACT_layer_msg("请正确填写延期天数！",8);
			}else{
				$posttime=(int)$_POST['day']*86400;
				$ids=@explode(",",$_POST['gotimeid']);
				if(is_array($ids)){
					foreach($ids as $value){
						$where=array();$data=array();
						$row=$this->obj->DB_select_once("partjob","`id`='".(int)$value."' and `uid`='".$this->uid."'","`state`,`deadline`,`edate`");
						if ($row['deadline']){
							$time=$row['deadline']+$posttime;
						}else{
							$time='';							
						}
						if ($row['edate']){
						    $edate=$row['edate']+$posttime;
						}else{
						    $edate='';
						}
						$where['id']=(int)$value;
						$where['uid']=$this->uid;
						if($row['state']==2 && $time>time()){
							$data['deadline']=$time;
							$data['state']=1;
							$data['edate']=$edate;
							$id=$this->obj->update_once("partjob",$data,$where);
						}else{
							$id=$this->obj->update_once("partjob",array("deadline"=>$time,'edate'=>$edate),$where);
						}
					}
				}
				if($id)
				{
					$this->obj->member_log("兼职职位延期");
					$this->ACT_layer_msg("兼职延期成功！",9,$_SERVER['HTTP_REFERER']);
				}else{
					$this->ACT_layer_msg("兼职延期失败！",8,$_SERVER['HTTP_REFERER']);
				}
			}
		}
		  
		
		if($_POST['recid']){
			$IntegralM=$this->MODEL('integral');
			$id=(int)$_POST['recid'];
			$_POST['day']=intval($_POST['day']);
			if($_POST['day']<1){
				$this->ACT_layer_msg("请正确填写推荐天数！",8,$_SERVER['HTTP_REFERER']);
			}
			$reow=$this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'","integral");
			$part=$this->obj->DB_select_once("partjob","`id`='".$id."' and `uid`='".$this->uid."'","name,rec_time");
			if($part['rec_time']<time())
			{
				$time=time()+$_POST['day']*86400;
			}else{
				$time=$part['rec_time']+$_POST['day']*86400;
			}
			$integral=$this->config['com_recpartjob']*$_POST['day'];
			if($reow['integral']<$integral && $this->config['com_recpartjob_type']=="2")
			{
				$this->ACT_layer_msg("您的".$this->config['integral_pricename']."不足，请充值！",8,"index.php?c=pay");
			}else{
				if($this->config['com_recpartjob_type']=="1")
				{
					$auto=true;
				}else{
					$auto=false;
				}
				$IntegralM->company_invtal($this->uid,$integral,$auto,"推荐兼职职位",true,2,'integral',12);
			}
			$data['rec']=1;
			$data['rec_time']=$time;
			$where['id']=$id;
			$where['uid']=$this->uid;
			$this->obj->update_once("partjob",$data,$where);
			$this->obj->member_log("发布推荐兼职职位《".$part['name']."》",1,1);
 			$this->ACT_layer_msg("推荐兼职成功！",9,$_SERVER['HTTP_REFERER']);
		}
	}
}
?>