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
class jfdk_model extends model{

    function buyAutoJob($data){
        if(!$data['uid']){
            $data['uid']=$this->uid;
        }
        if(!$data['username']){
            $data['username']=$this->username;
        }
        if(!$data['usertype']){
            $data['usertype']=$this->usertype;
        }
        if($this->config['com_integral_online']!=2){
            
            if($data['jobautoids'] && ($data['rdays'] || $data['crdays']) ){
                
                $jobautoids=@explode(',',$data['jobautoids']);
                $jobautoids = pylode(',',$jobautoids);
                $autodays= intval($data['crdays']);
                if($autodays < 1){
                    $autodays= intval($_POST['rdays']);
                }
                $autotype = 1;
                
                if($autodays > 0 && $jobautoids){
                    
                    
                    $jobs = $this->DB_select_all("company_job","`uid`='".$data['uid']."' and `id` in(".$jobautoids.")","`autotime`,`id`");
                    
                    if(empty($jobs)){
                        
                        $return['error'] = '请选择正确的刷新职位！';
                        
                    } else {
                        
                        $jobnum = $this->DB_select_num("company_job","`uid`='".$data['uid']."' and `id` in(".$jobautoids.")");
                        $dkjf= $autodays * $this->config['job_auto'] * $jobnum * $this->config['integral_proportion'];
                        
                        $statis = $this->DB_select_once("company_statis","`uid`='".$data['uid']."'","`integral`");
                        
                        if($statis['integral']>=$dkjf){
                            
                            
                            $autoJob = $this->DB_select_all("company_job","`uid`='".$data['uid']."' AND `id`in (".$jobautoids.") ","`autotime`,`id`");
                            if(!empty($autoJob)){
                                $lastautotime=0;
                                foreach ($autoJob as $v){
                                    if ($v['autotime'] >= time()) {
                                        $autotime = $v['autotime'] + $autodays*86400;
                                    } else {
                                        $autotime = time() + $autodays*86400;
                                    }
                                    if ($autotime > $lastautotime) {
                                        $lastautotime = $autotime;
                                    }
                                    $status=$this->update_once('company_job',array('autotime'=>$autotime,'autotype'=>'1'),"`uid`='".$data['uid']."' and `id`='".$v['id']."'");
                                }
                                $this->update_once('company_statis',array('autotime'=>$lastautotime),array('uid'=>$data['uid']));
                                if($status){
                                    require_once('integral.model.php');
                                    $integral = new integral_model($this->db,$this->def,array('uid'=>$data['uid'],'username'=>$data['username'],'usertype'=>$data['usertype']));
                                    $integral->company_invtal($data['uid'],$dkjf,false,"购买自动刷新职位",true,2,'integral',12);
                                    $return['status']='1';
                                    $return['msg']='自动刷新职位购买成功';
                                }
                            }
                        }else{
                            if($this->config['com_integral_online']==3){
                                $return['error'] = '积分不足，请先充值积分！';
                                $return['url'] = $this->config['sy_weburl']."/member/index.php?c=pay&type=integral";
                            }else{
                                $return['error'] = '积分不足，请正确输入抵扣积分！';
                            }
                        }
                        
                    }
                }else{
                    $return['error'] = '请正确选择自动刷新职位以及刷新天数！';
                }
            } else {
                $return['error'] = '参数填写错误，请重新设置！';
            }
        }else{
            $return['error'] = '系统目前只支持金额消费！';
        }
        return $return;
    }

