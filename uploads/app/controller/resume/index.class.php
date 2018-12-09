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
class index_controller extends resume_controller{
	function usersearch(){
		if($_GET[job]){
			$job=explode("_",$_GET[job]);
			$_GET['job1']=$job[0];
			$_GET['job1_son']=$job[1];
			$_GET['job_post']=$job[2];
		}
		if($_GET[city]){
			$city=explode("_",$_GET[city]);
			$_GET['provinceid']=$city[0];
			$_GET['cityid']=$city[1];
			$_GET['three_cityid']=$city[2];
		}
		
		if($_GET[tp]==1){
			$_GET['pic']=1;
		}
		
		if($_GET[all]){
			$allurl=explode("_",$_GET[all]);
			$_GET['hy']=$allurl[0];
			$_GET['edu']=$allurl[1];
			$_GET['exp']=$allurl[2];
			$_GET['sex']=$allurl[3];
			$_GET['report']=$allurl[4];
			$_GET['uptime']=$allurl[5];
			$_GET['idcard']=$allurl[6];
			$_GET['work']=$allurl[7];
			$_GET['tag']=$allurl[8];
		}
		if ($_GET['salary']){
		    $salary=explode('_', $_GET['salary']);
		    $_GET['minsalary']=$salary[0];
		    $_GET['maxsalary']=$salary[1];
		}
		include(CONFIG_PATH."db.data.php");	
		unset($arr_data['sex'][3]);	
		$this->yunset("arr_data",$arr_data);
		$arr_data1=$arr_data['sex'][$_GET['sex']];
		$this->yunset("arr_data1",$arr_data1);
        
        $uptime=array(1=>'今天',3=>'最近3天',7=>'最近7天',30=>'最近一个月','90'=>'最近三个月');
        $this->yunset("uptime",$uptime);
        
        $FinderParams=array('hy','job1','job1_son','job_post','provinceid','cityid','three_cityid','minsalary','maxsalary','edu','exp','sex','type','report','adtime','uptime','keyword');
        
		$adtime=array('1'=>'一天内',"3"=>'三天内','7'=>'七天内',"15"=>'十五天内','30'=>'一个月内',"60"=>'两个月内');
		$this->yunset("adtime",$adtime);
		if($this->config['province']){
			$_GET['provinceid'] = $this->config['province'];
		}
		if($this->config['cityid']){
			$_GET['cityid'] = $this->config['cityid'];
		}
		if($this->config['three_cityid']){
			$_GET['three_cityid'] = $this->config['three_cityid'];
		}
		$CacheM=$this->MODEL('cache');
        $CacheList=$CacheM->GetCache(array('job','city','user','hy'));
        $this->yunset($CacheList);
		
		foreach($_GET as $k=>$v){
			if(in_array($k,$FinderParams)&&$v!=""&&$v!="0"){
				if($v!=""){
					$finder[$k]=$v;
				}
			}
		}
		unset($finder['city']);unset($finder['job']);unset($finder['all']);unset($finder['tp']);
		$this->yunset('finder',$finder);
		if($this->config['cityid']){
			unset($finder['cityid']);
		} 
		if($finder&&is_array($finder)){
			foreach($finder as $key=>$val){
				$para[]=$key."=".$val;
			}
			$paras=@implode('##',$para);
			$this->yunset("paras",$paras);
		}
		
		 
		
		if((int)$_GET['three_cityid']){
			foreach($CacheList['city_type'] as $k=>$v){
				if(in_array((int)$_GET['three_cityid'],$v)){
					$zpthreecityid=$k;
				}
			}
			$this->yunset("zpthreecityid",$zpthreecityid);
		}elseif((int)$_GET['cityid']){
			foreach($CacheList['city_type'] as $k=>$v){
				if(in_array((int)$_GET['cityid'],$v)){
					$zpcityid=$k;
				}
			}
			$this->yunset("zpcityid",$zpcityid);
		}
		if((int)$_GET['job_post']){
			foreach($CacheList['job_type'] as $k=>$v){
				if(in_array((int)$_GET['job_post'],$v)){
					$zpjobpost=$k;
				}
			}
			$this->yunset("zpjobpost",$zpjobpost);
		}elseif((int)$_GET['job1_son']){
			foreach($CacheList['job_type'] as $k=>$v){
				if(in_array((int)$_GET['job1_son'],$v)){
					$zpjob1son=$k;
				}
			}
			$this->yunset("zpjob1son",$zpjob1son);
		}
		 
         
        include PLUS_PATH."keyword.cache.php";
        if(is_array($keyword)){
          foreach($keyword as $k=>$v){
            if($v['type']=='5'&&$v['tuijian']=='1'){
              $resumekeyword[]=$v;
            }
          }
        }
        $this->yunset("resumekeyword",$resumekeyword);
        
		$this->seo("user_search");
		$this->yun_tpl(array('search'));
	}
	function search_action(){
		$this->usersearch();
	}
	function index_action(){
             if(($this->uid==''||$this->username=='')&&$this->config['sy_resume_visitors']>0){ 
        		if($_COOKIE['resumevisitors']>=$this->config['sy_resume_visitors']){
        			$this->ACT_msg($this->config['sy_weburl'],"请登录后继续访问！");
        		}else{
        			if ($_COOKIE['resumevisitors']==''){
        				$resumevisitors=1;
        			}else{
        				$resumevisitors=$_COOKIE['resumevisitors']+1;
        			}
        			$this->cookie->SetCookie("resumevisitors",$resumevisitors,time()+86400,"/");
        		}
        	}
	    
		if($this->config['sy_default_userclass']=='1'){
	        if($this->config['sy_resumedir']!=""){
				$resumeclassurl=$this->config['sy_weburl']."/resume/index.php?c=search&";
			}else{
				$resumeclassurl=$this->config['sy_weburl']."/index.php?m=resume&c=search&";
			}
			$this->yunset("resumeclassurl",$resumeclassurl);
			$CacheM=$this->MODEL('cache');
            $CacheList=$CacheM->GetCache(array('job','city','hy'));
            $this->yunset($CacheList);
			$this->yunset(array('gettype'=>$_SERVER["QUERY_STRING"],'getinfo'=>$_GET));
			$this->seo("user");
			$this->yun_tpl(array('index'));
		}else{
			$this->usersearch();
		}
	}
}
?>