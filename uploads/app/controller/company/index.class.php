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
class index_controller extends common{
	function index_action(){
		if($this->config['cityid']){
			$_GET['cityid'] = $this->config['cityid'];
		}
        $CacheM=$this->MODEL('cache');
        $CacheList=$CacheM->GetCache(array('city','com','hy','job'));
		$this->yunset($CacheList);
		$this->seo("firm");
		$this->yunset(array("gettype"=>$_SERVER["QUERY_STRING"],"getinfo"=>$_GET));
		
		include PLUS_PATH."keyword.cache.php";
		if(is_array($keyword)){
			foreach($keyword as $k=>$v){
				if($v['type']=='4'&&$v['tuijian']=='1'){
					$comkeyword[]=$v;
				}
			}
		}
		$this->yunset("comkeyword",$comkeyword);
        
		$this->yun_tpl(array('index'));
	}
	function public_action(){
		$M=$this->MODEL("job");
		$UserinfoM=$this->MODEL('userinfo');
		$CompanyM=$this->MODEL('company');
        $sq_num=$M->GetUserJobNum(array('com_id'=>(int)$_GET['id']));
        $this->yunset("sq_num",$sq_num);
        
        $ComMember=$UserinfoM->GetMemberOne(array("uid"=>(int)$_GET['id']),array("field"=>"`source`,`email`,`claim`,`status`"));
		$this->yunset("ComMember",$ComMember);
        $userinfo=$UserinfoM->GetUserinfoOne(array("uid"=>(int)$_GET['id']),array('usertype'=>2));
       
       
       
        $userstatis=$UserinfoM->GetUserstatisOne(array("uid"=>(int)$_GET['id']),array('usertype'=>2));
        $row=array_merge($userinfo,$userstatis);
        if(!is_array($row)){
            $this->ACT_msg($this->config['sy_weburl'],"没有找到该企业！");
        }elseif($ComMember[status]==0&&$row['uid']!=$this->uid){
            session_start();
            if($_SESSION['auid']==''){
                $this->ACT_msg($this->config['sy_weburl'],"该企业正在审核中，请稍后查看！");
            }	
        }elseif($ComMember[status]==3&&$row['uid']!=$this->uid){
            session_start();
            if($_SESSION['auid']==''){
                $this->ACT_msg($this->config['sy_weburl'],"该企业未通过审核！");
            }
        }elseif($row[r_status]==2&&$row['uid']!=$this->uid){
            session_start();
            if($_SESSION['auid']==''){
                $this->ACT_msg($this->config['sy_weburl'],"该企业暂被锁定，请稍后查看！");
            }
        } 
        $CacheM=$this->MODEL('cache');
        $CacheList=$CacheM->GetCache(array('city','com','hy'));
        $this->yunset($CacheList);
        $city_name=$CacheList['city_name'];
        $comclass_name=$CacheList['comclass_name'];
        $industry_name=$CacheList['industry_name'];
       
        	
       
        $city['provinceid']=$row['provinceid'];
        $city['cityid']=$row['cityid'];
        $city['three_cityid']=$row['three_cityid'];
        $row['provinceid']=$city_name[$row['provinceid']];
		$row['cityid']=$city_name[$row['cityid']];
        $row['mun_info']=$comclass_name[$row['mun']];
        $row['pr_info']=$comclass_name[$row['pr']];
        $row['hy_info']=$industry_name[$row['hy']];
        if(!$row['logo'] || !file_exists(str_replace("./",APP_PATH,$row['logo']))){
            $row['logo']=$this->config['sy_weburl']."/".$this->config['sy_unit_icon'];
        }else{
            $row['logo']=str_replace("./",$this->config['sy_weburl']."/",$row['logo']);
        }
        if($row['comqcode']){
        	$row['comqcode']=str_replace("./",$this->config['sy_weburl']."/",$row['comqcode']);
        }
        $welfare = @explode(",",$row['welfare']);
        foreach($welfare as $k=>$v){
        	if(!$v){
        		unset($welfare[$k]);
        	}
        }
        $row['welfare_n']=$welfare;
        $banner=$CompanyM->GetBannerOnes(array('uid'=>(int)$_GET['id']));
        if(!$banner['pic']||!file_exists(str_replace($this->config['sy_weburl'],APP_PATH,'.'.$banner['pic']))){
            $banner['pic']=$this->config['sy_weburl']."/".$this->config['sy_banner'];
        }else{
            $banner['pic']=str_replace("./",$this->config['sy_weburl']."/",$banner['pic']);
        }
		if($row['infostatus']=='2'){
			$row['linkphone']=$row['linktel']=$row['linkmail']=$row['linkqq']='';
		}
		
		$row['content']=str_replace(array("ti<x>tle","“","”"),array("title"," "," "),$row['content']);

		$this->yunset("com",$row);
		$this->yunset("city",$city);
        $this->yunset("banner",$banner);
        $NewsList=$CompanyM->GetNewsAll(array('status'=>1,'uid'=>$_GET['id']));
        $this->yunset('NewsList',$NewsList);
        $ProductList=$CompanyM->GetProductAll(array('status'=>1,'uid'=>$_GET['id']));
        $this->yunset('ProductList',$ProductList);
        $data['infostatus']=$row['infostatus'];
		$data['company_name']=$row['name'];
		$data['company_name_desc']=$row['content'];
		$data['industry_class']=$row['hy_info'];
		
		if($this->config['com_login_link']=='2'){
		    $look_msg="网站没有开放企业联系信息！";
		    $looktype="2";
		}elseif($data['infostatus']==2){
		    $look_msg="企业没有公开联系信息！";
		    $looktype="3";
		}elseif($this->config['com_login_link']=='1'){
			$looktype="1";
		}elseif($this->uid=='' &&$this->username==''){
		 	$look_msg="您还未登录，登录后即可查看联系方式";
		 	$looktype="4";
		}elseif($this->usertype!=1 && (int)$_GET['id']!=$this->uid){
		  	$look_msg="您不是个人用户，不能查看联系方式";
			$looktype="5";
		}elseif($this->config['com_login_link']=="3"){
		   	$looktype="1";
		}elseif($this->config['com_login_link']=="4"){
			$Resume=$this->MODEL('resume');
		    $rows=$Resume->GetResumeExpectNum(array("uid"=>$this->uid));
		  	if($rows<1){
		   		$look_msg="您缺少一份正式的个人简历，暂时无法查看该企业联系方式！";
		    	$looktype="6";
		  	}else{
		    	$looktype="1";
		 	}
		}elseif($this->config['com_login_link']=="5"&& (int)$_GET['id']!=$this->uid){
	         $sendresume=$this->obj->DB_select_num("userid_job","`uid`='".$this->uid."' and `com_id`='".(int)$_GET['id']."'");
	        if($sendresume<1){
	           	$look_msg="投递简历，联系电话任意看"; 
				$looktype="7";
	        }else{
	        	$looktype="1";
	        }
	   	}else{
			$looktype="1";
		}
		$this->yunset("look_msg",$look_msg);
		$this->yunset("looktype",$looktype);
		
		if($this->uid&&$this->usertype=='1'){
		    $AskM=$this->MODEL('ask');
		    $isatn=$AskM->GetAtnOne(array("uid"=>$this->uid,"sc_uid"=>(int)$_GET['id']));
		    $this->yunset("isatn",$isatn);
		}
		$id=(int)$_GET['id'];
		$memeber = $this->obj->DB_select_once("member","`uid`=$id","`login_date`");
		$this->yunset("member",$memeber);
		$invite_resume=$this->obj->DB_select_num("userid_msg","`fid`=$id");
		$de_resume=$this->obj->DB_select_num("userid_job","`com_id`=$id and `is_browse`='1'");
		$des_resume=$this->obj->DB_select_num("userid_job","`com_id`=$id");
		if ($de_resume&&!empty($de_resume)){
		    $pre=round((1-$de_resume/$des_resume)*100);
		}else{
		    $pre=100;
		}
		$time=strtotime("-14 day");
		$limit=2;
		$pageurl=Url('company',array('id'=>(int)$_GET['id'],'c'=>'show','page'=>'{{page}}'));
		$jobs=$this->get_page("company_job","`uid`='".(int)$_GET['id']."' and `r_status`='1' and `status`='0' and `state`='1' order by lastupdate desc",$pageurl,$limit);
		$this->yunset('jobs',$jobs);
		$this->yunset("pre",$pre);
		$M=$this->MODEL('job');
		$num=$M->GetComjobNum(array('uid'=>(int)$_GET['id'],'r_status'=>1,'status'=>'0','state'=>1));
		$this->yunset("num",$num);
		$this->yunset("invite_resume",$invite_resume);
		$this->yunset("id",$id);
		$this->lookcom();
		return $data;
	}
    function show_action(){
    	$data=$this->public_action();
    	$UserinfoM=$this->MODEL('userinfo');
		$JobM=$this->MODEL('job');
    	$CompanyM=$this->MODEL('company');
		$M=$this->MODEL('job');
		$CompanyM->UpdateCompany(array("`hits`=`hits`+1"),array("uid"=>(int)$_GET['id']));
        if($_GET['style']){
        	$urlmsg=Url('company',array('c'=>'msg','id'=>(int)$_GET['id'],'style'=>$_GET['style']));
        }else{
        	$urlmsg=Url('company',array('c'=>'msg','id'=>(int)$_GET['id']));
        }
        $this->yunset("urlmsg",$urlmsg);
        if ($this->usertype==1){
            $userid_job = $JobM->GetUseridJobOne(array('uid'=>$this->uid,'com_id'=>(int)$_GET['id']));
            $this->yunset('userid_job', $userid_job);
        }
        $shows =$this->obj->DB_select_all('company_show',"`uid`='".(int)$_GET['id']."' order by sort desc limit 6");
        $this->yunset('shows', $shows);
        $NewsList =$this->obj->DB_select_all('company_news',"`uid`='".(int)$_GET['id']."' and status = 1 order by id desc limit 6");
        $this->yunset('NewsList', $NewsList);
        $ProductList =$this->obj->DB_select_all('company_product',"`uid`='".(int)$_GET['id']."' and status = 1 order by id desc limit 6");
        $this->yunset('ProductList', $ProductList);
        $Info =$this->obj->DB_select_all('company_job',"`uid`='".(int)$_GET['id']."' and state = 1 and operatime <> ''",'`operatime`,`id`');
        $i=0;$ids=array();
        foreach($Info as $k=>$v){
        	if($v['operatime']){
        		$times+=$v['operatime'];
        		$i++;
        	}
        	$ids[]=$v['id'];
        }
        if($i>0){
        	$times=$times/$i;
        }
        $Infojob =$this->obj->DB_select_all('userid_job',"`com_id`='".(int)$_GET['id']."' and job_id IN (".@implode(',',$ids).")",'`datetime`');
        $i=0;
        foreach($Infojob as $k=>$v){
        	if($v['datetime']){
        		$timejobs+=$v['datetime'];
        		$i++;
        	}
        }
        if($i>0){
        	$timejobs=$timejobs/$i;
        }
        $operatime=$times-$timejobs;
		if(!$times or !$timejobs){
			$times='0';
		}else if($operatime<3600){
			$times='一小时以内';
		}else if($operatime>=3600&&$operatime<86400){
			$times=floor($operatime/3600).'小时';
		}else if($operatime>=86400){
			$times=floor($operatime/86400).'天';
		}
		$row=$UserinfoM->GetUserinfoOne(array('uid'=>(int)$_GET['id']),array('usertype'=>2));
		$rows=$UserinfoM->GetUserstatisOne(array('uid'=>(int)$row['uid']),array("field"=>"`rating`","usertype"=>2));		
		$comrat=$UserinfoM->GetRatinginfoOne(array("id"=>$rows['rating']));
	
		if($comrat['com_pic']&&file_exists(APP_PATH.$comrat['com_pic'])){
			$comrat['com_pic']=$this->config['sy_weburl']."/".$comrat['com_pic'];
		}else{
			$comrat['com_pic']='';
		}
		$this->yunset("comrat",$comrat);
        $this->yunset('operatime', $times);
		$this->data=$data;
		$this->seo("company_index");
    	$this->comtpl("index");
    }

