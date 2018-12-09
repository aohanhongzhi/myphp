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
class admin_company_pic_controller extends adminCommon{
	function index_action(){
		$where="`logo`<>'' ";
		if(trim($_GET['keyword'])){
            if($_GET['type']=='2'){
				$where.=" AND `uid` like '%" .trim($_GET['keyword']). "%' ";
            }else{
				$where.= "  AND `name` like '%".trim($_GET['keyword'])."%' ";
			}
			$urlarr['type']=$_GET['type'];
			$urlarr['keyword']=$_GET['keyword'];
		}
		$where.=" order by `uid` desc";
		$urlarr['page']="{{page}}";
		$pageurl=Url('admin_company_pic',$urlarr,'admin');
		$rows=$this->get_page("company",$where,$pageurl,"15");
		if(is_array($rows)){
			foreach($rows as $key=>$value){
				if(strpos($value['logo'],'http')===false&&file_exists(str_replace("./",APP_PATH."/",$value['logo']))){
					$rows[$key]['logo'] = str_replace("./",$this->config['sy_weburl']."/",$value['logo']);
				}else{
					$rows[$key]['logo'] = $this->config['sy_weburl']."/".$this->config['sy_unit_icon'];
				}
			}
		}
		$this->yunset("rows",$rows);
		$this->yuntpl(array('admin/admin_company_pic'));
	}

