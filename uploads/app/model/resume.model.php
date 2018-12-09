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
class resume_model extends model{
	
    function AddExpectHits($id){
		if($this->config['sy_job_hits']>100 || !$this->config['sy_job_hits']){
				$this->config['sy_job_hits']=1;
		}
		$hits=rand(1,$this->config['sy_job_hits']);
        $this->DB_update_all("resume_expect","`hits`=`hits`+".$hits,"`id`='".$id."'");
    }
    
    function SaveLookResume($Values=array(),$Where=array()){
        $ValuesStr=$this->FormatValues($Values);

        if($Where){
            $WhereStr=$this->FormatWhere($Where);
            return $this->DB_update_all('look_resume',$ValuesStr,$WhereStr);
        }else{
            return $this->insert_into('look_resume',$Values);
        }
    }
    
    function SelectResume($Where=array(),$field='*'){
    	$WhereStr=$this->FormatWhere($Where);
		return $this->DB_select_once('resume',$WhereStr,$field);
    }
    
    function SelectExpectOne($Where=array(),$field='*'){ 
    	$WhereStr=$this->FormatWhere($Where);
		return $this->DB_select_once('resume_expect',$WhereStr,$field);
    }
	
	function SelectResumeTpl($Where=array(),$field='*'){ 
    	$WhereStr=$this->FormatWhere($Where);
		return $this->DB_select_once('resumetpl',$WhereStr,$field);
    }
    
    function SelectLookResumeOne($Where=array(),$field='*'){
    	$WhereStr=$this->FormatWhere($Where);
		return $this->DB_select_once('look_resume',$WhereStr,$field);
    }
    
    function SelectDownResumeOne($Where=array(),$field="*"){
    	$WhereStr=$this->FormatWhere($Where);
		return $this->DB_select_once('down_resume',$WhereStr,$field='*');
    }
	
    function SelectDownResumeNum($Where=array()){
    	$WhereStr=$this->FormatWhere($Where);
		return $this->DB_select_num('down_resume',$WhereStr);
    }
	
	function ResumeAll($Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
    	$WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all('resume',$WhereStr.$FormatOptions['order'],$FormatOptions['field'],$Options['special']);
    }
	function UpdateResume($Values=array(),$Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        $ValuesStr=$this->FormatValues($Values);
        return $this->DB_update_all('resume',$ValuesStr,$WhereStr);
    }
    
    function GetResumeExpectNum($Where=array()){
    	$WhereStr=$this->FormatWhere($Where);
		return $this->DB_select_num('resume_expect',$WhereStr);
    }
    