    function buyZdJob($data){
        if(!$data['uid']){
            $data['uid']=$this->uid;
        }
        if(!$data['username']){
            $data['username']=$this->username;
        }
        if(!$data['usertype']){
            $data['usertype']=$this->usertype;
        }
        if($this->config['com_integral_online']!=2){
            if($data['zdjobid'] && ($data['xsdays'] || $data['cxsdays'])){
                
                $jobid = $data['zdjobid'];
                
                
                $xsdays=intval($data['xsdays']);
                if($xsdays==''&&$data['cxsdays']){
                    $xsdays=intval($data['cxsdays']);
                }
                if($xsdays<1){
                    $xsdays=1;
                }
                if($xsdays > 0 && $jobid){
                    
                    
                    $job = $this->DB_select_once("company_job","`uid`='".$data['uid']."' and `id` ='".$jobid."'");
                    
                    if(empty($job)){
                        
                        $return['error'] = '请选择正确的职位置顶！';
                        
                    }else {
                        $dkjf= $xsdays * $this->config['integral_job_top'] * $this->config['integral_proportion'];
                        $statis = $this->DB_select_once("company_statis","`uid`='".$data['uid']."'","`integral`");
                        
                        if($statis['integral']>=$dkjf){
                            
                            $xsjob = $this->DB_select_once("company_job","`id`='".$jobid."'","name,xsdate");
                            if(!empty($xsjob)){
                                if ($xsjob['xsdate'] > time()){
                                    $xsdate = $xsjob['xsdate']+$xsdays*86400;
                                }else {
                                    $xsdate = strtotime("+".$xsdays." day");
                                }
                                $status=$this->update_once("company_job",array('xsdate'=>$xsdate),"`uid`='".$data['uid']."' and `id`='".$jobid."'");
                                
                                if($status){
                                    require_once('integral.model.php');
                                    $integral = new integral_model($this->db,$this->def,array('uid'=>$data['uid'],'username'=>$data['username'],'usertype'=>$data['usertype']));
                                    $integral->company_invtal($data['uid'],$dkjf,false,"购买职位置顶",true,2,'integral',12);
                                    $return['status']='1';
                                    $return['msg']='职位置顶购买成功';
                                }
                            }
                        }else{
                            if($this->config['com_integral_online']==3){
                                $return['error'] = '积分不足，请先充值积分！';
                                $return['url'] = $this->config['sy_weburl']."/member/index.php?c=pay&type=integral";
                            }else{
                                $return['error'] = '积分不足，请正确输入抵扣积分！';
                            }
                        }
                    }
                    
                }else{
                    $return['error'] = '请正确选择职位置顶以及置顶的天数！';
                }
            } else {
                $return['error'] = '参数填写错误，请重新设置！';
            }
        }else{
            $return['error'] = '系统目前只支持金额消费！';
        }
        return $return;
    }

    function buyRecJob($data){
        if(!$data['uid']){
            $data['uid']=$this->uid;
        }
        if(!$data['username']){
            $data['username']=$this->username;
        }
        if(!$data['usertype']){
            $data['usertype']=$this->usertype;
        }
        if($this->config['com_integral_online']!=2){
            if($data['recjobid'] && ($data['recdays'] || $data['crecdays'])){
                
                $jobid = $data['recjobid'];
                
                
                $recdays=intval($data['recdays']);
                if($recdays==''&&$data['crecdays']){
                    $recdays=intval($data['crecdays']);
                }
                if($recdays<1){
                    $recdays=1;
                }
                if($recdays > 0 && $jobid){
                    
                    
                    $job = $this->DB_select_once("company_job","`uid`='".$data['uid']."' and `id` ='".$jobid."'");
                    
                    if(empty($job)){
                        $return['error'] = '请选择正确的职位推荐！';
                    }else {
                        $dkjf= $recdays * $this->config['com_recjob'] * $this->config['integral_proportion'];
                        $statis = $this->DB_select_once("company_statis","`uid`='".$data['uid']."'","`integral`");
                        
                        if($statis['integral']>=$dkjf){
                            
                            $recjob = $this->DB_select_once("company_job","`id`='".$jobid."' ","`name`,`rec_time`");
                            
                            if(!empty($recjob)){
                                if ($recjob['rec_time'] > time()){
                                    $rec_time = $recjob['rec_time']+$recdays * 86400;
                                }else {
                                    $rec_time = time()+$recdays * 86400;
                                }
                                $status=$this->update_once("company_job",array('rec_time'=>$rec_time,'rec'=>'1'),"`uid`='".$data['uid']."' and `id`='".$jobid."'");
                                
                                if($status){
                                    require_once('integral.model.php');
                                    $integral = new integral_model($this->db,$this->def,array('uid'=>$data['uid'],'username'=>$data['username'],'usertype'=>$data['usertype']));
                                    $integral->company_invtal($data['uid'],$dkjf,false,"购买职位推荐",true,2,'integral',12);
                                    $return['status']='1';
                                    $return['msg']='职位推荐购买成功';
                                }
                            }
                        }else{
                            if($this->config['com_integral_online']==3){
                                $return['error'] = '积分不足，请先充值积分！';
                                $return['url'] = $this->config['sy_weburl']."/member/index.php?c=pay&type=integral";
                            }else{
                                $return['error'] = '积分不足，请正确输入抵扣积分！';
                            }
                        }
                    }
                }else{
                    $return['error'] = '请正确选择职位推荐以及推荐的时长！';
                }
                
            } else {
                $return['error'] = '参数填写错误，请重新设置！';
            }
        }else{
            $return['error'] = '系统目前只支持金额消费！';
        }
        return $return;
    }

