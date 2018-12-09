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
class userinfo_model extends model{
    function GetUserinfoOne($Where=array(),$Options=array('usertype'=>null,'field'=>null)){
    	$UserType=$Options['usertype'];
        $TableName='company';
        $TableNameList=array(1=>'resume',2=>'company');
        $Table=$TableNameList[$UserType];
		if($Table==''){$Table=$TableName;}
		$WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_once($Table,$WhereStr,$FormatOptions['field']);
    }
	function UpdateUserStatis($Values=array(),$Where=array(),$Options=array('usertype'=>null)){
    	(int)$Options['usertype']?$UserType=(int)$Options['usertype']:1;
		$tables=array(1=>'member_statis',2=>'company_statis');
        $TableName=$tables[$UserType];
		$WhereStr=$this->FormatWhere($Where);
		$ValuesStr=$this->FormatValues($Values);
        $FormatOptions=$this->FormatOptions($Options);
		return $this->DB_update_all($TableName,$ValuesStr,$WhereStr);
    }
    function GetUserstatisOne($Where=array(),$Options=array('usertype'=>null,'field'=>null)){
    	(int)$Options['usertype']?$UserType=(int)$Options['usertype']:1;
        $TableNameList=array(1=>'member_statis',2=>'company_statis');
        $TableName=$TableNameList[$UserType];
		$WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_once($TableName,$WhereStr,$FormatOptions['field']);
    }
	function GetUserstatisAll($Where=array(),$Options=array('usertype'=>null,'field'=>null)){
    	(int)$Options['usertype']?$UserType=(int)$Options['usertype']:1;
        $TableNameList=array(1=>'member_statis',2=>'company_statis');
        $TableName=$TableNameList[$UserType];
		$WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all($TableName,$WhereStr,$FormatOptions['field'],$FormatOptions['special']);
    }
    function GetRatinginfoOne($Where=array(),$Options=array('field'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);

        return $this->DB_select_once('company_rating',$WhereStr,$FormatOptions['field']);
    }
	function GetRatinginfoAll($Where=array(),$Options=array('field'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all('company_rating',$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }

    
    function GetServiceDetailAll($Where = array(), $Options = array('field' => null)){

        $WhereStr=$this->FormatWhere(array("display"=>1));
        $FormatOptions=$this->FormatOptions( array('field' => null));
        $service = $this->DB_select_all('company_service',$WhereStr.$FormatOptions['order'],$FormatOptions['field']);

        $ids = array();
        foreach($service as $row){
            $ids [] = $row['id'];
        }
        $Where [] = 'and type in (' . implode(',', $ids) . ')';
        $WhereStr = $this->FormatWhere($Where);
        $FormatOptions = $this->FormatOptions($Options);
        return $this->DB_select_all('company_service_detail',$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }

    function GetCompanyPayOne($Where=array(),$Options=array()){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_once('company_pay',$WhereStr,$FormatOptions['field']);
    }
    function GetCompanyPay($Where=array(),$Options=array()){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all('company_pay',$WhereStr,$FormatOptions['field']);
    }
	function DeleteComPay($Where=array()){
		$WhereStr=$this->FormatWhere($Where);
        return $this->DB_delete_all('company_pay',$WhereStr,"");
    }
    function GetUserinfoList($Where=array(),$Options=array('usertype'=>null,'field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        (int)$Options['usertype']?$UserType=(int)$Options['usertype']:1;
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        $TableName='company';
        $TableNameList=array(1=>'resume',2=>'company');
        if(is_numeric($UserType)){
            $Table=$TableNameList[$UserType];
        }
		if($Table==''){$Table=$TableName;}
		return $this->DB_select_all($Table,$WhereStr.$FormatOptions['order'],$FormatOptions['field'],$FormatOptions['special']);
    }

    function  UpdateUserinfo($Options=array('usertype'=>null,'values'=>null),$Where=array()){
		(int)$Options['usertype']?$UserType=(int)$Options['usertype']:1;
        $TableName='company';
        $TableNameList=array(1=>'resume',2=>'company');
        $ValuesStr=$this->FormatValues($Options['values']);
        if(is_numeric($UserType)){
            $Table=$TableNameList[$UserType];
        }
		if($Table==''){$Table=$TableName;}
		$WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options); 
        return $this->DB_update_all($Table,$ValuesStr,$WhereStr);
    }
    function GetMemberOne($Where=array(),$Options=array('field'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_once('member',$WhereStr,$FormatOptions['field']);
    }
    function GetMemberNum($Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        return $this->DB_select_num('member',$WhereStr);
    }
    function GetMemberList($Where=array(),$Options=array('field'=>null,'orderby'=>null,'groupby'=>null,'limit'=>null)){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
		return $this->DB_select_all('member',$WhereStr.$FormatOptions['order'],$FormatOptions['field'],$FormatOptions['special']);
    }
    
    function GetMemberUid($usertype){
    	$list=$this->DB_select_all('member',"`usertype`='".$usertype."' and `status`='1'","`uid`");
    	if(is_array($list))
    	{
    		foreach($list as $v)
    		{
    			$uid[]=$v['uid'];
    		}
    	}
        return @implode(",",$uid);
    }
    function UpdateMember($Values=array(),$Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        $ValuesStr=$this->FormatValues($Values); 
        return $this->DB_update_all('member',$ValuesStr,$WhereStr);
    }
	function UpdateWxlogin($Values=array(),$Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        $ValuesStr=$this->FormatValues($Values); 
        return $this->DB_update_all('wxqrcode',$ValuesStr,$WhereStr);
    }
    function AddMember($Values=array()){
        return $this->insert_into('member',$Values);
    }
    function RegisterMember($Values=array(),$Options=array()){
		(int)$Options['usertype']?$UserType=(int)$Options['usertype']:1;
        $userid=(int)$Options['uid'];
		$udata2['uid'] = $udata['uid'] = $userid;
		$udata['did'] =$udata2['did'] = (int)$this->config['did'];

        if($_POST['moblie_status']){
            $udata2['moblie_status'] = $_POST['moblie_status'];
        }
        if($UserType=="1"){
            $table = "member_statis";
            $table2 = "resume"; 
            $udata2['email'] = $Values['email'];
            $udata2['name'] = $Values['name'];
            $udata2['telphone'] = $Values['moblie'];
        }elseif($UserType=="2"){
            $table = "company_statis";
            $table2 = "company"; 
            $udata2['linkmail'] = $Values['email'];
            $udata2['linkman'] = trim($Values['linkman']);
            $udata2['linktel'] = trim($Values['moblie']);
			$udata2['name'] = $Values['name'];
			$udata2['address'] = $Values['address'];
			$udata2['conid'] = $Values['conid'];
			$udata2['crm_uid'] = $Values['crm_uid'];
			$udata2['crmtime'] = $Values['crmtime'];

			if($this->config['com_status']==0){
				$udata2['r_status']=2;
			}
            $udata=$this->FetchRatingInfo($udata,array("uid"=>$userid,"id"=>$Options['rating']));
        }
        $AddFlag=$this->insert_into($table,$udata);
        $AddFlag=$this->insert_into($table2,$udata2);
        return $AddFlag;
    }
    function FetchRatingInfo($data=array(),$Options=array()){
		if((int)$Options[id]<1){
			$id =$this->config['com_rating'];
		}else{
			$id=(int)$Options[id];
		}
		if(!$Options['uid']){
			$Options['uid'] = $data['uid'];
		}
		$row = $this->GetRatinginfoOne(array('id'=>$id));
        $data=array_merge($data,array('rating'=>$id,'rating_name'=>$row['name'],'rating_type'=>$row['type']));
		if($row['type']==1){
			$data['job_num']=$row['job_num'];
			$data['down_resume']=$row['resume'];
			$data['editjob_num']=$row['editjob_num'];
			$data['breakjob_num']=$row['breakjob_num'];
			$data['invite_resume']=$row['interview'];
			$data['part_num']=$row['part_num'];
			$data['editpart_num']=$row['editpart_num'];
			$data['breakpart_num']=$row['breakpart_num'];
			$data['zph_num']=$row['zph_num'];
			
			
			if($row['service_time']){
				$time=time()+86400*$row['service_time'];
				$data['vip_etime']=$time;
			}else{
				$data['vip_etime']=0;
			}
		}else{
			$time=time()+86400*$row['service_time'];
			$data['vip_etime']=$time;
		}
		$data['integral']=$row['integral_buy'];
		$data['vip_stime']=time();

		return $data;
	}
    function GetMemberstatisOne($Where=array(),$Options=array()){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_once('member_statis',$WhereStr,$FormatOptions['field']);
    }
    function UpdateMemberstatis($Values=array(),$Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        $ValuesStr=$this->FormatValues($Values);
        return $this->DB_update_all('member_statis',$ValuesStr,$WhereStr);
    }
    function AddMemberstatis($Values=array(),$Options=array()){
        $ValuesStr=$this->FormatValues($Values);
        return $this->insert_into('member_statis',$ValuesStr);
    }
    
	
    function GetCompanyCert($Where=array(),$Options=array()){
        $WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_once('company_cert',$WhereStr.$FormatOptions['order'],$FormatOptions['field']);
    }
    function UpdateCompanyCert($Values=array(),$Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        $ValuesStr=$this->FormatValues($Values);
        return $this->DB_update_all('company_cert',$ValuesStr,$WhereStr);
    }
    function AddCompanyCert($Values=array()){
        return $this->insert_into('company_cert',$Values);
    }
    function UpdateResume($Values=array(),$Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        $ValuesStr=$this->FormatValues($Values);
        return $this->DB_update_all('resume',$ValuesStr,$WhereStr);
    }
    function GetResumeList($Where=array(),$Options=array()){
		$WhereStr=$this->FormatWhere($Where);
        $FormatOptions=$this->FormatOptions($Options);
        return $this->DB_select_all('resume',$WhereStr,$FormatOptions['field']);
    }
    function UpdateCompany($Values=array(),$Where=array()){
        $WhereStr=$this->FormatWhere($Where);
        $ValuesStr=$this->FormatValues($Values);
        return $this->DB_update_all('company',$ValuesStr,$WhereStr);
    }
    function InsertReg($table,$Values=array()){
        return $this->insert_into($table,$Values);
    }
    function DeleteMember($UIDS=array(),$Options=array()){
		$del_array=array("member","resume","member_statis","look_resume","look_job","resume_show","userid_msg","userid_job","resume_expect","resume_cert","resume_edu","resume_other","resume_project","resume_skill","resume_training","resume_work","resume_doc","user_resume","resume_show","userid_job","question","msg","attention","down_resume","fav_job","answer","answer_review","evaluate_log","subscribe","subscriberecord","talent_pool");
        if(is_array($UIDS)){  
            foreach($UIDS as $k=>$v){
                delfiledir("../data/data/upload/tel/".intval($v));
            }
            $uids = @implode(",",$UIDS);
            $resume=$this->DB_select_all("resume","`uid` in ($uids) and `photo`<>''","`photo`,`resume_photo`");
            if(is_array($resume)){
                foreach($resume as $val){
                    unlink_pic(".".$val['photo']);
                    unlink_pic(".".$val['resume_photo']);
                }
            }
            
            $show=$this->DB_select_all("resume_show","`uid` in ($uids) and `picurl`<>''","`picurl`");
            if(is_array($show)){
                foreach($show as $val){
                    unlink_pic(".".$val['picurl']);
                }
            }

            foreach($del_array as $value){
                $this->DB_delete_all($value,"`uid` in ($uids)","");
            }
			$this->DB_delete_all("email_msg","`uid` in (".$uids.") or `cuid` in (".$uids.")"," ");
            $this->DB_delete_all("atn","`uid` in ($uids) or `scid` in ($uids)","");
            $this->DB_delete_all("message","`fa_uid` in ($uids)","");
            $this->DB_delete_all("blacklist","`p_uid` in ($uids)","");
            $this->DB_delete_all("report","`p_uid` in ($uids) or `c_uid` in ($uids)","");
            $this->DB_delete_all("member_log","`uid` in ($uids)","");
            $layer_type=1;
        }else{
            $del = intval($UIDS);
            $uids = intval($UIDS);
            delfiledir("../data/upload/tel/".$del);
            $resume=$this->DB_select_once("resume","`uid`='".$del."' and `photo`<>''");
            if(is_array($resume)){
                unlink_pic('.'.$resume['photo']);
                unlink_pic(".".$resume['resume_photo']);
            }
            
            $show=$this->DB_select_all("resume_show","`uid`='".$del."' and `picurl`<>''","`picurl`");
            unlink_pic(".".$show['picurl']);
            foreach($del_array as $value){
                $this->DB_delete_all($value,"`uid`='".$del."'","");
            }
			$this->DB_delete_all("email_msg","`uid`='".$del."' or `cuid`='".$del."'"," ");
            $this->DB_delete_all("atn","`uid`='$del' or `scid`='$del'","");
            $this->DB_delete_all("message","`fa_uid`='".$del."'","");
            $this->DB_delete_all("blacklist","`p_uid`='$del'","");
            $this->DB_delete_all("report","`p_uid`='$del' or `c_uid`='$del'");
            $this->DB_delete_all("member_log","`uid`='$del'");
            $layer_type=0;
        }
        return true;
    }

	function Guwen(){

		
		$day = date("w");

		$gwList = $this->DB_select_all("company_consultant","FIND_IN_SET('".$day."',`status`) ORDER BY `id` ASC","`id`,`crm_uid`");
		if(is_array($gwList)){
			foreach($gwList as $key=>$value){
				$GwId[$value['id']] = $key+1;
				$GwKey[$key] = $value['id'];
				if($value['crm_uid']){
					$crmid[$value['id']] = $value['crm_uid'];
				}
			}
			
			$endGw = $this->DB_select_once("company","`conid`>0 ORDER BY `uid` DESC","`conid`");
			
			
			
			if(!$GwId[$endGw['conid']]){
				$GwId[$endGw['conid']] = 0;
			}
			if($GwId[$endGw['conid']] >= count($gwList)){
				
				$conid = $GwKey[0];
				
			}else{
				
				$conid = $GwKey[$GwId[$endGw['conid']]];
			}
		}
		
		$crmid = $crmid[$conid];
		
		return array('conid'=>$conid,'crmid'=>$crmid);
		
	}

    
    function GetDayAskedNum($Where = array())
    {
        $todayBegin = strtotime(date("Y-m-d"));
        $now = time();
        $Where [] = " and add_time >= {$todayBegin} and add_time <= {$now}";
        $WhereStr = $this->FormatWhere($Where);
        return $this->DB_select_num('question', $WhereStr);
    }

	
	function bindUser($username,$password,$bindinfo){
		
		if($username && $password){
			$userinfo = $this->DB_select_once("member","`username`='".$username."'");
			if(!$userinfo && CheckMoblie($username)){
				$user = $this->GetMemberOne(array("`moblie`='".$username."'"),array('field'=>"username,usertype,password,uid,usertype,salt,status,did,login_date"));
				$binding=$this->GetUserinfoOne(array("`uid`=".$user['uid']." and `moblie_status`=1"),array('usertype'=>$user['usertype'],'field'=>"uid"));
				if(!empty($binding)){
					$userinfo = $user;
				}
			}
			if(!$userinfo&&CheckRegEmail($username)){
				$user = $this->GetMemberOne(array("`email`='".$username."'"),array('field'=>"username,usertype,password,uid,usertype,salt,status,did,login_date"));
 				
				$binding=$this->GetUserinfoOne(array("`uid`=".$user['uid']." and `email_status`=1"),array('usertype'=>$user['usertype'],'field'=>"uid"));
				if(!empty($binding)){
					$userinfo = $user;
				}
			}
			if(!empty($userinfo)){

				$res = md5(md5($password).$userinfo['salt']) == $userinfo['password'] ? true : false;
				if($res){
					if($userinfo['status']=='1'){
					
						
						if($bindinfo['qqid']){
							
							$sqlBind = "`qqid`='".$bindinfo['qqid']."'";
						}

						if($bindinfo['sinaid']){
							
							$sqlBind = "`sinaid`='".$bindinfo['sinaid']."'";
							
						}
						if($bindinfo['wxid']){
							
							$sqlBind = "`wxid`='".$bindinfo['wxid']."',`unionid`='".$bindinfo['wxunionid']."'";
							
						}
						if($sqlBind){

							$this->DB_update_all('member',$sqlBind,"`uid`='".$userinfo['uid']."'");
							$error['info'] = $userinfo;
							$error['error'] = '1';
						}else{
							$error['msg'] = '绑定失败！';
						}
						

						
					}else{
						$error['msg'] = '该账户正在审核中，请稍后再绑定';
					}
				}else{
					$error['msg'] = '密码错误，请重试！';
				}
				

			}else{
				$error['msg'] = '请输入正确的账户名！';
			}
			
		
		}else{
			$error['msg'] = '请输入需要绑定的账户、密码！';
		}
	
		return $error;
	}
}
?>