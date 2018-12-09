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
class part_controller extends company{
	function index_action(){
		include PLUS_PATH."part.cache.php";
		if(trim($_GET['keyword'])){
			$where.=" and name like '%".trim($_GET['keyword'])."%'";
			$urlarr['keyword']=trim($_GET['keyword']);
		}
		$urlarr['c']=$_GET['c'];
		$urlarr["page"]="{{page}}";
		$pageurl=Url('member',$urlarr);
		$rows=$this->get_page("partjob","`uid`='".$this->uid."'".$where,$pageurl,"10");
		if(is_array($rows)){
			foreach($rows as $k=>$v){
				$rows[$k]['salary_type']=$partclass_name[$v['salary_type']];
				$rows[$k]['type']=$partclass_name[$v['type']];
			}
		}
		$this->public_action();
		$this->yunset("rows",$rows);
		$this->company_satic();
		$this->yunset("js_def",3);
		$this->com_tpl('partlist');
	}
	function del_action(){
		if($_GET['del']||$_GET['id']){
			if(is_array($_GET['del'])){
				$del=@implode(",",$_GET['del']);
				$layer_type=1;
			}else{
				$del=(int)$_GET['id'];
				$layer_type=0;
			}
		}
		$oid=$this->obj->DB_delete_all("partjob","`id` in (".$del.") and `uid`='".$this->uid."'","");
		if($oid){
			$this->obj->DB_delete_all("part_collect","`jobid` in (".$del.") and `comid`='".$this->uid."'","");
			$this->obj->DB_delete_all("part_apply","`jobid` in (".$del.") and `comid`='".$this->uid."'","");
			$this->obj->member_log("删除兼职职位");
			$this->layer_msg('删除成功！',9,$layer_type,$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg('删除失败！',8,$layer_type,$_SERVER['HTTP_REFERER']);
		}
	}
	function opera_action(){
		$this->part();
	}

	  
	
	function refresh_part_action(){
		if($_POST){
 			$M=$this->MODEL('comtc');
 			$return = $M->refresh_part($_POST);

 			if($return['status']==1){
				
				echo json_encode(array('error'=>1,'msg'=>$return['msg']));
			}else if($return['status']==2){
				
				echo json_encode(array('error'=>2,'msg'=>$return['msg']));
			}else{
				
				echo json_encode(array('error'=>3,'msg'=>$return['msg'],'url'=>$return['url']));
			}
		}else{
			echo json_encode(array('error'=>3,'msg'=>'参数错误，请重试！'));
		}
	}
}
?>