	function show_action(){
		$where="1 ";
		if(trim($_GET['keyword'])){
			if($_GET['type']=='2'){
				$where.=" AND `uid` like '%" .trim($_GET['keyword']). "%' ";
			}elseif($_GET['type']=='3'){
				$where.=" AND `title` like '%" .trim($_GET['keyword']). "%' ";
			}else{
				$com=$this->obj->DB_select_all("company","`name` like '%".trim($_GET['keyword'])."%' ","`uid`");
				if($com){
					foreach ($com as $v){
						$uid[]=$v['uid'];
					}
				}
				$where.=" and `uid` in (".pylode(',', $uid).")";
			}
			$urlarr['type']=$_GET['type'];
			$urlarr['keyword']=$_GET['keyword'];
		}
		$where.=" order by `id` desc";
		$urlarr['c']="show";
		$urlarr['page']="{{page}}";
		$pageurl=Url('admin_company_pic',$urlarr,'admin');
		$rows=$this->get_page("company_show",$where,$pageurl,"15");
		if(is_array($rows)){
			foreach ($rows as $v){
				$uids[]=$v['uid'];
			}
			$company=$this->obj->DB_select_all("company","`uid` in (".pylode(',', $uids).")","`uid`,`name`");
			foreach($rows as $key=>$value){
				if(strpos($value['picurl'],'http')===false&&file_exists(str_replace("./",APP_PATH."/",$value['picurl']))){
					$rows[$key]['picurl'] = str_replace("./",$this->config['sy_weburl']."/",$value['picurl']);
				}else{
					$rows[$key]['picurl'] ='';
				}
				$rows[$key]['title']=mb_substr($value['title'], 0,15);
				foreach ($company as $v){
					if($v['uid']==$value['uid']){
						$rows[$key]['name']=$v['name'];
					}
				}
			}
		}
		$this->yunset("rows",$rows);
		$this->yuntpl(array('admin/admin_company_picshow'));
	}
	function banner_action(){
		$where="1";
		if(trim($_GET['keyword'])){
			if($_GET['type']=='2'){
				$where.=" AND `uid` like '%" .trim($_GET['keyword']). "%' ";
			}else{
				$com=$this->obj->DB_select_all("company","`name` like '%".trim($_GET['keyword'])."%' ","`uid`");
				if($com){
					foreach ($com as $v){
						$uid[]=$v['uid'];
					}
				}
				$where.=" and `uid` in (".pylode(',', $uid).")";
			}
			$urlarr['type']=$_GET['type'];
			$urlarr['keyword']=$_GET['keyword'];
		}
		$where.=" order by `id` desc";
		$urlarr['c']="banner";
		$urlarr['page']="{{page}}";
		$pageurl=Url('admin_company_pic',$urlarr,'admin');
		$rows=$this->get_page("banner",$where,$pageurl,"15");
		if(is_array($rows)){
			foreach ($rows as $v){
				$uids[]=$v['uid'];
			}
			$company=$this->obj->DB_select_all("company","`uid` in (".pylode(',', $uids).")","`uid`,`name`");
			foreach($rows as $key=>$value){
				if(strpos($value['pic'],'http')===false&&file_exists(str_replace("./",APP_PATH."/",$value['pic']))){
					$rows[$key]['pic'] = str_replace("./",$this->config['sy_weburl']."/",$value['pic']);
				}else{
					$rows[$key]['pic'] = $this->config['sy_weburl']."/".$this->config['sy_banner'];
				}
				foreach ($company as $v){
					if($v['uid']==$value['uid']){
						$rows[$key]['name']=$v['name'];
					}
				}
			}
		}
		$this->yunset("rows",$rows);
		$this->yuntpl(array('admin/admin_company_picbanner'));
	}
	function uploadsave_action(){
		$_POST=$this->post_trim($_POST);
		extract($_POST);
		$id=$_POST['id'];
		$UploadM = $this->MODEL('upload');
		if($update){
			if($type=='logo'){
				$row=$this->obj->DB_select_once("company","`uid`='".$id."'","logo");
				if($_FILES['file']['tmp_name']){
					$upload=$UploadM->Upload_pic("../data/upload/company/",false);
					$pic=$upload->picture($_FILES['file']);
					$picmsg=$UploadM->picmsg($pic,$_SERVER['HTTP_REFERER']);
					if($picmsg['status'] == $pic){
						$this->ACT_layer_msg($picmsg['msg'],8);
					}
					$data['logo'] = str_replace("../data","./data",$pic);
					if($row['logo']){
						unlink_pic(str_replace("./",APP_PATH."/",$row['logo']));
					}
				}
				$where['uid']=$id;
				$nbid=$this->obj->update_once("company",$data,$where);
				isset($nbid)?$this->ACT_layer_msg("企业logo(ID:".$id.")修改成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg("修改失败！",8,$_SERVER['HTTP_REFERER']);
			}
			if($type=='show'){
				$row=$this->obj->DB_select_once("company_show","`id`='".$id."'","picurl");
				if($_FILES['file']['tmp_name']){
					$upload=$UploadM->Upload_pic("../data/upload/show/",false);
					$pic=$upload->picture($_FILES['file']);
					$picmsg=$UploadM->picmsg($pic,$_SERVER['HTTP_REFERER']);
					if($picmsg['status'] == $pic){
						$this->ACT_layer_msg($picmsg['msg'],8);
					}
					$data['picurl'] = str_replace("../data","./data",$pic);
					if($row['picurl']){
						unlink_pic(str_replace("./",APP_PATH."/",$row['picurl']));
					}
				}
				$data['title']=$title;
				$data['sort']=$sort;
				$data['ctime']=time();
				$where['id']=$id;
				$nbid=$this->obj->update_once("company_show",$data,$where);
				isset($nbid)?$this->ACT_layer_msg("企业环境(ID:".$id.")修改成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg("修改失败！",8,$_SERVER['HTTP_REFERER']);
					
			}
			if($type=='banner'){
				$row=$this->obj->DB_select_once("banner","`id`='".$id."'","pic");
				if($_FILES['file']['tmp_name']){
					$upload=$UploadM->Upload_pic("../data/upload/cpmoany/",false);
					$pic=$upload->picture($_FILES['file']);
					$picmsg=$UploadM->picmsg($pic,$_SERVER['HTTP_REFERER']);
					if($picmsg['status'] == $pic){
						$this->ACT_layer_msg($picmsg['msg'],8);
					}
					$data['pic'] = str_replace("../data","./data",$pic);
					if($row['pic']){
						unlink_pic(str_replace("./",APP_PATH."/",$row['pic']));
					}
				}
				$where['id']=$id;
				$nbid=$this->obj->update_once("banner",$data,$where);
				isset($nbid)?$this->ACT_layer_msg("企业横幅(ID:".$id.")修改成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg("修改失败！",8,$_SERVER['HTTP_REFERER']);
				
			}
		}
	}
	
	function del_action(){
		if($_GET['delid']){
			$this->check_token();
			if($_GET['type']=='logo'){
				$row=$this->obj->DB_select_once("company","`uid`='".$_GET['delid']."'","`logo`");
				unlink_pic(str_replace("./",APP_PATH."/",$row['logo']));
				$delid=$this->obj->DB_update_all("company","`logo`=''","`uid`='".$_GET['delid']."'");
				$delid?$this->layer_msg("企业logo(ID:".$_GET['delid'].")删除成功！",9,0,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,0,$_SERVER['HTTP_REFERER']);
			}
			if($_GET['type']=='show'){
				$row=$this->obj->DB_select_once("company_show","`id`='".$_GET['delid']."'");
				unlink_pic(".".$row['picurl']);
				$delid=$this->obj->DB_delete_all("company_show","`id`='".$_GET['delid']."'");
				$delid?$this->layer_msg("企业环境(ID:".$_GET['delid'].")删除成功！",9,0,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,0,$_SERVER['HTTP_REFERER']);
			}
			if($_GET['type']=='banner'){
				$row=$this->obj->DB_select_once("banner","`id`='".$_GET['delid']."'");
				unlink_pic(".".$row['pic']);
				$delid=$this->obj->DB_delete_all("banner","`id`='".$_GET['delid']."'");
				$delid?$this->layer_msg("企业横幅(ID:".$_GET['delid'].")删除成功！",9,0,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,0,$_SERVER['HTTP_REFERER']);
			}
		}
		if(is_array($_GET['del'])){
			$this->check_token();
			if($_GET['type']=='logo'){
				$uids = @implode(",",$_GET['del']);
				$row=$this->obj->DB_select_all("company","`uid` in (".$uids.") and `logo`!=''","logo");
				if(is_array($row)){
					foreach($row as $v){
						unlink_pic(str_replace("./",APP_PATH."/",$v['logo']));
					}
				}
				$delid=$this->obj->DB_update_all("company","`logo`=''","`uid` in (".$uids.")");
				$delid?$this->layer_msg("企业logo(ID:".$uids.")删除成功！",9,1,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,1,$_SERVER['HTTP_REFERER']);
			}
			if($_GET['type']=='show'){
				$ids = @implode(",",$_GET['del']);
				$row=$this->obj->DB_select_all("company_show","`id` in (".$ids.") and `picurl`!=''","picurl");
				if(is_array($row)){
					foreach($row as $v){
						unlink_pic(str_replace("./",APP_PATH."/",$v['picurl']));
					}
				}
				$delid=$this->obj->DB_delete_all("company_show","`id` in (".$ids.")","");
				$delid?$this->layer_msg("企业环境(ID:".$ids.")删除成功！",9,1,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,1,$_SERVER['HTTP_REFERER']);
			}
			if($_GET['type']=='banner'){
				$ids = @implode(",",$_GET['del']);
				$row=$this->obj->DB_select_all("banner","`id` in (".$ids.") and `pic`!=''","pic");
				if(is_array($row)){
					foreach($row as $v){
						unlink_pic(str_replace("./",APP_PATH."/",$v['pic']));
					}
				}
				$delid=$this->obj->DB_delete_all("banner","`id` in (".$ids.")","");
				$delid?$this->layer_msg("企业横幅(ID:".$ids.")删除成功！",9,1,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,1,$_SERVER['HTTP_REFERER']);
			}
		}else{
			$this->layer_msg( "请选择您要删除的图片！",8,1);
	    }
	}
}
?>