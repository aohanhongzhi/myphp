<?php
/*
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2018 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 *
 */
class special_controller extends adminCommon{
	function index_action(){

        $urlarr['page']="{{page}}";
		$pageurl=Url($_GET['m'],$urlarr,'admin');
		$rows=$this->get_page("special","1",$pageurl,$this->config['sy_listnum']);
		if(is_array($rows)){
			$zid=array();
			foreach($rows as $key=>$val){
				$rows[$key]['comnum']=$rows[$key]['booking']='0';
				$zid[]=$val['id'];
			}
			$all=$this->obj->DB_select_all("special_com","`sid` in(".pylode(",",$zid).") group by `sid` ","`sid`,count(id) as num");
			$status=$this->obj->DB_select_all("special_com","`sid` in(".pylode(",",$zid).") and `status`='0' group by `sid` ","`sid`,count(id) as num");
			foreach($rows as $key=>$v){
				foreach($all as $val){
					if($v['id']==$val['sid']){
						$rows[$key]['comnum']=$val['num'];
					}
				}
				foreach($status as $val){
					if($v['id']==$val['sid']){
						$rows[$key]['booking']=$val['num'];
					}
				}
			}
            
		}
		
		
		
		$this->yunset("rows",$rows);
		
		$this->yuntpl(array('admin/admin_special'));
	}
    function addlist_action(){
        $companylsit=$this->obj->DB_select_all("company_job","`state`=1 and `status`=0 and `edate`>".time()." GROUP BY uid","`uid`");
        $companyuids=array();
        foreach($companylsit as $vv){
            $companyuids[]=$vv['uid'];
        }
        $where="`uid` in(".pylode(",",$companyuids).") ";
        if($_GET['order'])
		{
			$where.=" order by ".$_GET['t']." ".$_GET['order'];
			$urlarr['order']=$_GET['order'];
			$urlarr['t']=$_GET['t'];
		}else{
			$where.=" order by uid desc";
		}
        $urlarr['c']=$_GET['c'];
        $urlarr['sid']=$_GET['sid'];
        $urlarr['page']="{{page}}";
		$pageurl=Url($_GET['m'],$urlarr,'admin');
        $M=$this->MODEL();
        $sid=$_GET['sid'];
		$PageInfo=$M->get_page("company",$where,$pageurl,$this->config['sy_listnum']);
        $this->yunset($PageInfo);
        $rows=$PageInfo['rows'];
        if(is_array($rows)&&$rows)      
		{   
            foreach($rows as $v){
                $uids[]=$v['uid'];
            }
            $all=$this->obj->DB_select_all("special_com","`uid` in(".pylode(",",$uids).") and `sid`='".$sid."'");
            foreach($rows as $key=>$val){
                $rows[$key]['uid_n']=$val['uid'];
                $rows[$key]['name_n']=$val['name'];
                foreach($all as $v){
                    if($v['uid']!=""){
                        if($val['uid']==$v['uid']){
                         $rows[$key]['cuid']=$v['uid'];   
                        } 
                    }  
                }
            }
		}
        $this->yunset("sid", $sid);
		$this->yunset("rows", $rows);
		$this->yunset("get_type", $_GET);
		$this->yuntpl(array('admin/admin_special_company'));
    }