	function resume_select($id,$user_jy=''){
		if($user_jy['uid']==''){
			$user_jy=$this->DB_select_once("resume_expect","`id`='".$id."'");
		}
		if($user_jy['uid']){
			$user=$this->DB_select_once("resume","`r_status`<>'2' and `uid`='".$user_jy['uid']."'");
			$thisember=$this->DB_select_once("member","`uid`='".$user_jy['uid']."'");
			if($user['uid']){
				include PLUS_PATH."/city.cache.php";
				include PLUS_PATH."/job.cache.php";
				include PLUS_PATH."/user.cache.php";
				include PLUS_PATH."/industry.cache.php";
				
				
				$user['customjob'] = $user_jy['name'];
				
				if($this->config['user_name']=='1' || !$this->config['user_name']){
					if($user['nametype']=='1'){
						$user['username_n'] = $user['name'];
					}else if($user['nametype']=='2'){
						$user['username_n'] = "NO.".$user_jy['id'];
					}else{
						if($user['sex']==1){
							$user['username_n'] = mb_substr($user['name'],0,1,'utf-8')."先生";
						}else{
							$user['username_n'] = mb_substr($user['name'],0,1,'utf-8')."女士";
						}
					}
				}elseif($this->config['user_name']=='2'){
					$user['username_n'] = "NO.".$user_jy['id'];
				}elseif($this->config['user_name']=='3'){
					if($user['sex']==1){
						$user['username_n'] = mb_substr($user['name'],0,1,'utf-8')."先生";
					}else{
						$user['username_n'] = mb_substr($user['name'],0,1,'utf-8')."女士";
					}
				}elseif($this->config['user_name']=='4'){
					$user['username_n'] = $user['name'];
				}
				
				if(!$user['username_n']){
					$user['username_n'] = $user['name'];
				}
				if($this->config['user_pic']=='1'||$this->config['user_pic']==''){
					if($user['photo']&&$user['phototype']!='1'&&(file_exists(str_replace('./',APP_PATH,'.'.$user['photo'])) || file_exists(str_replace('./',APP_PATH,$user['photo'])))){
						$user['resume_photo']=str_replace("./",$this->config['sy_weburl']."/",$user['photo']);
					}else{
						if($user['sex']==1||$user['sex']=='152'){
							$user['resume_photo']=$this->config['sy_weburl'].'/'.$this->config['sy_member_icon']; 
						}else{
							$user['resume_photo']=$this->config['sy_weburl'].'/'.$this->config['sy_member_iconv']; 
						}
					}
				}elseif($this->config['user_pic']=='2'){
					if($user['photo']&&(file_exists(str_replace('./',APP_PATH,'.'.$user['photo'])) || file_exists(str_replace('./',APP_PATH,$user['photo'])))){
						$user['resume_photo']=str_replace("./",$this->config['sy_weburl']."/",$user['photo']);
					}else{
						if($user['sex']==1||$user['sex']=='152'){
							$user['resume_photo']=$this->config['sy_weburl'].'/'.$this->config['sy_member_icon']; 
						}else{
							$user['resume_photo']=$this->config['sy_weburl'].'/'.$this->config['sy_member_iconv']; 
						}
					}
				}elseif($this->config['user_pic']=='3'){
					if($user['sex']==1||$user['sex']=='152'){
						$user['resume_photo']=$this->config['sy_weburl'].'/'.$this->config['sy_member_icon']; 
					}else{
						$user['resume_photo']=$this->config['sy_weburl'].'/'.$this->config['sy_member_iconv']; 
					}
				}
				if($user['birthday']){
				    $year=date('Y',strtotime(''.$user['birthday'].''));
				    $user['age']=date("Y")-$year;
				}
				
				$user['username']=$thisember['username'];
				
				$user['user_exp']=$userclass_name[$user['exp']];
				$user['user_marriage']=$userclass_name[$user['marriage']];
				$user['useredu']=$userclass_name[$user['edu']];
				$user['jobstatus']=$userclass_name[$user_jy['jobstatus']];
				$user['city_one']=$city_name[$user_jy['provinceid']];
				$user['city_two']=$city_name[$user_jy['cityid']];
				$user['city_three']=$city_name[$user_jy['three_cityid']];
				$user['minsalary']=$user_jy['minsalary'];
				$user['maxsalary']=$user_jy['maxsalary'];
				$user['report']=$userclass_name[$user_jy['report']];
				$user['type']=$userclass_name[$user_jy['type']];
				$user['hy']=$industry_name[$user_jy['hy']];
				$user['lastupdate']=date("Y-m-d",$user_jy['lastupdate']);
				$user['r_name'] = $user_jy['name'];
				$user['doc'] = $user_jy['doc'];
				$user['hits']=$user_jy['hits'];
				$user['dnum']=$user_jy['dnum'];
				
				
				$user['dom_sort']=$user_jy['dom_sort'];
				
				$user['id']=$id;
				if ($user['tag']){
				    $user['arrayTag']=explode(',', $user['tag']);
				}
				
				if ($user_jy['whour']){
					$user['whour'] = $user_jy['whour'];

					if(($user_jy['whour']/12)>=1){
						$whour = floor($user_jy['whour']/12).'年';
					}
					if(($user_jy['whour']%12)>=1){
						$whour .= floor($user_jy['whour']%12).'个月';
					}
					$user['whourInfo'] = $whour;

				}
				if ($user_jy['avghour']){
					$user['avghour'] = $user_jy['avghour'];
					if(($user_jy['avghour']/12)>=1){
						$avghour = floor($user_jy['avghour']/12).'年';
					}
					if(($user_jy['avghour']%12)>=1){
						$avghour .= floor($user_jy['avghour']%12).'个月';
					}
					$user['avghourInfo'] = $avghour;

				}else{
					$user['avghourInfo'] = '1个月内';
				}

				$jy=@explode(",",$user_jy['job_classid']);
				$jy=array_unique($jy);
				if(@is_array($jy)){
					foreach($jy as $v){
						if($job_name[$v]){
							$jobname[]=$job_name[$v];
						}
					}
					$user['jobname']=@implode(",",$jobname);
					$user['expectjob']=$jobname;
				}
				$jc=@explode(",",$user_jy['city_classid']);
				$jc=array_unique($jc);
				if(@is_array($jc)){
				    foreach($jc as $v){
				        if($city_name[$v]){
				            $cityname[]=$city_name[$v];
				        }
				    }
				    $user['cityname']=@implode(",",$cityname);
				    $user['expectcity']=$cityname;
				}
				if($user_jy['doc']){
					$user_doc=$this->DB_select_once("resume_doc","`eid`='".$user['id']."'");
				}else{
					$user_edu=$this->DB_select_all("resume_edu","`eid`='".$user_jy['id']."' order by `sdate` desc");
					$user_training=$this->DB_select_all("resume_training","`eid`='".$user_jy['id']."' order by `sdate` desc");
					$user_work=$this->DB_select_all("resume_work","`eid`='".$user_jy['id']."' order by `sdate` desc");
					$user_other=$this->DB_select_all("resume_other","`eid`='".$user_jy['id']."'");

					$user_skill=$this->DB_select_all("resume_skill","`eid`='".$user_jy['id']."'");
					$user_xm=$this->DB_select_all("resume_project","`eid`='".$user_jy['id']."'  order by `sdate` desc");
					$user_show=$this->DB_select_all("resume_show","`eid`='".$user_jy['id']."'");
				}
			}
			if(is_array($user_training)){
			    foreach($user_training as $k=>$v){
			        $user_training[$k]['content']=str_replace("\r\n", "<br/>", strip_tags($v['content'],"\r\n"));
			    }
			}
			if(is_array($user_other)){
			    foreach($user_other as $k=>$v){
			        $user_other[$k]['content']=str_replace("\r\n", "<br/>", strip_tags($v['content'],"\r\n"));
			    }
			}
			if(is_array($user_work)){
			    foreach($user_work as $k=>$v){
			        $user_work[$k]['content']=str_replace("\r\n", "<br/>", strip_tags($v['content'],"\r\n"));
			    }
			}
			if(is_array($user_xm)){
			    foreach($user_xm as $k=>$v){
			        $user_xm[$k]['content']=str_replace("\r\n", "<br/>", strip_tags($v['content'],"\r\n"));
			    }
			}
			if(is_array($user_skill)){
				foreach($user_skill as $k=>$v){
					$user_skill[$k]['skill_n']=$userclass_name[$v['skill']];
					$user_skill[$k]['ing_n']=$userclass_name[$v['ing']];
					if($v['pic']&&file_exists(str_replace('./',APP_PATH,$v['pic']))){
						$user_skill[$k]['picurl']=str_replace('./' ,$this->config['sy_weburl'].'/',$v['pic']);
					}
				}
				$user_cert=$this->DB_select_all("resume_cert","`eid`='".$user_jy['id']."'");
			}
			if(is_array($user_edu)){
				foreach($user_edu as $k=>$v){
					$user_edu[$k]['education_n']=$userclass_name[$v['education']];		
					$user_edu[$k]['content']=str_replace("\r\n", "<br/>", strip_tags($v['content'],"\r\n"));
				}
			}
			if(is_array($user_show)){
			    foreach($user_show as $k=>$v){
					if(!$v['picurl']||!file_exists(str_replace('./',APP_PATH,$v['picurl']))){
						unset($user_show[$k]);
					}
			    }
			}
			if(is_array($user_show)){
			    foreach($user_show as $k=>$v){
					if($v['picurl']&&file_exists(str_replace('./',APP_PATH,$v['picurl']))){
						$user_show[$k]['picurl']=str_replace('./' ,$this->config['sy_weburl'].'/',$v['picurl']);
					}
			    }
			}
			if($this->usertype=='2'){ 
				$userid_job=$this->DB_select_once("userid_job","`com_id`='".$this->uid."' and `eid`='".$user_jy['id']."'");
				$comstatis = $this->DB_select_once("company_statis","`uid`='".$this->uid."'","`rating`");
				if(!empty($userid_job)&&in_array($comstatis['rating'], @explode(',', $this->config['com_look']))){
					$user['m_status']=1;
				}
			}
			if($this->uid==$user['uid'] && $this->username && $this->usertype==1){
				$user['m_status']=1;
			}
			if($this->uid && $this->username && ($this->usertype==2 || $this->usertype==3)){
				$row=$this->DB_select_once("down_resume","`eid`='".$id."' and comid='".$this->uid."'");
				if(is_array($row)){
					$user['downresume']=1;
					$user['m_status']=1;
					$user['username_n'] = $user['name'];
				}else{
					if($this->usertype=='2'){
						$row=$this->DB_select_once("company_statis","`uid`='".$this->uid."'","down_resume,rating_type");
						
						
						if($row['rating_type']=='2'){
							require_once('company.model.php');
							$dayCheckMax = new company_model($this->db,$this->def,array('uid'=>$this->uid,'username'=>$this->username,'usertype'=>$this->usertype));
							
							$today = strtotime(date('Y-m-d'));
							$nid=$dayCheckMax->comVipDayActionCheck("resume",$this->uid);
								$dnum = $this->DB_select_num("down_resume","`comid`='".$this->uid."' and `downtime` >= '".$today."' and `type` <> 2 ");
							
												
							if($nid!=1){
								$row['down_resume']=0;
							}else{
								$row['down_resume']=$row['down_resume'] - $dnum;
							}
						}
						
					}
					
					if($row['down_resume']>0){
						$user['link_msg']="<a href='javascript:void(0)' onclick=\"isDownResume('$id',{$row['down_resume']},'".Url("ajax",array('c'=>'for_link'))."')\"><span>查看联系方式</span></a>";
						$user['link_wapmsg']="<a href='javascript:void(0)' onclick=\"isDownResume('$id',{$row['down_resume']},'".Url("ajax",array('c'=>'forlink'))."')\"><span>查看联系方式</span></a>";
					}else{
						$user['link_msg']="<a href='javascript:void(0)' onclick=\"for_link('$id','".Url("ajax",array('c'=>'for_link'))."')\"><span>查看联系方式</span></a>";
						$user['link_wapmsg']="<a href='javascript:void(0)' onclick=\"for_link('$id','".Url("ajax",array('c'=>'forlink'))."')\"><span>查看联系方式</span></a>";
					}
				}
			}
			if ($user['uid']==$this->uid||$user['downresume']==1){
			    $user['username_n']=$user['name']; 
			}
			if($this->uid && $this->username && $this->uid==$user_jy['uid']){
				
				$user['diy_status']=1;
			}else{
				
				$user['diy_status']=2;
			}
			
			if($this->uid && $this->username){
				if($this->usertype!=2){
					$user['link_msg']="企业HR才能查看联系方式哦！";
					$user['link_wapmsg']="企业HR才能查看联系方式哦！";
				}
			}
			
			if(!$this->uid && !$this->username){
				
				$usertype=2;
				
				$user['link_msg']="您还没有登录，请点击<a href=\"javascript:void(0);\" onclick=\"showlogin('".$usertype."');\">登录</a>！";
				$user['link_wapmsg']='<div class="com_s_logoin_tip">请登录后查看联系方式</div>
              <a href="'.Url('wap',array(c=>login)).'" class="com_s_logoin">登录</a><a href="'.Url('wap',array('c'=>'register','usertype'=>'2')).'" class="com_s_reg">注册</a>';
			}
            
			if($_GET['look']){
				session_start();

				if(!preg_match("/^\d*$/",$_SESSION['auid'])){return false;}

				$row = $this->DB_select_once("admin_user","`uid`='".$_SESSION['auid']."'");

				if(!empty($row) && $_SESSION['ashell'] == md5($row['username'].$row['password'].$this->md)){
					$user['m_status']=1;
				}else{
					echo "您无权查看！";die;
				}

			}
			if($userid_job['is_browse']=='1'){
				$this->DB_update_all("userid_job","`is_browse`='2'","`id`='".$userid_job['id']."'");
			}
			$user['per']=sprintf('%.2f%%',($user_jy['dnum']/$user_jy['hits'])*100);
						
			$user['description']=str_replace(array("\r","\n"), array("<br/>","<br/>"), strip_tags($user['description'],"\r,\n"));
			$user['user_doc']=$user_doc;
			$user['user_jy']=$user_jy;
			$user['user_edu']=$user_edu;
			$user['user_tra']=$user_training;
			$user['user_work']=$user_work;
			$user['user_other']=$user_other;
			$user['user_xm']=$user_xm;
			$user['user_skill']=$user_skill;
			$user['user_cert']=$user_cert;
			$user['user_show']=$user_show;
		}
		return $user;
	}
    
