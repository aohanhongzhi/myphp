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
class jobsync_controller extends adminCommon{

	function index_action(){
		

		$jobCount = $this->obj->DB_select_num("company_job");
		$this->yunset("jobCount",$jobCount);
		$this->yunset("config",$this->config);
		$this->yuntpl(array('admin/admin_jobsync'));
	}
	
	function sync_action(){	
		set_time_limit(0);
		if($_POST['count'] && $_POST['limit']){
			$page = ceil($_POST['count']/$_POST['limit']);
			if(!$_POST['page']){

				$_POST['page'] = 0;
			}
			
			$pageSize = intval($_POST['page'])*intval($_POST['limit']);
			
			$jobAll = $this->obj->DB_select_all("company_job","1  LIMIT ".$pageSize.",".$_POST['limit']);
			
			if(is_array($jobAll)){
				
				foreach($jobAll as $key=>$value){
						
					$uid[] = $value['uid'];
					$jobList[$value['uid']][] = $value;
					
				}
				$uids = array_unique($uid);
				
				$comList = $this->obj->DB_select_all("company","`uid` IN (".@implode(',',$uids).")");
				
			}
			if(is_array($comList)){
				include PLUS_PATH."/job.cache.php";
				include PLUS_PATH."/com.cache.php";
				include PLUS_PATH."/industry.cache.php";
				include PLUS_PATH."/city.cache.php";
				include(CONFIG_PATH."db.data.php");		
		        $this->yunset("arr_data",$arr_data);				
				foreach($comList as $key=>$value){ 
					$jsonvalue = array();
					$jsonvalue['uid'] = $value['uid'];
					$jsonvalue['name'] = $value['name'];
					$jsonvalue['hy'] = $industry_name[$value['hy']];
					$jsonvalue['comnum'] = $comclass_name[$value['mun']];
					$jsonvalue['province'] = $city_name[$value['provinceid']];
					$jsonvalue['city'] = $city_name[$value['cityid']];
					$jsonvalue['three_city'] = $city_name[$value['three_cityid']];
					$jsonvalue['address'] = $value['address'];
					$jsonvalue['website'] = $value['website'];
					$jsonvalue['sdate'] = $value['sdate'];
					$jsonvalue['linkman'] = $value['linkman'];
					$jsonvalue['content'] = $value['content'];
					$jsonvalue['x'] = $value['x'];
					$jsonvalue['y'] = $value['y'];
					$jsonvalue['bustops'] = $value['bustops'];
					$jsonvalue['linktel'] = $value['linktel'];
					
					$jsonvalue['linkphone'] = $value['linkphone'];
					
					$jsonvalue['linkmail'] = $value['linkmail'];
					
					if(is_array($jobList[$value['uid']])){
						
						foreach($jobList[$value['uid']] as $k=>$v){
							$jobvalue['id'] = $v['id'];
							$jobvalue['name'] = $v['name'];
							
							$jobvalue['job1'] = $job_name[$v['job1']];
							$jobvalue['job1_son'] = $job_name[$v['job1_son']];
							$jobvalue['job_post'] = $job_name[$v['job_post']];
							$jobvalue['province'] = $city_name[$v['provinceid']];
							$jobvalue['city'] = $city_name[$v['provinceid']];
							$jobvalue['country'] = $city_name[$v['three_cityid']];
							$jobvalue['salary'] = $comclass_name[$v['salary']];
							$jobvalue['type'] = $comclass_name[$v['type']];
							$jobvalue['number'] = $comclass_name[$v['number']];
							$jobvalue['exp'] = $comclass_name[$v['exp']];
							$jobvalue['report'] = $comclass_name[$v['report']];
							$jobvalue['sex'] = $arr_data['sex'][$v['sex']];
							$jobvalue['edu'] = $comclass_name[$v['edu']];
							$jobvalue['marriage'] = $comclass_name[$v['marriage']];
							$jobvalue['description'] = $v['description'];
							$jobvalue['sdate'] = $v['sdate'];
							$jobvalue['edate'] = $v['edate'];
							if($v['lang']){
								$langList = array();
								$lang = @explode(',',$v['lang']);
								foreach($lang as $lv){
									
									$langList[] = $comclass_name[$lv];
								}
								$jobvalue['lang'] = @implode(',',$langList);
							}
							if($v['welfare']){
								$welfareList = array();
								$welfare = @explode(',',$v['welfare']);
								foreach($welfare as $fv){
									
									$welfareList[] = $comclass_name[$fv];
								}
								$jobvalue['welfare'] = @implode(',',$welfareList);
							}
							
						$jsonvalue['joblist'][] = $jobvalue;
						}

					}
					
					$Arr[] = $jsonvalue;
				}
				if(!$_POST['synckey']){
					$_POST['synckey'] = time().rand(1000,9999);
				}
				
				$Crmjson['syncjson'] = json_encode($Arr);
				$Crmjson['dxuser'] = $this->config['dx_user'];
				$Crmjson['dxkey'] = $this->config['dx_key'];
				$Crmjson['synckey'] = $_POST['synckey'];
				
				
				$url = 'http://sync.dx.com/job/';
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $Crmjson);
				$return = curl_exec($ch);
				if(curl_errno($ch)){
					
				}
				curl_close($ch); 
				
				
				$response = json_decode($return);
				
				
				if($response->state == '1')
				{
					if($response->uids)
					{
						$this->obj->DB_update_all("company_job","`sync`='1'","`id` IN (".$response->uids.")");
						$nowcount = count(explode(',',$response->uids));
					}
					
					if(($_POST['page']+1)>=$page){
						echo json_encode(array('state'=>'0'));
					}else{
						
						
						echo json_encode(array('state'=>'1','readynum'=>$response->readynum,'page'=>($_POST['page']+1),'count'=>($_POST['page']+1)*$_POST['limit'],'synckey'=>$_POST['synckey']));
					}
					die;
				}else{
					echo json_encode(array('state'=>'2','error'=>$response->error));
				}
			}
		}else{
			echo json_encode(array('state'=>'2'));
		}
	}

	
	function save_action(){
 		if($_POST["config"]){
		 unset($_POST["config"]);
		   foreach($_POST as $key=>$v){
		    	$config=$this->obj->DB_select_num("admin_config","`name`='$key'");
			   if($config==false){
				$this->obj->DB_insert_once("admin_config","`name`='$key',`config`='".$v."'");
			   }else{
				$this->obj->DB_update_all("admin_config","`config`='".$v."'","`name`='$key'");
			   }
		 	}
			$this->web_config(); 
			$this->ACT_layer_msg("网站配置设置成功！",9,1);
		 }
	}
}
?>