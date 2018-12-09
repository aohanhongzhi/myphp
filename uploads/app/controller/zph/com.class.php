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
class com_controller extends zph_controller{ 
	function index_action(){ 
		$this->Zphpublic_action();
		$id=(int)$_GET['id'];
		$M=$this->MODEL('zph');
		$Job=$this->MODEL('job');
		$UserinfoM=$this->MODEL('userinfo');
		$row=$M->GetZphOnce(array("id"=>$id)); 
		if($row['id']==''){
			$this->ACT_msg(url("zph"),"没有找到该招聘会！");
		}
		$urlarr["c"]=$_GET['c'];
		$urlarr["id"]=$id;
		$urlarr["page"]="{{page}}";
		$pageurl=Url('zph',$urlarr,"1");
		$rows=$this->get_page("zhaopinhui_com","`zid`='".$id."' and status='1'  order by ctime asc",$pageurl,"13");
		if(is_array($rows)&&$rows){
			foreach($rows as $v){
				$uid[]=$v['uid'];
				$sid[]=$v['sid'];
				$cid[]=$v['cid'];
				$bid[]=$v['bid'];
			}	 
			$sidspace=$M->GetspaceList(array("id in(".@implode(",",$sid).")"),array('field'=>'id,name'));
			$cidspace=$M->GetspaceList(array("id in(".@implode(",",$cid).")"),array('field'=>'id,name'));
			$bidspace=$M->GetspaceList(array("id in(".@implode(",",$bid).")"),array('field'=>'id,name'));
			
			$com=$M->GetZphComInfo($UserinfoM,array("uid in(".@implode(",",$uid).")"),array("field"=>"`uid`,name,shortname"));		
			foreach($rows as $key=>$v){
				foreach($com as $val){
					if($v['uid']==$val['uid']){
						if($val['shortname']){
							$rows[$key]['comname']=$val['shortname'];
						}else{
							$rows[$key]['comname']=$val['name'];
						}
						if($val['comtpl'] && $val['comtpl']!="default"){
							$rows[$key]['url']=Url('company',array("id"=>$v[uid]))."#job";
						}else{
							$rows[$key]['url']=Url('company',array("id"=>$v[uid]));
						}
					}
				}   
				foreach($sidspace as $val){
					if($v['sid']==$val['id']){
						$rows[$key]['sidname']=$val['name'];
					}
				}
				foreach($cidspace as $val){
					if($v['cid']==$val['id']){
						$rows[$key]['cidname']=$val['name'];
					}
				}
				foreach($bidspace as $val){
					if($v['bid']==$val['id']){
						$rows[$key]['bidname']=$val['name'];
					}
				}
				$rows[$key]['job']=$Job->GetComjobList(array("`id` in (".$v['jobid'].") and `uid`='".$v['uid']."' and `status`='0' and `r_status`='1'"),array('field'=>"name,id")); 
			}
		}
		if($row['starttime']<time()){
			$row['isend']=1;
		} 
		$this->yunset('rows',$rows);
 		$this->yunset("row",$row);
		$data['zph_title']=$row['title'];
		$data['zph_desc']=$this->GET_content_desc($row['body']);
		$this->data=$data;
		$this->seo("zph_com");
		$this->zph_tpl('zphcom'); 
	} 
}
?>