	function lookcom(){
		$CompanyM=$this->MODEL('company');
		$M=$this->MODEL('job');
		$lookuser=$M->GetLookJobList(array('com_id'=>(int)$_GET['id']),array("orderby"=>"datetime","desc"=>"desc",'field'=>'id,uid'));
        if(is_array($lookuser)){
        	foreach ($lookuser as $v){
        		$uids[]=$v['uid'];
        	}
        	$lookcom=$M->GetLookJobList(array('`uid` in ('.pylode(',', $uids).') and `com_id` <> \''.(int)$_GET['id'].'\''),array('limit'=>6,"orderby"=>"datetime","desc"=>"desc",'field'=>'com_id'));
        	foreach ($lookcom as $v){
        		$comids[]=$v['com_id'];
        	}
        	$coms=$CompanyM->GetComList(array('`uid` in ('.pylode(',', $comids).')'),array('field'=>'uid,name,shortname'));
        	foreach ($lookcom as $key=>$val){
        		foreach ($coms as $v){
        			if($val['com_id']==$v['uid']){
        				if($v['shortname']){
        					$lookcom[$key]['comname']=$v['shortname'];
        				}else{
        					$lookcom[$key]['comname']=$v['name'];
        				}
        			}
        		}
        		$jobnum=$M->GetComjobNum(array('uid'=>$val['com_id']));
        		$lookcom[$key]['jobnum']=$jobnum; 
        	}
        }
        $this->yunset("lookcom",$lookcom);	
	}
	function comtpl($tpl){
        if ($_GET['style'] && !preg_match('/^[a-zA-Z]+$/',$_GET['style'])){
            exit();
        }
        if ($_GET['tp'] && !preg_match('/^[a-zA-Z]+$/',$_GET['tp'])){
            exit();
        }
        $UserinfoM=$this->MODEL('userinfo');
        $statis=$UserinfoM->GetUserstatisOne(array("uid"=>(int)$_GET['id']),array('usertype'=>2));
        if($statis['comtpl'] && $statis['comtpl']!="default" && !$_GET['style']){
            $tplurl=$statis['comtpl'];
        }else{
            $tplurl="default";
        }
        if($_GET['style']){
            $tplurl=$_GET['style'];
        } 
		
		$this->yunset(array("com_style"=>$this->config['sy_weburl']."/app/template/company/".$tplurl."/","comstyle"=>TPL_PATH."company/".$tplurl."/")); 
		$this->yuntpl(array('company/'.$tplurl."/".$tpl));
	}
	function productshow_action(){
		$CompanyM=$this->MODEL("company");
        $Where['id']=(int)$_GET['pid'];
		session_start();
        if(!is_numeric($_SESSION['auid']) && (int)$_GET['id']!=$this->uid){
            $Where['status']=1;
        }
        $ProductInfo=$CompanyM->GetProductOne($Where);
		$ProductInfo['body']=str_replace(array("ti<x>tle","“","”"),array("title"," "," "),$ProductInfo['body']);

        $this->yunset('ProductInfo',$ProductInfo);
		$data=$this->public_action();
		$data['company_product']=$ProductInfo['name'];
		$this->data=$data;
	    $this->seo("company_productshow");
		$this->comtpl("productshow");
	}
	function newsshow_action(){
		$CompanyM=$this->MODEL('company');
        $Where['id']=$_GET['nid'];
		session_start();
        if(!is_numeric($_SESSION['auid']) && (int)$_GET['id']!=$this->uid){
            $Where['status']=1;
        }
        $NewsInfo=$CompanyM->GetNewsOne($Where);
		$NewsInfo['body']=str_replace(array("ti<x>tle","“","”"),array("title"," "," "),$NewsInfo['body']);

        $this->yunset('NewsInfo',$NewsInfo);
		$data=$this->public_action();
		$data['company_news']=$NewsInfo['title'];
		$this->data=$data;
	    $this->seo("company_newsshow");
		$this->comtpl("newsshow");
	}
	function prestr_action(){
		
 	    if($_POST['page']){

			$CacheM=$this->MODEL('cache');
			$CacheList=$CacheM->GetCache(array('city','com','hy','job'));
			$this->yunset($CacheList);
			$city_name=$CacheList['city_name'];
			$comclass_name=$CacheList['comclass_name'];
			$industry_name=$CacheList['industry_name'];

	        $M=$this->MODEL('job');
	        $page=intval($_POST['page']);  
	       
			$num=$M->GetComjobNum(array('uid'=>(int)$_POST['id'],'`r_status`<>2','`status`<>1','state'=>1));
			$data['num']=$num;
	        
			$limit=intval($_POST['limit']);  
 			$maxpage=intval(ceil($num/$limit)); 
			if ($page>$maxpage){
	            $page=$maxpage;
	        }
			$start = $page*$limit;

	        if (intval($_POST['updown'])==1){
				$compage=max(1,($page-1));
	        }else if (intval($_POST['updown'])==2){
				$compage=min($maxpage,($page+1));
	        }
			
			if($compage==1){
				$start=0;
			}else if($compage==$maxpage){
				$start=($compage-1)*$limit;
			}else if(intval($_POST['updown'])==1 && $compage==($maxpage-1)){
				$start=($compage-1)*$limit;
			}
			$job_list = $this->obj->DB_select_all("company_job","`uid`='".$_POST['id']."' and `status`=0 and `state`=1 order by lastupdate desc limit $start,$limit");
			
			$joblist="";
			foreach($job_list as $v){
				$v['url']=Url('job',array('c'=>'comapply','id'=>$v[id]));
				$joblist.="<div class=\"firm_post\"><div class=\"fpc_name\"><a href=\"$v[url]\" target=\"_blank\">$v[name]</a></div><div class=\"firm_post_joblist\">";
				
				if($v[minsalary]&&$v[maxsalary]){
					$v[job_salary] =$v[minsalary]."-".$v[maxsalary];
				}elseif($value[minsalary]){
					$v[job_salary] =$v[minsalary]."以上";
				}else{
					$v[job_salary] ="面议";
				}
				
				if($v[job_salary]){
					$joblist.="<span class=\"comshow_job_xz\"><i>$v[job_salary]</i></span>";
				}
				
				if($v[cityid]){
					$v[job_city_two]=$city_name[$v[cityid]];
					$joblist.="<span class=\"comshow_job_city\">$v[job_city_two]</span>";
				}
				if($v['exp']){
					$v[job_exp]=$comclass_name[$v['exp']];
					$joblist.="<span  class=\"comshow_job_jy\">$v[job_exp]经验</span>";
				}
				if($v[edu]){
					$v[job_edu]=$comclass_name[$v[edu]];
					$joblist.="<span class=\"comshow_job_xl\">$v[job_edu]学历</span>";
				}
				$joblist.="</div>";

				if($v[lastupdate]){
					$v[lastupdate] = date('Y-m-d', $v[lastupdate]);
					$joblist.="<div class=\"firm_post_jobtime\">发布时间：$v[lastupdate]</div>";
				}
				$joblist.="<a href=\"$v[url]\" class=\"firm_post_jobbth\">立即查看</a></div>";
			}
			
			$joblist.="
					<div class=\"pages\"> 
						<a href=\"javascript:void(0);\" onclick=\"page('$_POST[id]','$compage','5','1');\">上一页</a> 
						<a href=\"javascript:void(0);\" onclick=\"page('$_POST[id]','$compage','5','2');\">下一页</a>
					</div>";
			$data['joblist']=$joblist;

	        echo json_encode($data);
	    }
	}
}
?>