    function buyUrgentJob($data){
        if(!$data['uid']){
            $data['uid']=$this->uid;
        }
        if(!$data['username']){
            $data['username']=$this->username;
        }
        if(!$data['usertype']){
            $data['usertype']=$this->usertype;
        }
        if($this->config['com_integral_online']!=2){
            if($data['ujobid'] && ($data['udays'] || $data['cudays'])){
                
                $jobid = $data['ujobid'];
                
                
                $udays=intval($data['udays']);
                if($udays==''&&$data['cudays']){
                    $udays=intval($data['cudays']);
                }
                if($udays<1){
                    $udays=1;
                }
                if($udays > 0 && $jobid){
                    
                    
                    $job = $this->DB_select_once("company_job","`uid`='".$data['uid']."' and `id` ='".$jobid."'");
                    
                    if(empty($job)){
                        
                        $return['error'] = '请选择正确的职位！';
                        
                    }else {
                        
                        $dkjf= $udays * $this->config['com_urgent'] * $this->config['integral_proportion'];
                        
                        $statis = $this->DB_select_once("company_statis","`uid`='".$data['uid']."'","`integral`");
                        
                        if($statis['integral']>=$dkjf){
                            $ujob = $this->DB_select_once("company_job","`id`='".$jobid."' ","`name`,`urgent_time`");
                            if(!empty($ujob)){
                                if ($ujob['urgent_time'] > time()){
                                    $urgent_time = $ujob['urgent_time']+$udays*86400;
                                }else {
                                    $urgent_time = strtotime("+".$udays." day");
                                }
                                $status=$this->update_once("company_job",array('urgent_time'=>$urgent_time,'urgent'=>'1'),"`uid`='".$data['uid']."' and `id`='".$jobid."'");
                                if($status){
                                    require_once('integral.model.php');
                                    $integral = new integral_model($this->db,$this->def,array('uid'=>$data['uid'],'username'=>$data['username'],'usertype'=>$data['usertype']));
                                    $integral->company_invtal($data['uid'],$dkjf,false,"购买紧急职位",true,2,'integral',12);
                                    $return['status']='1';
                                    $return['msg']='紧急职位购买成功';
                                }
                            }
                        }else{
                            if($this->config['com_integral_online']==3){
                                $return['error'] = '积分不足，请先充值积分！';
                                $return['url'] = $this->config['sy_weburl']."/member/index.php?c=pay&type=integral";
                            }else{
                                $return['error'] = '积分不足，请正确输入抵扣积分！';
                            }
                        }
                    }
                }else{
                    $return['error'] = '请正确选择职位以及紧急招聘天数！';
                }
                
            } else {
                $return['error'] = '参数填写错误，请重新设置！';
            }
        }else{
            $return['error'] = '系统目前只支持金额消费！';
        }
        
        return $return;
    }

