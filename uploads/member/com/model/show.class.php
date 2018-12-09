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
class show_controller extends company{
	function index_action(){
		$urlarr['c']="show";
		$urlarr["page"]="{{page}}";
		$pageurl=Url('member',$urlarr);
		$this->get_page("company_show","`uid`='".$this->uid."' order by sort desc",$pageurl,"12","`title`,`id`,`picurl`");
		$sessionid=session_id();
		$this->yunset("sessionid",$sessionid);
		$this->public_action();
		$this->company_satic();
		$this->yunset("js_def",2);
		$this->com_tpl('show');
	}
	function del_action(){
		if($_GET['id']){
			$row=$this->obj->DB_select_once("company_show","`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'","`picurl`");
			if(is_array($row)){
				unlink_pic(".".$row['picurl']);
				$oid=$this->obj->DB_delete_all("company_show","`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'");
			}
			if($oid){
				$this->obj->member_log("删除企业环境展示");
				$this->layer_msg('删除成功！',9,0,"index.php?c=show");
			}else{
				$this->layer_msg('删除失败！',8,0,"index.php?c=show");
			}
		}
	}
	function showpic_action(){
		if($_GET['id']){
			$this->public_action();
			$picurl=$this->obj->DB_select_once("company_show","`id`='".(int)$_GET['id']."' and `uid`='".$this->uid."'","`picurl`,`title`,`sort`");
			$this->yunset("picurl",$picurl);
			$this->yunset("uid",$this->uid);
			$this->yunset("id",$_GET['id']);
		    $this->yunset("js_def",2);
			$this->com_tpl('editshow');
		}
	}
	function delshow_action(){
		$ids = pylode(',',@explode(',',$_POST['ids']));
		$company_show=$this->obj->DB_select_all("company_show","`uid`='".$this->uid."' and `id` in (".$ids.")","`picurl`");
		if(is_array($company_show)&&$company_show){
			foreach($company_show as $val){
				unlink_pic(".".$val['picurl']);
			}
			$this->obj->DB_delete_all("company_show","`id` in (".$ids.") and `uid`='".$this->uid."'","");
			$this->obj->member_log("删除企业环境展示");
		}
		return true;
	}
	function addshow_action(){
		$this->public_action();
		$this->yunset("uid",$this->uid);
		$this->yunset("js_def",2);
		$this->com_tpl('addshow');
	}
	function upshow_action(){
	    if ($_POST['picurl']!=''){
	        $show = $this->obj->DB_select_once('company_show',"`uid`='".$this->uid."'and `id`='".$_POST['id']."'");
	        if ($show['picurl']!=$_POST['picurl']){
	            $data['picurl'] = $this->checksrc($_POST['picurl'],$show['picurl']);
	        }
	    }
	    $data['title']=$_POST['title'];
	    $data['sort']=$_POST['showsort'];
	    $data['ctime']=time();
	    $companyM = $this->MODEL('company');
	    $nid = $companyM->UpdateShow($data,array('id'=>intval($_POST['id']),'uid'=>$this->uid));
	    if($nid){
	        $this->ACT_layer_msg("更新成功！",9,"index.php?c=show");
	    }else{
	        $this->ACT_layer_msg("更新失败！",8,"index.php?c=show");
	    }
	}
	 function save_action(){
		$UploadM=$this->MODEL("upload");
		if (!empty($_FILES)){
		    $upload=$UploadM->Upload_pic("../data/upload/show/");
		    $pictures=$upload->picture($_FILES['file']);
		    $picmsg=$UploadM->picmsg($pictures);
		    if($picmsg['status'] == $pictures){
		        
		    }
		    $pic = str_replace("../data/upload/show","./data/upload/show",$pictures);
		    $name=$_FILES['file']['name'];
		    $data=array(
		        'title'=>$this->stringfilter($name),
		        'uid'=>$this->uid,
		        'picurl'=>$pic,
		        'ctime'=>time()
		    );
		    $companyM = $this->MODEL('company');
		    $id = $companyM->AddCompanyShow($data);
		    $this->obj->member_log("添加环境展示");
		    $arr=array(
		        'jsonrpc'=>'2.0',
		        'id'=>$id
		    );
		    echo json_encode($arr);die;
		}
	}
}
?>