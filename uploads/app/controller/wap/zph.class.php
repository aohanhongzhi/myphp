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
class zph_controller extends common{
	function index_action(){
		$this->yunset("headertitle","招聘会");
		$this->seo("zph");
		$this->yuntpl(array('wap/zph'));
	}
	function show_action(){
		$id=(int)$_GET['id'];
		$M=$this->MODEL('zph');
	    if($id){
	      $row=$M->GetZphOnce(array("id"=>$id));
	      $row["stime"]=strtotime($row['starttime'])-mktime();
	      $row["etime"]=strtotime($row['endtime'])-mktime();
	      $data['zph_title']=$row['title'];
	      $data['zph_desc']=$this->GET_content_desc($row['body']);
	    }
		$this->data=$data;
		$this->yunset("Info",$row);
		$this->yunset("headertitle","招聘会详情");
		$this->seo("zph_show");
		$this->yuntpl(array('wap/zph_show'));
	}
	function com_action(){
		$id=(int)$_GET['id'];
		$M=$this->MODEL('zph');
		$Job=$this->MODEL('job');
		$UserinfoM=$this->MODEL('userinfo');
	    if($id){
	        $row=$M->GetZphOnce(array("id"=>$id),array('field'=>'starttime,endtime,title,body'));
	        $row["stime"]=strtotime($row['starttime'])-mktime();
	        $row["etime"]=strtotime($row['endtime'])-mktime();
	        $data['zph_title']=$row['title'];
	        $data['zph_desc']=$this->GET_content_desc($row['body']);
	        $urlarr["c"]=$_GET['c'];
	        $urlarr["a"]=$_GET['a'];
	        $urlarr["id"]=(int)$_GET['id'];
	        $urlarr["page"]="{{page}}";
	        $pageurl=Url('wap',$urlarr,"1");
	        $rows=$M->get_page("zhaopinhui_com","`zid`='".(int)$_GET['id']."' and status='1'  order by ctime asc",$pageurl,"13");
	        
	        if(is_array($rows['rows'])&&$rows['rows']){	       
				$uid=$sid=$cid=$bid=$jobid=array();
				foreach($rows['rows'] as $v){
					$uid[]=$v['uid'];
					$sid[]=$v['sid'];
					$cid[]=$v['cid'];
					$bid[]=$v['bid'];
					if($v['jobid']){
						$jid = array_filter(@explode(',',$v['jobid']));
						if(!empty($jid)){
							$jobid =array_merge($jobid,$jid);
						}
						
					}
				}
				$com=$M->GetZphComInfo($UserinfoM,array("uid in(".@implode(",",$uid).")"),array("field"=>"`uid`,`shortname`,`name`"));
				$sidspace=$M->GetspaceList(array("id in(".@implode(",",$sid).")"),array('field'=>'id,name'));
				$cidspace=$M->GetspaceList(array("id in(".@implode(",",$cid).")"),array('field'=>'id,name'));
				$bidspace=$M->GetspaceList(array("id in(".@implode(",",$bid).")"),array('field'=>'id,name'));
				$jobs=$Job->GetComjobList(array("`id` in (".@implode(",",$jobid).")  and `status`<>'1' and `r_status`<>'2'"),array('field'=>"name,uid,id")); 
				
				foreach($rows['rows'] as $key=>$v){
					foreach($com as $val){
						if($v['uid']==$val['uid']){
							if($val['shortname']){
								$rows['rows'][$key]['comname']=$val['shortname'];
							}else{
								$rows['rows'][$key]['comname']=$val['name'];
							}
							
						}
					}
					foreach($sidspace as $val){
						if($v['sid']==$val['id']){
							$rows['rows'][$key]['sidname']=$val['name'];
						}
					}
					foreach($cidspace as $val){
						if($v['cid']==$val['id']){
							$rows['rows'][$key]['cidname']=$val['name'];
						}
					}
					foreach($bidspace as $val){
						if($v['bid']==$val['id']){
							$rows['rows'][$key]['bidname']=$val['name'];
						}
					}
					foreach($jobs as $val){
						if($v['uid']==$val['uid']){
							$rows['rows'][$key]['job'][]=array('id'=>$val['id'],'uid'=>$val['uid'],'name'=>$val['name']);
						}
					}
				}
	        }
	    } 
		$this->yunset($rows);
		$this->yunset("zphspace",$zphspace);
		$this->yunset("row",$row);
		$this->data=$data;
		$this->yunset("headertitle","参会企业");
		$this->seo("zph_com");
		$this->yuntpl(array('wap/zph_com'));
	}
	function reserve_action(){
		$id=(int)$_GET['id'];
		$M=$this->MODEL('zph');
		$row=$M->GetZphOnce(array("id"=>$id));
		$row["stime"]=strtotime($row['starttime'])-mktime();
		$row["etime"]=strtotime($row['endtime'])-mktime();
		$rows=$M->GetZphPic(array("zid"=>$id));
		$data['zph_title']=$row['title'];
		$data['zph_desc']=$this->GET_content_desc($row['body']);
		$this->data=$data;
		$this->yunset("Info",$row);
		if($row['reserved']){
			$reserved=@explode(",",$row['reserved']);
			$this->yunset("reserved",$reserved);
		}
		$this->yunset("Image_info",$rows);
		$space=$M->GetZphspaceOnce(array("id"=>$row['sid']));
		$this->yunset("space",$space);
		$spacelist=$M->GetspaceList(array("keyid"=>$row['sid']),array("orderby"=>"sort","desc"=>"asc"));
		if(is_array($spacelist)){
			foreach($spacelist as $v){
				$keyid[]=$v['id'];
			}
			$keyid=@implode(",",$keyid);
			$spacelistall=$M->GetspaceList(array("keyid in (".$keyid.")"),array("orderby"=>"sort","desc"=>"asc"));
			$comlist=$M->GetZphComList(array("zid"=>$id));
			if(is_array($comlist)){
				foreach($comlist as $val){
					$uids[]=$val['uid'];
					$jobids[]=$val['jobid'];
				}
				$Company=$this->MODEL("company");
				$companylist=$Company->GetComList(array("uid in (".@implode(",",$uids).")"),array("field"=>"uid,name"));
				$Job=$this->MODEL("job");
				$joblist=$Job->GetComjobList(array("id in (".@implode(",",$jobids).")"),array("field"=>"id,name,lastupdate"));
				foreach($comlist as $k=>$v){
					foreach($companylist as $val){
						if($v['uid']==$val['uid'] ){
							$comlist[$k]['name']=$val['name'];
						}
					}
					$jobid=@explode(",",$v['jobid']);
					foreach($joblist as $val){
						if(in_array($val['id'],$jobid)){
							$comlist[$k]['joblist'][]=$val;
						}
					}
				}
				foreach($spacelistall as $k=>$v){
					$spacelistall[$k]['comstatus']="-1";
					foreach($comlist as $val){
						if($v['id']==$val['bid']){
							$spacelistall[$k]['comstatus']=$val['status'];
							$spacelistall[$k]['uid']=$val['uid'];
							$spacelistall[$k]['comname']=$val['name'];
							$spacelistall[$k]['joblist']=$val['joblist'];
						}
					}
				}
			}
			foreach($spacelist as $k=>$v){
				foreach($spacelistall as $val){
					if($v['id']==$val['keyid']){
						$spacelist[$k]['list'][]=$val;
					}
				}
			}
		} 
		$this->yunset("spacelist",$spacelist);
		$this->yunset("headertitle","在线预订");
		$this->seo("zph_reserve");
		$this->yuntpl(array('wap/zph_reserve'));
	}
}
?>