	function buyVip($data){
	    if(!$data['uid']){
	        $data['uid']=$this->uid;
	    }
	    if(!$data['username']){
	        $data['username']=$this->username;
	    }
	    if(!$data['usertype']){
	        $data['usertype']=$this->usertype;
	    }
 		if($this->config['com_integral_online']!=2){
 			
 			if($data['id']){
				$id = intval($data['id']);
				
				$ratinginfo = $this->DB_select_once("company_rating"," `id` = {$id} ");
				
				if($data['usertype']=="2"){
					$statis = $this->DB_select_once("company_statis"," `uid` = {$data['uid']} ");
				}				
				
				if(empty($ratinginfo)){
					$return['error'] = '该会员套餐已下架，请重新选择！';
				}else {
					
					if($ratinginfo['time_start'] < time() && $ratinginfo['time_end'] > time()){
						$price = $ratinginfo['yh_price'] * $this->config['integral_proportion'];
					}else{
						$price = $ratinginfo['service_price'] * $this->config['integral_proportion'];
					}
					
					if($statis['integral']>=$price){
						
						require_once('rating.model.php');
						$rating = new rating_model($this->db,$this->def,array('uid'=>$data['uid'],'username'=>$data['username'],'usertype'=>$data['usertype']));
						
						if($data['usertype']=='2'){
							$value=$rating->rating_info($id,$data['uid']);
							$status=$this->DB_update_all("company_statis",$value,"`uid`='".$data['uid']."'");
						}
						
						
						if($data['orderid']){
							$this->DB_delete_all("company_order","`id`='".$data['orderid']."' and `uid`='".$data['uid']."'");
							
														
						}
						$this->DB_update_all("company_job","`rating`='".$id."'","`uid`='".$data['uid']."'");
						
						require_once('integral.model.php');
						$integral = new integral_model($this->db,$this->def,array('uid'=>$data['uid'],'username'=>$data['username'],'usertype'=>$data['usertype']));
						$integral->company_invtal($data['uid'],$price,false,"购买会员",true,2,'integral',27);
						
						$return['status']='1';
						$return['msg']='会员购买成功';
					}else{
						if($this->config['com_integral_online']==3){
							$return['error'] = '积分不足，请先充值积分！';
							$return['url'] = $this->config['sy_weburl']."/member/index.php?c=pay&type=integral";
						}else{
							$return['error'] = '积分不足，请正确输入抵扣积分！';
						}
						
					}
 				}
			} else {
				$return['error'] = '参数填写错误，请重新设置！';
			}
		}else{
			$return['error'] = '系统目前只支持金额消费！';
		}
		return $return;
	
	}
	function buyPackOrder($data){
	    if(!$data['uid']){
	        $data['uid']=$this->uid;
	    }
	    if(!$data['username']){
	        $data['username']=$this->username;
	    }
	    if(!$data['usertype']){
	        $data['usertype']=$this->usertype;
	    }
	    if($this->config['com_integral_online']!=2){
	        
	        if($data['tcid']){
	            
	            $tid = intval($data['tcid']);
	            
	            if($tid){
	                
	                
	                $service = $this->DB_select_once("company_service_detail"," `id` = {$tid} ");
	                
	                if(empty($service)){
	                    
	                    $return['error'] = '请选择正确增值套餐！';
	                    
	                }else {
	                    
	                    $statis = $this->DB_select_once("company_statis","`uid`='".$data['uid']."'","`integral`,`rating`,`vip_etime`");
	                    
	                    if($statis['vip_etime'] < time()){
	                        
	                        $return['error'] = '您的会员已到期，请先购买会员！';
	                        $return['url'] = $this->config['sy_weburl']."/member/index.php?c=right";
	                    }else{
	                        
	                        $rating = $this->DB_select_once("company_rating","`id` = {$statis['rating']}","`service_discount`");
	                        
	                        if($rating['service_discount']){
	                            
	                            $discount = intval($rating['service_discount']);
	                            $price = $service['service_price'] * $discount * 0.01 ;
	                        }else{
	                            
	                            $price = $service['service_price'];
	                        }
	                        
	                        $dkjf= $price * $this->config['integral_proportion'];
	                        
	                        if($statis['integral']>=$dkjf){
	                            
	                            $value.="`job_num`=`job_num`+'".$service['job_num']."',";
	                            $value.="`down_resume`=`down_resume`+'".$service['resume']."',";
	                            $value.="`invite_resume`=`invite_resume`+'".$service['interview']."',";
	                            $value.="`breakjob_num`=`breakjob_num`+'".$service['breakjob_num']."',";
	                            $value.="`part_num`=`part_num`+'".$service['part_num']."',";
	                            $value.="`breakpart_num`=`breakpart_num`+'".$service['breakpart_num']."',";
	                            $value.="`all_pay`=`all_pay`+ {$price},";
	                            $status=$this->DB_update_all('company_statis',$value,"`uid`='".$data['uid']."'");
	                            
	                            if($status){
	                                require_once('integral.model.php');
	                                $integral = new integral_model($this->db,$this->def,array('uid'=>$data['uid'],'username'=>$data['username'],'usertype'=>$data['usertype']));
	                                $integral->company_invtal($data['uid'],$dkjf,false,"购买增值包",true,2,'integral',12);
	                                
	                                $return['status']='1';
	                                $return['msg']='增值包购买成功';
	                            }
	                        }else{
	                            if($this->config['com_integral_online']==3){
	                                $return['error'] = '积分不足，请先充值积分！';
	                                $return['url'] = $this->config['sy_weburl']."/member/index.php?c=pay&type=integral";
	                            }else{
	                                $return['error'] = '积分不足，请正确输入抵扣积分！';
	                            }
	                        }
	                    }
	                }
	            }else{
	                $return['error'] = '请正确选择增值服务套餐！';
	            }
	            
	        } else {
	            $return['error'] = '参数填写错误，请重新设置！';
	        }
	        
	    }else{
	        $return['error'] = '系统目前只支持金额消费！';
	    }
	    
	    return $return;
	}

