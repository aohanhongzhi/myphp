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
class admin_tpl_index_controller extends adminCommon{
	function index_action(){
		$list=$this->obj->DB_select_all("tplindex","1 order by id desc");
		$this->yunset("list",$list);
		$this->yuntpl(array('admin/admin_tpl_index'));
	}
	function add_action(){
		if($_GET['id']){
			$list=$this->obj->DB_select_once("tplindex","id='".$_GET['id']."'");
			$timearr[]=date("Y-m-d",$list['stime']);
			$timearr[]=date("Y-m-d",$list['etime']);
			$time=implode(" ~ ",$timearr);
			$list['time']=$time;
			$this->yunset("row",$list);
		}
		$this->yuntpl(array('admin/admin_tpl_indexadd'));
	}
	function save_action(){
	    if($_POST['pic']=="") {
	        $this->ACT_layer_msg("请上传主题图片！",8);
	    }else{
	        $list=$this->obj->DB_select_once("tplindex","id='".$_POST['id']."'");
	        $_POST['pic'] = $this->checksrc($_POST['pic'],$list['pic']);
	    }
		$time=explode("~",$_POST['time']);
		$_POST['stime']=strtotime(trim($time[0]));
		$_POST['etime']=strtotime(trim($time[1]));
		unset($_POST['msgconfig']);
		unset($_POST['time']);
		if($_POST['id']){
			$id=$this->obj->update_once("tplindex",$_POST,array("id"=>$_POST['id']));
			$msg="主题模板(ID:".$_POST['id'].")更新";
		}else{
			$id=$this->obj->insert_into("tplindex",$_POST);
			$msg="主题模板(ID:".$id.")添加";
		}
		$this->cache();
		$id?$this->ACT_layer_msg($msg."成功！",9,"index.php?m=admin_tpl_index",2,1):$this->ACT_layer_msg($msg."失败！",8);
	}
	function del_action(){
		$this->check_token();
		$del=$_GET['id'];
		if($del){
			$list=$this->obj->DB_select_once("tplindex","id='".$del."'");
			if($list['pic']&&file_exists(APP_PATH.$list['pic'])){
				unlink_pic(APP_PATH.$list['pic']);
			}
			$this->obj->DB_delete_all("tplindex","`id`='".$del."'","");
			$this->cache();
			$this->layer_msg("主题模板(ID".$del.")删除成功！",9,0,$_SERVER['HTTP_REFERER']);
		}else{
			$this->cache();
			$this->layer_msg('请先选择！',8,0,$_SERVER['HTTP_REFERER']);
		}
		
	}
	
	function cache(){
		include_once(LIB_PATH."cache.class.php");
		$cacheclass= new cache(PLUS_PATH,$this->obj);
		$makecache=$cacheclass->indextpl_cache("indextpl.cache.php");
	}
}