    function GetFavjobOne($Where=array(),$Options=array('field'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_once('fav_job',$WhereStr,$FormatOptions['field']);
    }
    function GetResumeExpectList($Where=array(),$Options=array()){
		$WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
		return $this->DB_select_all('resume_expect',$WhereStr,$FormatOptions['field'],$FormatOptions['special']);
    }
    function UpdateResumeExpect($Values=array(),$Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        $ValuesStr=$this->FormatValues($Values);
        return $this->DB_update_all('resume_expect',$ValuesStr,$WhereStr);
    }
    
    function AddResumeExpect($Values=array()){

        return $this->insert_into('resume_expect',$Values);
    }
    function DeleteResumeExpect($Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        return $this->DB_delete_all('resume_expect',$WhereStr);
    }
	function GetUserResumeOne($Where=array(),$Options=array()){
		$WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_once('user_resume',$WhereStr,$FormatOptions['field']);
    }
    function GetUserResumeList($Where=array(),$Options=array()){
		$WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all('user_resume',$WhereStr,$FormatOptions['field']);
    }
    function UpdateUserResume($Values=array(),$Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        $ValuesStr=$this->FormatValues($Values);
        return $this->DB_update_all('user_resume',$ValuesStr,$WhereStr);
    }
    function AddUserResume($Values=array()){
        return $this->insert_into('user_resume',$Values);
    }
    function DeleteUserResume($Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        return $this->DB_delete_all('user_resume',$WhereStr);
    }
    function AddResumeShow($Values=array()){
        return $this->insert_into('resume_show',$Values);
    }
    function GetResumeShow($Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_once('resume_show',$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }
    function GetResumeShowList($Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all('resume_show',$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }
    function UpdateResumeShow($Values=array(),$Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        $ValuesStr=$this->FormatValues($Values);
        return $this->DB_update_all('resume_show',$ValuesStr,$WhereStr);
    }
    function GetResumeSkillList($Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all('resume_skill',$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }
    function GetResumeEduList($Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all('resume_edu',$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }
    function GetResumeProjectList($Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all('resume_project',$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }
    function GetResumeCertList($Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all('resume_cert',$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }
    function GetResumeWorkList($Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all('resume_work',$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }
    function GetResumeTrainingList($Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all('resume_training',$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }
    function GetResumeOtherList($Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all('resume_other',$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }
    function DeleteResumeSkill($Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        return $this->DB_delete_all('resume_skill',$WhereStr);
    }
    function DeleteResumeEdu($Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        return $this->DB_delete_all('resume_edu',$WhereStr);
    }
    function DeleteResumeProject($Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        return $this->DB_delete_all('resume_project',$WhereStr);
    }
    function DeleteResumeCert($Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        return $this->DB_delete_all('resume_cert',$WhereStr);
    }
    function DeleteResumeWork($Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        return $this->DB_delete_all('resume_work',$WhereStr);
    }
    function DeleteResumeTraining($Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        return $this->DB_delete_all('resume_training',$WhereStr);
    }
    function DeleteResumeOther($Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        return $this->DB_delete_all('resume_other',$WhereStr);
    }
    function DeleteLookResume($Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        return $this->DB_delete_all('look_resume',$WhereStr);
    }
    function AddEntrustRecord($Values=array()){
        return $this->insert_into('user_entrust_record',$Values);
    }
    function GetEntrustRecordList($Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all('user_entrust_record',$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }
    function GetEntrustRecordOne($Where=array(),$Options=array()){
		$WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_once('user_entrust_record',$WhereStr,$FormatOptions['field']);
    }
    function DeleteEntrustRecord($Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        return $this->DB_delete_all('user_entrust_record',$WhereStr);
    }
    function DeleteDownResume($Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        return $this->DB_delete_all('down_resume',$WhereStr);
    }
	function AddResume($Table,$Values=array(),$Where=array()){
		if($Where){
			$WhereStr=$this->FormatWhere($Where);
			$ValuesStr=$this->FormatValues($Values);
            return $this->DB_update_all($Table,$ValuesStr,$WhereStr);
		}else{
			return $this->insert_into($Table,$Values);
		}
	}
	function UpWour($eid,$uid){
		
		$workList = $this->DB_select_all("resume_work","`eid`='".$eid."' AND `uid`='".$uid."'","`sdate`,`edate`");
		$whour = 0;$hour=array();
		if(is_array($workList)){
			foreach($workList as $value){
				
				if ($value['edate']){
					$workTime = ceil(($value['edate']-$value['sdate'])/(30*86400));
				}else{
					$workTime = ceil((time()-$value['sdate'])/(30*86400));
				}
				$hour[] = $workTime;
				$whour += $workTime;
			}
			$avghour = ceil($whour/count($hour));
		

			$this->DB_update_all("resume_expect","`whour`='".$whour."',`avghour`='".$avghour."'","`id`='".$eid."' AND `uid`='".$uid."'");
		}
		
		
		
	}
	function GetResumeAbouts($Table,$Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all($Table,$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }
	function GetResumeAbout($Table,$Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_once($Table,$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }
    function SelectTalentPool($Where=array(),$field="*"){
    	$WhereStr=$this->FormatWhere($Where);
		return $this->DB_select_once('talent_pool',$WhereStr,$field='*');
    }
	function GetUserMsg($Where=array()){
    	$WhereStr=$this->FormatWhere($Where);
		return $this->DB_select_once('userid_msg',$WhereStr,$field='*');
    }
	function GetUserMsgNums($Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all("userid_msg",$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }
    
    function TemporaryResume($Values=array()){
        return $this->insert_into('temporary_resume',$Values);
    }
	
    function SelectTemporaryResume($Where=array()){
    	$WhereStr=$this->FormatWhere($Where);
		return $this->DB_select_once('temporary_resume',$WhereStr);
    }
    
    function DelTemporaryResume($Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        return $this->DB_delete_all('temporary_resume',$WhereStr);
    }
	
	function SelectUserIdMsgNum($Where=array()){
		$WhereStr=$this->FormatWhere($Where);
		return $this->DB_select_num('userid_msg',$WhereStr);
	}
	
	function CheckAddExpect($infoDate,$expectDate){
		$infoDate = array(
				"name"		=>	$_POST['uname'],
				"sex"		=>	$_POST['sex'],
				"birthday"	=>	$_POST['birthday'],
				"edu"		=>	$_POST['edu'],
				"exp"		=>	$_POST['exp'],
				"living"	=>	$_POST['living'],
				"lastupdate"=>time()
			);
	}
	function complete($user_resume=array()){
		$numresume=20;
		if($user_resume[expect]!="0"){
			$numresume=$numresume+35;
		}
		if($user_resume[skill]!="0"){
			$numresume=$numresume+10;
		}
		if($user_resume[work]!="0"){
			$numresume=$numresume+10;
		}
		if($user_resume[project]!="0"){
			$numresume=$numresume+8;
		}
		if($user_resume[edu]!="0"){
			$numresume=$numresume+10;
		}
		if($user_resume[training]!="0"){
			$numresume=$numresume+7;
		}
		$this->update_once('resume_expect',array('integrity'=>$numresume),array('id'=>$user_resume['eid']));
		return $numresume;
	}
}
?>