	function buyRefreshJob($data){
	    if(!$data['uid']){
	        $data['uid']=$this->uid;
	    }
	    if(!$data['username']){
	        $data['username']=$this->username;
	    }
	    if(!$data['usertype']){
	        $data['usertype']=$this->usertype;
	    }
	    if($this->config['com_integral_online']!=2){
	        
	        if($data['sxjobid']){
	            
	            $sxjobid=@explode(',',$data['sxjobid']);
	            $sxjobid = pylode(',',$sxjobid);
	            
	            if($sxjobid){
	                
	                $statis = $this->DB_select_once("company_statis","`uid`='".$data['uid']."'","`integral`, `breakjob_num`");
	                
	                $breakjob_num = intval($statis['breakjob_num']);
	                
	                
	                $jobs = $this->DB_select_all("company_job","`uid`='".$data['uid']."' and `id` in (".$sxjobid.") ","`id`,`name`");
	                
	                if(empty($jobs)){
	                    
	                    $return['error'] = '请选择正确的职位刷新！';
	                    
	                }else {
	                    
	                    $jobnum = $this->DB_select_num("company_job","`uid`='".$data['uid']."' and `id` in(".$sxjobid.")");
	                    
	                    if($breakjob_num){
	                        $jobnum = $jobnum - $breakjob_num;
	                    }
	                    
	                    $dkjf= $jobnum * $this->config['integral_jobefresh'] *  $this->config['integral_proportion'];
	                    
	                    if($statis['integral']>=$dkjf){
	                        
	                        
	                        $sxJob = $this->DB_select_once("company_job"," `uid`='".$data['uid']."' AND `id` in (".$sxjobid.") ","`lastupdate`,`id`");
	                        if(!empty($sxJob)){
	                            foreach ($sxJob as $v){
	                                $status=$this->DB_update_all('company_job'," `lastupdate` ='".time()."' "," `id` in(".$sxjobid.") " );
	                            }
	                            $this->update_once('company',array('lastupdate'=>time()),array('uid'=>$data['uid']));
	                            if($breakjob_num){
	                                $this->update_once('company_statis',array('breakjob_num'=>'0'),array('uid'=>$data['uid']));
	                            }
	                            
	                            if($status){
	                                require_once('integral.model.php');
	                                $integral = new integral_model($this->db,$this->def,array('uid'=>$data['uid'],'username'=>$data['username'],'usertype'=>$data['usertype']));
	                                $integral->company_invtal($data['uid'],$dkjf,false,"购买刷新职位",true,2,'integral',12);
	                                
	                                if($jobnum == 1){
	                                    $this->member_log("刷新职位《".$jobs[0]['name']."》",1,4);
	                                }else{
	                                    $this->member_log("批量刷新职位",1,4);
	                                }
	                                
	                                $return['status']='1';
	                                $return['msg']='职位刷新成功';
	                            }
	                        }
	                    }else{
	                        if($this->config['com_integral_online']==3){
	                            $return['error'] = '积分不足，请先充值积分！';
	                            $return['url'] = $this->config['sy_weburl']."/member/index.php?c=pay&type=integral";
	                        }else{
	                            $return['error'] = '积分不足，请正确输入抵扣积分！';
	                        }
	                    }
	                }
	            }else{
	                $return['error'] = '请先选择职位！';
	            }
	        } else {
	            $return['error'] = '参数填写错误，请重新设置！';
	        }
	        
	    }else{
	        
	        $return['error'] = '系统目前只支持金额消费！';
	        
	    }
	    return $return;
	}
	