	function add_action(){
		if($_GET['id']){
			$row=$this->obj->DB_select_once("special","`id`='".$_GET['id']."'");
			$row['rating']=@explode(',',$row['rating']);
			$this->yunset("row",$row);
		}
		$qy_rows=$this->obj->DB_select_all("company_rating","`category`=1 order by sort desc");
		$this->yunset("qy_rows",$qy_rows);
		$publicdir = "../app/template/".$this->config['style']."/special/";
		$filesnames = @scandir($publicdir);
		if(is_array($filesnames)){
			foreach($filesnames as $key=>$value){
				if($value!='.' && $value!='..' ){
					 $typearr = explode('.',$hostdir.$value);
					 if(in_array(end($typearr),array('htm'))) {
						 if($value!="index.htm"){
						   	$file[] =$value;
						 }
					 }
				 }

			}
		}
		$this->yunset("file",$file);
		$this->yuntpl(array('admin/admin_special_add'));
	}
	function save_action(){
	    $id=(int)$_POST['id'];
	    unset($_POST['save']);
	    unset($_POST['id']);
	    $_POST['sort']=(int)$_POST['sort'];
	    $_POST['limit']=(int)$_POST['limit'];
	    $_POST['etime']=strtotime($_POST['etime']);
	    $_POST['ctime']=mktime();
	    $_POST["intro"]= str_replace(array("&amp;","background-color:#ffffff","background-color:#fff","white-space:nowrap;"),array("&",'','',''),$_POST["intro"]);
	    if($_POST['rating']&&is_array($_POST['rating'])){
	        $_POST['rating']=implode(',',$_POST['rating']);
	    }else{
	        $_POST['rating']='';
	    }
	    $row=$this->obj->DB_select_once("special","`id`='".$id."'");
	    $_POST['pic'] = $this->checksrc($_POST['pic'],$row['pic']);
	    $_POST['background'] = $this->checksrc($_POST['background'],$row['background']);
	    if(!$id){
	        $nid=$this->obj->insert_into("special",$_POST);
	        $name="专题招聘（ID：".$nid."）添加";
	    }else{
	        $where['id']=$id;
	        $nid=$this->obj->update_once("special",$_POST,$where);
	        $name="专题招聘（ID：".$id."）更新";
	    }
		$nid?$this->ACT_layer_msg($name."成功！",9,"index.php?m=special",2,1):$this->ACT_layer_msg($name."失败！",8,"index.php?m=special");
	}
	function com_action(){
		$_GET['id']=(int)$_GET['id'];
		$urlarr['c']=$_GET['c'];
		$urlarr['id']=$_GET['id'];
		$urlarr['page']="{{page}}";
		$pageurl=Url($_GET['m'],$urlarr,'admin');
		$rows=$this->get_page("special_com","`sid`='".$_GET['id']."' ORDER BY time DESC",$pageurl,$this->config['sy_listnum']);
		if($rows&&is_array($rows)){
			$uid=array();
			foreach($rows as $val){
				$uid[]=$val['uid'];
			}
			$company=$this->obj->DB_select_all("company","`uid` in(".pylode(',',$uid).")","uid,name");
			$sid=$_GET['id'];
            foreach($rows as $key=>$val){
				
                foreach($company as $v){
					if($val['uid']==$v['uid']){
						$rows[$key]['name']=$v['name'];
					}
				}
			}
		}
        $this->yunset("sid",$sid);
		$this->yunset("rows",$rows);
		$SpecialM=$this->MODEL('special');
        $info=$SpecialM->GetSpecial(array("id"=>$sid));
        $applynum =$SpecialM->GetComNum(array("sid"=>$sid,'status'=>'1'));
		if($info['limit']>$applynum){
			$this->yunset("applyman",'1');
		}
		$this->yuntpl(array('admin/admin_special_com'));
	}
	function statuscom_action(){
		$pid=$_POST['pid'];
		$status=(int)$_POST['status'];
		$statusbody=trim($_POST['statusbody']); 
		if($status=='2'){
			$rows=$this->obj->DB_select_all("special_com","`id` IN ($pid) and `status`<>'2'","`uid`,`integral`");
		}
		$id=$this->obj->DB_update_all("special_com","`status`='$status',`statusbody`='$statusbody'","`id` IN ($pid) and `status`<>'2'");
		if($id&&count($rows)){
			foreach($rows as $val){
				if($val['integral']>0){
					$IntegralM=$this->MODEL('integral');
					$IntegralM->company_invtal($val['uid'],$val['integral'],true,"专题招聘未通过审核，退还".$this->config['integral_pricename'],true,2,'integral');
				}
			}
		}
		$id?$this->ACT_layer_msg("操作成功！",9,$_SERVER['HTTP_REFERER']):$this->ACT_layer_msg("操作失败！",8,$_SERVER['HTTP_REFERER']);
	}
	function getinfo_action(){
		$row=$this->obj->DB_select_once("special_com","`id`='".intval($_POST['id'])."' ","`statusbody`");
		echo $row['statusbody'];die;
	}
	function delcom_action(){
		$this->check_token();
	    if($_GET['del']||$_GET['id']){
    		if(is_array($_GET['del'])){
    			$layer_type=1;
				$del=@implode(',',$_GET['del']);
	    	}else{
	    		$layer_type=0;
	    		$del=$_GET['id'];
	    	}
			$rows=$this->obj->DB_select_all("special_com","`id` in (".$del.") and `status`=0","`uid`,`integral`");
			if(count($rows)){
				foreach($rows as $val){
					if($val['integral']>0){
						$IntegralM=$this->MODEL('integral');
						$IntegralM->company_invtal($val['uid'],$val['integral'],true,"取消专题招聘报名，退还".$this->config['integral_pricename'],true,2,'integral');
					}
				}
			}
			$this->obj->DB_delete_all("special_com","`id` in (".$del.")","");
			$this->layer_msg("公司申请(ID:".$del.")删除成功！",9,$layer_type,$_SERVER['HTTP_REFERER']);
    	}else{
			$this->layer_msg("请选择您要删除的信息！",8,1);
    	}
	}
	function del_action(){
		$IntegralM=$this->MODEL('integral');
		$_GET['id']=(int)$_GET['id'];
		if($_GET['id']){
			$this->check_token();
			$nid=$this->obj->DB_delete_all("special","`id`='".$_GET['id']."'");
			$rows=$this->obj->DB_select_all("special_com","`sid`='".$_GET['id']."' and `status`=0","`uid`,`integral`");
			if(count($rows)){
				foreach($rows as $val){
					if($val['integral']>0){
 						$IntegralM->company_invtal($val['uid'],$val['integral'],true,"取消专题招聘，退还".$this->config['integral_pricename'],true,2,'integral');
					}
				}
			}
			$this->obj->DB_delete_all("special_com","`sid`='".$_GET['id']."'");
			$nid?$this->layer_msg('专题招聘（ID：'.$_GET['id'].'）删除成功！',9):$this->layer_msg('删除失败！',8);
		}
		if($_GET['del']||$_GET['id']){
    		if(is_array($_GET['del'])){
    			$layer_type=1;
				$del=@implode(',',$_GET['del']);
	    	}else{
	    		$layer_type=0;
	    		$del=$_GET['id'];
	    	}
			$this->obj->DB_delete_all("special","`id` in (".$del.")","");
			$rows=$this->obj->DB_select_all("special_com","`sid` in (".$del.") and `status`=0","`uid`,`integral`");
			if(count($rows)){
				foreach($rows as $val){
					if($val['integral']>0){
						$IntegralM->company_invtal($val['uid'],$val['integral'],true,"取消专题招聘，退还".$this->config['integral_pricename'],true,2,'integral');
					}
				}
			}
			$this->obj->DB_delete_all("special_com","`sid` in (".$del.")","");
			$this->layer_msg("专题(ID:".$del.")删除成功！",9,$layer_type,$_SERVER['HTTP_REFERER']);
    	}else{
			$this->layer_msg("请选择您要删除的信息！",8,1);
    	}
	}
    function savespecial_action(){
       $id=intval($_POST['sid']);
       $uid=intval($_POST['uid']);
       $JobM=$this->MODEL('job');
       $UserInfoM=$this->MODEL('userinfo');
       $IntegralM=$this->MODEL('integral');
       $SpecialM=$this->MODEL('special');
       $info=$SpecialM->GetSpecial(array("id"=>$id,"display"=>1));
       $isapply=$SpecialM->GetComNum(array("uid"=>$uid,"sid"=>$id));
       $applynum =$SpecialM->GetComNum(array("sid"=>$id,'status'=>'1'));
       if($info['com_bm']!='1'){
           echo 1;die;
        }
        if($isapply){
            echo 2;die;
		}
        $time=time();
        $jobnum=$JobM->GetComjobNum(array("uid"=>$uid,"state"=>'1',"`edate`>'".$time."' and `sdate`<'".$time."'"));
		if($info['limit']<=$applynum){
			echo 3;die;
		}
        
        else{
            $rows=$SpecialM->AddSpecialCom(array("sid"=>$id,"uid"=>$uid,'integral'=>$info['integral'],'status'=>'1','time'=>time()));
            if($rows){
               echo 5;die;
            }else{
               echo 6;die;
            } 
        } 

    
    }
    function recommend_action(){
			if($_GET['type']=="rec_display"){
                $nid=$this->obj->DB_update_all("special","`display`='".$_GET['rec']."'","`id`='".$_GET['id']."'");
                $this->MODEL('log')->admin_log("专题名称(ID:".$_GET['id'].")");
			}
            echo $nid?1:0;die;
			
	}
}
?>