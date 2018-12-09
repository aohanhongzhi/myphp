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
class company_order_controller extends adminCommon{
	function set_search(){
		$typedd = array(1 => '会员充值（购买会员）',2 => '积分充值',3 => '银行转帐',4 => '金额充值',5 => '购买增值包',10 => '职位置顶',11 => '职位紧急',12 => '职位推荐',13 => '自动刷新',14 => '简历置顶',16 => '刷新职位',17 => '刷新兼职',19 => '下载简历',20 => '发布职位',21 => '发布兼职',23 => '面试邀请',24 => '兼职推荐',25 => '店铺招聘');
		$search_list[]=array("param"=>"typezf","name"=>'支付类型',"value"=>array("alipay"=>"支付宝","wxpay"=>"微信支付","tenpay"=>"财富通","bank"=>"银行转帐"));
		$search_list[]=array("param"=>"typedd","name"=>'订单类型',"value"=>$typedd);
		$search_list[]=array("param"=>"order_state","name"=>'订单状态',"value"=>array("0"=>"支付失败","1"=>"等待付款","2"=>"支付成功","3"=>"等待确认"));
		$lo_time=array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
		$search_list[]=array("param"=>"time","name"=>'充值时间',"value"=>$lo_time);
		$this->yunset("search_list",$search_list);
	}
	function index_action(){
		
		$this->set_search();
		$where="1 ";
		if($_GET['typezf']){
			$where .=" and `order_type`='".$_GET['typezf']."'";
			$urlarr['typezf']=$_GET['typezf'];
		}
		if($_GET['typedd']){
			$where .=" and `type`='".$_GET['typedd']."'";
			$urlarr['typedd']=$_GET['typedd'];
		}
		if($_GET['news_search']){
			if ($_GET['keyword']!=""){
				if ($_GET['typeca']=='1'){
					$where .=" and `order_id` like '%".trim($_GET['keyword'])."%'";				   
			     }elseif($_GET['typeca']=='2'){
				    $orderinfo=$this->obj->DB_select_all("member","`username` like '%".trim($_GET['keyword'])."%'","`uid`");
					if (is_array($orderinfo))
					{
						foreach ($orderinfo as $val)
						{
							$orderuids[]=$val['uid'];
						}
						$oruids=@implode(",",$orderuids);
					}
					$where.=" and `uid` in (".$oruids.")";
			     }
			     $urlarr['typeca']=$_GET['typeca'];
			     $urlarr['keyword']=$_GET['keyword'];				 
			}
			 $urlarr['news_search']=$_GET['news_search'];
		}
		if($_GET['time']){
			if($_GET['time']=='1'){
				$where.=" and `order_time` >='".strtotime(date("Y-m-d 00:00:00"))."'";
			}else{
				$where .=" and `order_time`>'".strtotime('-'.intval($_GET['time']).' day')."'";
			}
			$urlarr['time']=$_GET['time'];
		}
		if($_GET['time_start1']){
			$where .=" and `order_time`>='".strtotime($_GET['time_start1']." 00:00:00")."'";
			$urlarr['time_start1']=$_GET['time_start1'];
		}
		if($_GET['time_end1']){
			$where .=" and `order_time`<='".strtotime($_GET['time_end1']." 23:59:59")."'";
			$urlarr['time_end1']=$_GET['time_end1'];
		}
		if($_GET['order_state']!=""){
            $where.=" and `order_state`='".$_GET['order_state']."'";
			$urlarr['order_state']=$_GET['order_state'];
	    }
		if($_GET['order']){
			$where.=" order by ".$_GET['t']." ".$_GET['order'];
			$urlarr['order']=$_GET['order'];
			$urlarr['t']=$_GET['t'];
		}else{
			$where.=" order by id desc";
		}
		$urlarr['page']="{{page}}";
		$pageurl=Url($_GET['m'],$urlarr,'admin');
		$rows=$this->get_page("company_order",$where,$pageurl,$this->config['sy_listnum']);
		include (APP_PATH."/config/db.data.php");
		if(is_array($rows)){
			foreach($rows as $k=>$v){
				$rows[$k]['order_state_n']=$arr_data['paystate'][$v['order_state']];
				$rows[$k]['order_type_n']=$arr_data['pay'][$v['order_type']];
				$classid[]=$v['uid'];
				if($v['once_id']){
					$onceid[]=$v['once_id'];
				}
			}
			$group=$this->obj->DB_select_all("member","uid in (".@implode(",",$classid).")","`uid`,`username`");
			$company=$this->obj->DB_select_all("company","`uid` in (".@implode(",",$classid).")","`uid`,`name`");
			$resume=$this->obj->DB_select_all("resume","`uid` in (".@implode(",",$classid).")","`uid`,`name`");
			$once=$this->obj->DB_select_all("once_job","`id` in (".@implode(",",$onceid).")","`id`,`companyname`,`linkman`");
			foreach($rows as $k=>$v){
				foreach($company as $val){
					if($v['uid']==$val['uid']){
						$rows[$k]['comname']=$val['name'];
					}
				}
				foreach($once as $val){
					if($v['once_id']==$val['id']){
						$rows[$k]['comname']=$val['companyname'];
						$rows[$k]['username']=$val['linkman'];
					}
				}
				foreach($group as $val){
					if($v['uid']==$val['uid']){
						$rows[$k]['username']=$val['username'];
					}				
				}
			
				foreach($resume as $val){
					if($v['uid']==$val['uid']){
						$rows[$k]['comname']=$val['name'];
					}
				}
			}
		}
        $this->yunset("get_type", $_GET);
        $this->yunset("where",$where);
		$this->yunset("rows",$rows);
		$this->yuntpl(array('admin/admin_company_order'));
	}
	function edit_action(){
		$id=(int)$_GET['id'];
		$row=$this->obj->DB_select_once("company_order","`id`='".$id."'");	
		$UserInfoM = $this->MODEL('userinfo');
		if(is_array($row)){	
			if($row['uid']){
				$member=$UserInfoM->GetMemberOne(array("uid"=>$row['uid']),array("field"=>"uid,username,usertype"));
				$row['username']=$member['username']; 
			}elseif($row['once_id']){
				$once=$this->obj->DB_select_once("once_job","`id`='".$row['once_id']."'","`id`,`companyname`,`linkman`");
				$row['username']=$once['linkman'];
				$row['comname']=$once['companyname']; 
			}
			if($member['usertype']=='1'){
				$resume=$this->obj->DB_select_once("resume","`uid`='".$member['uid']."'","`uid`,`name`");
				$row['comname']=$resume['name'];
			}else if($member['usertype']=='2'){
				$company=$this->obj->DB_select_once("company","`uid`='".$member['uid']."'","`uid`,`name`");
				$row['comname']=$company['name'];
			}
			$orderbank=@explode("@%",$row['order_bank']);
			if(is_array($orderbank)){
				foreach($orderbank as $key){
					$orderbank[]=$key;
				}
				$row['bankname']=$orderbank[0];
				$row['bankid']=$orderbank[1];
			} 
		}
		if($row['order_pic']&&file_exists(str_replace('./', APP_PATH, $row['order_pic']))){
			$row['order_pic']=str_replace('./', $this->config['sy_weburl'].'/', $row['order_pic']);
		}else{
			$row['order_pic']='';
		}
		$this->yunset("row",$row);
		$this->yuntpl(array('admin/admin_company_order_edit'));
	}
	function save_action(){
		$r_id=$this->obj->DB_update_all("company_order","`order_price`='".$_POST['order_price']."',`order_remark`='".$_POST['order_remark']."'","id='".$_POST['id']."'");
		isset($r_id)?$this->ACT_layer_msg("充值记录(ID:".$_POST['id'].")修改成功！",9,"index.php?m=company_order",2,1):$this->ACT_layer_msg("修改失败,请销后再试！",8,"index.php?m=company_order");
	}
	function setpay_action(){
		$del=(int)$_GET['id'];
		$this->check_token();
		$row=$this->obj->DB_select_once("company_order","`id`='$del'");
		if($row['order_state']=='1'||$row['order_state']=='3'){
			$nid=$this->MODEL('qrorder')->upuser_statis($row);

  			isset($nid)?$this->layer_msg("充值记录(ID:".$del.")确认成功！",9,0,$_SERVER['HTTP_REFERER']):$this->layer_msg("确认失败,请稍后再试！",8,0,$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg("订单已确认，请勿重复操作！",8,0,$_SERVER['HTTP_REFERER']);
		}
	}
	function xls_action(){
		if($_POST['where']){
			if($_POST['uid']){
				$uid = "id in (".$_POST['uid'].") and ";
			}
			$select=@implode(",",$_POST['uid']);
			if($_POST['time_start']){
				$time = " order_time >= ".substr($_POST['time_start'],0,strlen($_POST['time_start'])-3)." and ";
			}
			if($_POST['time_end']){
				$time .= " order_time < ".substr($_POST['time_end'],0,strlen($_POST['time_end'])-3)." and ";
			}
			$rows=$this->obj->DB_select_all("company_order",$uid.$time.str_replace("\\","",$_POST['where']));
			if(!empty($rows)){
				include (APP_PATH."/config/db.data.php");
				if(is_array($rows)){
					foreach($rows as $k=>$v){
						$rows[$k]['order_state_n']=$arr_data['paystate'][$v['order_state']];
						$rows[$k]['order_type_n']=$arr_data['pay'][$v['order_type']];
						$classid[]=$v['uid'];
						if($v['once_id']){
							$onceid[]=$v['once_id'];
						}
					}
					$group=$this->obj->DB_select_all("member","uid in (".@implode(",",$classid).")","`uid`,`username`");
					$company=$this->obj->DB_select_all("company","`uid` in (".@implode(",",$classid).")","`uid`,`name`");
					$resume=$this->obj->DB_select_all("resume","`uid` in (".@implode(",",$classid).")","`uid`,`name`");
					$once=$this->obj->DB_select_all("once_job","`id` in (".@implode(",",$onceid).")","`id`,`companyname`,`linkman`");
					foreach($rows as $k=>$v){
						foreach($company as $val){
							if($v['uid']==$val['uid']){
								$rows[$k]['comname']=$val['name'];
							}
						}				
						foreach($group as $val){
							if($v['uid']==$val['uid']){
								$rows[$k]['username']=$val['username'];
							}					
						}
						
						foreach($resume as $val){
							if($v['uid']==$val['uid']){
								$rows[$k]['comname']=$val['name'];
							}
						}
						foreach($once as $val){
							if($v['once_id']==$val['id']){
								$rows[$k]['comname']=$val['companyname'];
								$rows[$k]['username']=$val['linkman'];
							}
						}
					}
				}
				$this->yunset("list",$rows);
				$this->MODEL('log')->admin_log("导出支付订单信息");
				header("Content-Type: application/vnd.ms-excel");
				header("Content-Disposition: attachment; filename=order.xls");
				$this->yuntpl(array('admin/admin_order_xls'));
			}else{
				$this->ACT_layer_msg("没有可以导出的订单信息！",8,$_SERVER['HTTP_REFERER']);
			}
		}
	}
	
	function del_action(){
		$this->check_token();
	    if($_GET['del']){
	    	$del=$_GET['del'];
	    	if(is_array($del)){
				$company_order=$this->obj->DB_select_all("company_order","`id` in(".@implode(',',$del).")","`order_id`");
				if($company_order&&is_array($company_order)){
					foreach($company_order as $val){
						$order_ids[]=$val['order_id'];
					}
					$this->obj->DB_delete_all("company_order","`id` in(".@implode(',',$del).")","");
				}
				$this->layer_msg( "充值记录(ID:".@implode(',',$del).")删除成功！",9,1,$_SERVER['HTTP_REFERER']);
	    	}else{
				$this->layer_msg( "请选择您要删除的信息！",8,1,$_SERVER['HTTP_REFERER']);
	    	}
	    }
	    if(isset($_GET['id'])){
			$company_order=$this->obj->DB_select_once("company_order","`id`='".$_GET['id']."'" ,"`order_id`");
			$result=$this->obj->DB_delete_all("company_order","`id`='".$_GET['id']."'" );
			isset($result)?$this->layer_msg('充值记录(ID:'.$_GET['id'].')删除成功！',9,0,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,0,$_SERVER['HTTP_REFERER']);
		}else{
			$this->ACT_layer_msg("非法操作！",8,$_SERVER['HTTP_REFERER']);
		}
	}
}
?>