	function buyRefreshPart($data){
	    if(!$data['uid']){
	        $data['uid']=$this->uid;
	    }
	    if(!$data['username']){
	        $data['username']=$this->username;
	    }
	    if(!$data['usertype']){
	        $data['usertype']=$this->usertype;
	    }
	    if($this->config['com_integral_online']!=2){
	        
	        if($data['sxpartid']){
	            
	            $sxpartid=@explode(',',$data['sxpartid']);
	            $sxpartid = pylode(',',$sxpartid);
	            
	            if($sxpartid){
	                $statis = $this->DB_select_once("company_statis","`uid`='".$data['uid']."'","`integral`,`breakpart_num`");
	                $breakpart_num = intval($statis['breakpart_num']);
	                
	                
	                $parts = $this->DB_select_all("partjob","`uid`='".$data['uid']."' and `id` in (".$sxpartid.") ","`id`,`name`");
	                
	                if(empty($parts)){
	                    $return['error'] = '请选择正确的职位刷新！';
	                }else {
	                    $partnum = $this->DB_select_num("partjob","`uid`='".$data['uid']."' and `id` in(".$sxpartid.")");
	                    
	                    if($breakpart_num){
	                        $partnum = $partnum - $breakpart_num;
	                    }
	                    
	                    $dkjf= $partnum * $this->config['integral_partjobefresh'] *  $this->config['integral_proportion'];
	                    
	                    
	                    
	                    if($statis['integral']>=$dkjf){
	                        
	                        
	                        $sxpJob = $this->DB_select_once("partjob"," `uid`='".$data['uid']."' AND `id` in (".$sxpartid.") ","`lastupdate`,`id`");
	                        if(!empty($sxpJob)){
	                            foreach ($sxpJob as $v){
	                                $status=$this->DB_update_all('partjob'," `lastupdate` ='".time()."' "," `id` in(".$sxpartid.") " );
	                            }
	                            if($breakpart_num){
	                                $this->update_once('company_statis',array('breakpart_num'=>'0'),array('uid'=>$data['uid']));
	                            }
	                            
	                            
	                            if($status){
	                                require_once('integral.model.php');
	                                $integral = new integral_model($this->db,$this->def,array('uid'=>$data['uid'],'username'=>$data['username'],'usertype'=>$data['usertype']));
	                                $integral->company_invtal($data['uid'],$dkjf,false,"购买刷新兼职",true,2,'integral',12);
	                                
	                                if($partnum == 1){
	                                    $this->member_log("刷新兼职《".$parts[0]['name']."》",9,4);
	                                }else{
	                                    $this->member_log("批量刷新职位",9,4);
	                                }
	                                
	                                $return['status']='1';
	                                $return['msg']='兼职刷新成功';
	                            }
	                        }
	                    }else{
	                        if($this->config['com_integral_online']==3){
	                            $return['error'] = '积分不足，请先充值积分！';
	                            $return['url'] = $this->config['sy_weburl']."/member/index.php?c=pay&type=integral";
	                        }else{
	                            $return['error'] = '积分不足，请正确输入抵扣积分！';
	                        }
	                    }
	                }
	            }else{
	                $return['error'] = '请正确选择的职位刷新！';
	            }
	        } else {
	            $return['error'] = '参数填写错误，请重新设置！';
	        }
	    }else{
	        $return['error'] = '系统目前只支持金额消费！';
	    }
	    return $return;
	}

