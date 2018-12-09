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
class admin_siteadmin_controller extends adminCommon{
	

	function index_action(){

		
		$where="`did`>'0' ";
		
		
		$urlarr['page']="{{page}}";
		$pageurl=Url($_GET['m'],$urlarr,'admin');
        $M=$this->MODEL();
		$PageInfo=$M->get_page("admin_user",$where." order by uid desc",$pageurl,$this->config['sy_listnum']);
        $this->yunset($PageInfo);
        $rows=$PageInfo['rows'];		
		if(is_array($rows)){
			$adminusergroup=$this->obj->DB_select_all("admin_user_group","`did`='0' order by id desc","`id`,`group_name`");
			foreach($rows as $k=>$v){
				foreach($adminusergroup as $val){
					if($v['m_id']==$val['id']){
						$rows[$k]['group_name']=$val['group_name'];
					}
				}
				if($v['did']>0){
					$dids[]=$v['did'];
				}
			}
			if(!empty($dids)){
				$domain=$this->obj->DB_select_all("domain","`id` in (".@implode(",",$dids).")","`id`,`title`");
				foreach($rows as $k=>$v){
					foreach($domain as $val){
						if($v['did']==$val['id']){
							$rows[$k]['group_name']=$val['title'];
						}
					}
				}
			}
		}
		$this->yunset("rows",$rows);
		$this->yuntpl(array('admin/admin_siteadmin'));
	}
	function add_action(){
		$group=$this->obj->DB_select_once("admin_user_group","`group_type`='2'");
		if($group['id']==''){
			$this->yunset("nogroup",1);
		} 
		if(isset($_GET['uid'])){
			$adminuser=$this->obj->DB_select_once("admin_user","`uid`='".$_GET['uid']."'");
			$this->yunset("adminuser",$adminuser);
		}
		$domain=$this->obj->DB_select_all("domain","1 order by `id` desc","`id`,`title`");

		$user_group=$this->obj->DB_select_all("admin_user_group","`did`>0 OR `group_type`='2' order by `id` desc");
		$this->yunset("user_group",$user_group);

		$this->yunset("domain",$domain);
		$this->yuntpl(array('admin/admin_siteadmin_add'));
	}  
	function pass_action(){
		if($_POST['useradd']){
			$_POST['oldpass']=trim($_POST['oldpass']);
			$_POST['password']=trim($_POST['password']);
			$where="`uid`='".$_SESSION['auid']."'";
			$row=$this->obj->DB_select_once("admin_user",$where);
			if($_POST['oldpass']==''||$_POST['password']==''){
				$this->ACT_layer_msg("原始密码、新密码均不能为空！",8,$_SERVER['HTTP_REFERER']);
			}
			if($_POST['oldpass']==$_POST['password']){
				$this->ACT_layer_msg("新密码和原始密码一致，不需要修改！",8,$_SERVER['HTTP_REFERER']);
			}
			if(md5($_POST['oldpass'])!=$row['password']){
				$this->ACT_layer_msg("原始密码不正确！",8,$_SERVER['HTTP_REFERER']);
			}
			if($_POST['password']!=$_POST['okpassword']){
				$this->ACT_layer_msg("新密码两次输入不一致！",8,$_SERVER['HTTP_REFERER']);
			}
			$value.="`password`='".md5($_POST['password'])."'";
			$nbid=$this->obj->DB_update_all("admin_user",$value,$where);
			unset($_SESSION['authcode']);
			unset($_SESSION['auid']);
			unset($_SESSION['ausername']);
			unset($_SESSION['ashell']);
			$this->ACT_layer_msg("管理员(ID:".$row['uid']."帐号".$row['username'].")密码修改成功,请重新登录！",9,$_SERVER['HTTP_REFERER'],2,1);
		}

		$this->yuntpl(array('admin/admin_mypass'));
	} 
	function save_action(){
		if(isset($_POST['useradd'])){
			if(!empty($_POST['username'])&&!empty($_POST['name'])){
				
				if(!$_POST['m_id']){
					$this->ACT_layer_msg("请选择分站用户组！",8,$_SERVER['HTTP_REFERER']);
				}
				$value="`m_id`='".$_POST['m_id']."',`username`='".$_POST['username']."',`name`='".$_POST['name']."'";
				if($_POST['password']){
					$value.=",`password`='".md5($_POST['password'])."'";
				}
				if($_POST['did']){
					$value.=",`did`='".(int)$_POST['did']."'";
				}

				if(!$_POST[uid]){
				 	$nbid=$this->obj->DB_insert_once("admin_user","$value");
					$name="管理员（ID:".$nbid."）添加";
				 }else{ 
				 	$nbid=$this->obj->DB_update_all("admin_user",$value,"`uid`='".$_POST['uid']."'");
				 	if($_POST['uid']==$_SESSION['auid']){
						unset($_SESSION['authcode']);
						unset($_SESSION['auid']);
						unset($_SESSION['ausername']);
						unset($_SESSION['ashell']);
						$this->ACT_layer_msg( "管理员(ID:".$_POST['uid'].")修改成功,请重新登录！",9,$_SERVER['HTTP_REFERER'],2,1);
				 	}
				 	$name="管理员(ID:".$_POST['uid'].")更新";
				 }
				isset($nbid)?$this->ACT_layer_msg($name."成功！",9,"index.php?m=admin_siteadmin",2,1):$this->ACT_layer_msg($name."失败！",8,"index.php?m=admin_siteadmin");
			}else{
				$this->ACT_layer_msg( "请填写完整！",8,$_SERVER['HTTP_REFERER']);
			}
		}
	}
	function group_action(){
		$adminusergroup=$this->obj->DB_select_all("admin_user_group","`group_type`='2' OR `did`>0 order by id desc");
		
		$num=$this->obj->DB_select_all("admin_user","1 group by m_id desc","`m_id`,count(uid) as num");
		
		$domain=$this->obj->DB_select_all("domain","1 order by `id` desc","`id`,`title`");
	
		
		foreach($adminusergroup as $key=>$val){
			$adminusergroup[$key]['num']=0;
			foreach($num as $v){
				if($val['id']==$v['m_id']){
					$adminusergroup[$key]['num']=$v['num'];
				}
			}
			foreach($domain as $v){
				if($val['did']==$v['id']){
					$adminusergroup[$key]['domain']=$v['title'];
				}
			}
		}
		

		$this->yunset("adminusergroup",$adminusergroup);
		$this->yuntpl(array('admin/admin_siteadmin_group_list'));
	}
	function addgroup_action(){
		if((int)$_GET['id']){
			$where="`id`='".$_GET['id']."'";
			$admingroup=$this->obj->DB_select_once("admin_user_group",$where);
			$this->yunset("admin_group",$admingroup);
			$this->yunset("power",unserialize($admingroup[2]));
		}

		$menurows=$this->obj->DB_select_all("admin_navigation","`display`<>1 && dids='1' order by `sort` desc");
		$i=0;$j=0;$a=0;$b=0;
		if(is_array($menurows)){
			foreach($menurows as $key=>$v){
				if($v['keyid']==0){
					$navigation[$i]['id']=$v['id'];
					$navigation[$i]['name']=$v['name'];
					$i++;
				}
				if($v['menu']==2){
					$menu[$j]['id']=$v['id'];
					$menu[$j]['name']=$v['name'];
					$menu[$j]['url']=$v['url'];
					$j++;
				}
			}
		}
		if(is_array($navigation)){
			foreach($navigation as $va){
				if(is_array($menurows)){
					foreach($menurows as $key=>$v){
						if($v['keyid']==$va['id']){
							if(!is_array($one_menu[$va['id']]))$a=0;
							$one_menu[$va['id']][$a]['id']=$v['id'];
							$one_menu[$va['id']][$a]['name']=$v['name'];
							$a++;
							foreach($menurows as $key=>$vaa){
								if($vaa['keyid']==$v['id']){
									if(!is_array($two_menu[$v['id']]))$b=0;
									$two_menu[$v['id']][$b]['id']=$vaa['id'];
									$two_menu[$v['id']][$b]['name']=$vaa['name'];
									$two_menu[$v['id']][$b]['url']=$vaa['url'];
									$b++;
								}
							}
						}
					}
				}
			}
		}
		$domain=$this->obj->DB_select_all("domain","1 order by `id` desc","`id`,`title`");

		$this->yunset("domain",$domain);

		$this->yunset("one_menu",$one_menu);
		$this->yunset("two_menu",$two_menu);
		$this->yunset("navigation",$navigation);
		$this->yuntpl(array('admin/admin_siteadmin_group'));
	}
	function delgroup_action()
	{
		$this->check_token();
		if(isset($_GET['id'])){
			$where="`id`='".$_GET['id']."'";
			$result=$this->obj->DB_delete_all("admin_user_group",$where);
			isset($result)?$this->layer_msg('用户组（ID：'.$_GET['id'].'）删除成功！',9):$this->layer_msg('删除失败！',8);
		}else{
			$this->layer_msg('非法操作！',8);
		}
	}
	function savagroup_action(){
		extract($_POST);
		if(empty($group_name)){
			$this->ACT_layer_msg( "请填写权限组名称",8);
		}
		$power = array_filter($power);
		if(empty($power)){
			$this->ACT_layer_msg( "请至少选择一项权限",8);
		}

		$value="`group_name`='".$group_name."',`group_power`='".serialize(array_filter($power))."',`group_type`='2',`did`='".$did."'";
		
		
		if(!$groupid){
		
			$id=$this->obj->DB_insert_once("admin_user_group",$value);
			isset($id)?$this->ACT_layer_msg( "用户组(ID：".$id.")添加成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg( "用户组添加失败！",8,$_SERVER['HTTP_REFERER']);
		}else{

			$result=$this->obj->DB_update_all("admin_user_group", $value,"`id`='".$groupid."'");
			isset($result)?$this->ACT_layer_msg( "用户组(ID：".$groupid.")修改成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg( "修改失败！",8,$_SERVER['HTTP_REFERER']);
		}
	}
}

?>