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
class sysnews_controller extends company
{

	function index_action(){
		$urlarr=array("c"=>"sysnews","page"=>"{{page}}");
		$pageurl=Url('member',$urlarr);
		$rows = $this->get_page("sysmsg","`fa_uid`='".$this->uid."' order by id desc",$pageurl,"15");
		if(is_array($rows)){
			$patten = array("\r\n", "\n", "\r");
			foreach($rows as $key=>$value){
			
				$rows[$key]['content_all'] = str_replace($patten, "<br/>", $value['content']);
			}
		}
		$this->yunset("rows",$rows);
		
		
		$this->public_action();
		$this->company_satic();
		$this->yunset("js_def",7);
		$this->com_tpl('sysnews');
	}
	function del_action(){
		if ($_POST['del']||$_GET['id']){
			if(is_array($_POST['del'])){
				$ids=pylode(',',$_POST['del']);
				$layer_type='1';
			}else if($_GET['id']){
				$ids=(int)$_GET['id'];
				$layer_type='0';
			}
			$nid=$this->obj->DB_delete_all("sysmsg","`id` in(".$ids.") AND `fa_uid`='".$this->uid."'"," ");
 			if($nid)
 			{
 				
 				$this->obj->member_log("删除系统消息");
 				$this->layer_msg('删除成功！',9,$layer_type,"index.php?c=sysnews");
 			}else{
 				$this->layer_msg('删除失败！',8,$layer_type,"index.php?c=sysnews");
 			}
		}
	}
	function set_action(){
		if(intval($_POST['id'])){
			$this->obj->DB_update_all("sysmsg","`remind_status`='1'","`id`='".intval($_POST['id'])."' and `fa_uid`='".$this->uid."' and `remind_status`='0'");
		}
	}
}
?>