	function buyRecPart($data){
	    if(!$data['uid']){
	        $data['uid']=$this->uid;
	    }
	    if(!$data['username']){
	        $data['username']=$this->username;
	    }
	    if(!$data['usertype']){
	        $data['usertype']=$this->usertype;
	    }
	    if($this->config['com_integral_online']!=2){
	        
	        if($data['recpartid'] && ($data['recpartdays'] || $data['crecpartdays'])){
	            
	            $partid = $data['recpartid'];
	            
	            
	            $recdays=intval($data['recpartdays']);
	            if($recdays==''&&$data['crecpartdays']){
	                $recdays=intval($data['crecpartdays']);
	            }
	            if($recdays<1){
	                $recdays=1;
	            }
	            if($recdays > 0 && $partid){
	                
	                
	                $part = $this->DB_select_once("partjob","`uid`='".$data['uid']."' and `id` ='".$partid."'");
	                
	                if(empty($part)){
	                    $return['error'] = '请选择正确的职位推荐！';
	                }else {
	                    $dkjf= $recdays * $this->config['com_recpartjob'] * $this->config['integral_proportion'];
	                    
	                    $statis = $this->DB_select_once("company_statis","`uid`='".$data['uid']."'","`integral`");
	                    
	                    if($statis['integral']>=$dkjf){
	                        
	                        $recjob = $this->DB_select_once("partjob","`id`='".$partid."' ","`name`,`rec_time`");
	                        
	                        if(!empty($recjob)){
	                            if ($recjob['rec_time'] > time()){
	                                $rec_time = $recjob['rec_time']+$recdays * 86400;
	                            }else {
	                                $rec_time = time()+$recdays * 86400;
	                            }
	                            $status=$this->update_once("partjob",array('rec_time'=>$rec_time),"`uid`='".$data['uid']."' and `id`='".$partid."'");
	                            
	                            if($status){
	                                require_once('integral.model.php');
	                                $integral = new integral_model($this->db,$this->def,array('uid'=>$data['uid'],'username'=>$data['username'],'usertype'=>$data['usertype']));
	                                $integral->company_invtal($data['uid'],$dkjf,false,"购买兼职推荐",true,2,'integral',12);
	                                $return['status']='1';
	                                $return['msg']='兼职推荐购买成功';
	                            }
	                        }
	                    }else{
	                        if($this->config['com_integral_online']==3){
	                            $return['error'] = '积分不足，请先充值积分！';
	                            $return['url'] = $this->config['sy_weburl']."/member/index.php?c=pay&type=integral";
	                        }else{
	                            $return['error'] = '积分不足，请正确输入抵扣积分！';
	                        }
	                    }
	                }
	            }else{
	                $return['error'] = '请正确选择职位推荐以及推荐的时长！';
	            }
	            
	        } else {
	            $return['error'] = '参数填写错误，请重新设置！';
	        }
	    }else{
	        $return['error'] = '系统目前只支持金额消费！';
	    }
	    
	    return $return;
	}

	
	function downresume($data){
	    if(!$data['uid']){
	        $data['uid']=$this->uid;
	    }
	    if(!$data['username']){
	        $data['username']=$this->username;
	    }
	    if(!$data['usertype']){
	        $data['usertype']=$this->usertype;
	    }
	    if(!$data['did']){
	        $data['did']=$this->userdid;
	    }
	    if($this->config['com_integral_online']!=2){
	        
	        if($data['eid']){
	            $eid = intval($data['eid']);
	            
	            if($eid){
	                
	                
	                $resume = $this->DB_select_alls("resume","resume_expect","a.`r_status`<>'2' and a.`uid`=b.`uid` and b.`id`='".$eid."'", "a.name,a.telphone,a.telhome,a.email,a.uid,b.id,b.height_status");
	                $user=$resume[0];
	                
	                $downdata['eid']=$user['id'];
	                $downdata['uid']=$user['uid'];
	                $downdata['comid']=$data['uid'];
	                $downdata['did']=$data['did'];
	                $downdata['downtime']=time();
	                
	                if(empty($resume)){
	                    $return['error'] = '请选择正确的简历下载！';
	                }else {
	                    $dkjf= $this->config['integral_down_resume'] *  $this->config['integral_proportion'];
	                    
	                    $statis = $this->DB_select_once("company_statis","`uid`='".$data['uid']."'","`integral`");
	                    
	                    if($statis['integral']>=$dkjf){
	                        
	                        
	                        $nid = $this->insert_into("down_resume",$downdata);
	                        if($nid){
	                            
	                            require_once('integral.model.php');
	                            $integral = new integral_model($this->db,$this->def,array('uid'=>$data['uid'],'username'=>$data['username'],'usertype'=>$data['usertype']));
	                            $integral->company_invtal($data['uid'],$dkjf,false,"下载简历",true,2,'integral',12);
	                            
	                            $this->DB_update_all("resume_expect","`dnum`=`dnum`+'1'","`id`='".$eid."'");
	                            $content = "新下载了简历 <a href=\"".Url("resume",array('c'=>'show','id'=>(int)$user[id]))."\" target=\"_blank\">".$user['name']."</a> 。";
	                            $this->insert_into('friend_state',array('uid'=>$data['uid'],'content'=>$content,'type'=>2,'ctime'=>time()));
	                            $this->member_log("下载了简历：".$user['name'],3);
	                            
	                            $return['status']='1';
	                            $return['msg']='简历下载成功';
	                        }
	                    }else{
	                        if($this->config['com_integral_online']==3){
	                            $return['error'] = '积分不足，请先充值积分！';
	                            $return['url'] = $this->config['sy_weburl']."/member/index.php?c=pay&type=integral";
	                        }else{
	                            $return['error'] = '积分不足，请正确输入抵扣积分！';
	                        }
	                    }
	                }
	            }else{
	                $return['error'] = '请正确选择的简历下载！';
	            }
	        } else {
	            $return['error'] = '参数填写错误，请重新设置！';
	        }
	    }else{
	        $return['error'] = '系统目前只支持金额消费！';
	    }
	    return $return;
	}

	function buyIssueJob($data){
	    if(!$data['uid']){
	        $data['uid']=$this->uid;
	    }
	    if(!$data['username']){
	        $data['username']=$this->username;
	    }
	    if(!$data['usertype']){
	        $data['usertype']=$this->usertype;
	    }
	    if($this->config['com_integral_online']!=2){
	        
	        if($data['issuejob']){
	            if(!$data['uid']){
	                $return['error']="用户不存在，请重新登录";
	            }else {
	                
	                $dkjf= $this->config['integral_job'] *  $this->config['integral_proportion'];
	                
	                $statis = $this->DB_select_once("company_statis","`uid`='".$data['uid']."'","`integral`");
	                
	                if($statis['integral']>=$dkjf){
	                    
	                    
	                    $status = $this->DB_update_all('company_statis',"`job_num`=`job_num`+ '1' ","`uid`='".$data['uid']."'");
	                    if($status){
	                        
	                        require_once('integral.model.php');
	                        $integral = new integral_model($this->db,$this->def,array('uid'=>$data['uid'],'username'=>$data['username'],'usertype'=>$data['usertype']));
	                        
	                        $integral->company_invtal($data['uid'],$dkjf,false,"发布职位",true,2,'integral',12);
	                        
	                        $return['status']='1';
	                        $return['msg']='购买职位发布成功';
	                    }
	                }else{
	                    if($this->config['com_integral_online']==3){
	                        $return['error'] = '积分不足，请先充值积分！';
	                        $return['url'] = $this->config['sy_weburl']."/member/index.php?c=pay&type=integral";
	                    }else{
	                        $return['error'] = '积分不足，请正确输入抵扣积分！';
	                    }
	                }
	            }
	        }else{
	            $return['error']="参数异常";
	        }
	    }else{
	        $return['error'] = '系统目前只支持金额消费！';
	    }
	    
	    return $return;
	}

	function buyIssuePart($data){
	    if(!$data['uid']){
	        $data['uid']=$this->uid;
	    }
	    if(!$data['username']){
	        $data['username']=$this->username;
	    }
	    if(!$data['usertype']){
	        $data['usertype']=$this->usertype;
	    }
	    if($this->config['com_integral_online']!=2){
	        
	        if($data['issuepart']){
	            if(!$data['uid']){
	                $return['error']="用户不存在，请重新登录";
	            }else {
	                
	                $dkjf= $this->config['integral_partjob'] *  $this->config['integral_proportion'];
	                
	                $statis = $this->DB_select_once("company_statis","`uid`='".$data['uid']."'","`integral`");
	                
	                if($statis['integral']>=$dkjf){
	                    
	                    
	                    $status = $this->DB_update_all('company_statis',"`part_num`=`part_num`+ '1' ","`uid`='".$data['uid']."'");
	                    if($status){
	                        
	                        require_once('integral.model.php');
	                        $integral = new integral_model($this->db,$this->def,array('uid'=>$data['uid'],'username'=>$data['username'],'usertype'=>$data['usertype']));
	                        
	                        $integral->company_invtal($data['uid'],$dkjf,false,"发布兼职",true,2,'integral',12);
	                        
	                        $return['status']='1';
	                        $return['msg']='购买兼职发布成功';
	                    }
	                }else{
	                    if($this->config['com_integral_online']==3){
	                        $return['error'] = '积分不足，请先充值积分！';
	                        $return['url'] = $this->config['sy_weburl']."/member/index.php?c=pay&type=integral";
	                    }else{
	                        $return['error'] = '积分不足，请正确输入抵扣积分！';
	                    }
	                }
	            }
	        }else{
	            $return['error']="参数异常";
	        }
	    }else{
	        $return['error'] = '系统目前只支持金额消费！';
	    }
	    return $return;
	}

	

	function buyInviteResume($data){
	    if(!$data['uid']){
	        $data['uid']=$this->uid;
	    }
	    if(!$data['username']){
	        $data['username']=$this->username;
	    }
	    if(!$data['usertype']){
	        $data['usertype']=$this->usertype;
	    }
	    if($this->config['com_integral_online']!=2){
	        if($data['invite']){
	            if(!$data['uid']){
	                $return['error']="用户不存在，请重新登录";
	            }else {
	                
	                $dkjf= $this->config['integral_interview'] *  $this->config['integral_proportion'];
	                
	                $statis = $this->DB_select_once("company_statis","`uid`='".$data['uid']."'","`integral`");
	                
	                if($statis['integral']>=$dkjf){
	                    
	                    
	                    $status = $this->DB_update_all('company_statis',"`invite_resume`=`invite_resume`+ '1' ","`uid`='".$data['uid']."'");
	                    if($status){
	                        
	                        require_once('integral.model.php');
	                        $integral = new integral_model($this->db,$this->def,array('uid'=>$data['uid'],'username'=>$data['username'],'usertype'=>$data['usertype']));
	                        
	                        $integral->company_invtal($data['uid'],$dkjf,false,"邀请面试",true,2,'integral',12);
	                        
	                        $return['status']='1';
	                        $return['msg']='购买面试邀请成功';
	                    }
	                }else{
	                    if($this->config['com_integral_online']==3){
	                        $return['error'] = '积分不足，请先充值积分！';
	                        $return['url'] = $this->config['sy_weburl']."/member/index.php?c=pay&type=integral";
	                    }else{
	                        $return['error'] = '积分不足，请正确输入抵扣积分！';
	                    }
	                }
	            }
	        }else{
	            $return['error']="参数异常";
	        }
	    }else{
	        $return['error'] = '系统目前只支持金额消费！';
	    }
	    return $return;
	}
}
?>