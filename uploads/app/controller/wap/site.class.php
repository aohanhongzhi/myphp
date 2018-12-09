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
class site_controller extends common{
	function index_action(){
		if($this->config["sy_web_site"]!="1"){
			$this->ACT_msg($_SERVER['HTTP_REFERER'], $msg = "暂未开启多站点模式！");
		}
		
		$this->yunset("headertitle","分站");
		$this->seo("index");
		$this->yuntpl(array('wap/site'));
	}
  function domain_action(){
		$id=(int)$_GET['id']; 
		include(PLUS_PATH."/domain_cache.php");
		include(PLUS_PATH."/city.cache.php");
		$host =  $this->protocol.$_SERVER['HTTP_HOST']; 
		if(strpos($host,$this->config['sy_onedomain'])!==false){
			$domainUrl  = $this->config['sy_onedomain'];
		}else{
			$domainUrlAll  = parse_url($host);
			$domainUrl = $domainUrlAll['host'];
		} 
		
		if($id>=1){ 
			$domain=array();  
			foreach($site_domain as $key=>$val){
				if($val['cityid']==$id || $val['three_cityid']==$id || $val['province']==$id){
					$domain=$val;
				}
			} 
			
			
			if($domain){
				$parseDate['did']=$domain['id'];
				$parseDate['fz_type']=$domain['fz_type'];
				if($parseDate['fz_type']=='1'){
					if($domain['three_cityid']>0){
						$parseDate['three_cityid']	=	$domain['three_cityid'];
						$parseDate['cityname']		=	$city_name[$domain['three_cityid']];
					}elseif($domain['cityid']){
						$parseDate['cityid']	=	$domain['cityid'];
						$parseDate['cityname']	=	$city_name[$domain['cityid']];
					}elseif($domain['province']){
						$parseDate['province']	=	$domain['province'];
						$parseDate['cityname']	=	$city_name[$domain['province']];
					}
					setcookies('hyclass',time()-86400,$this->config['sy_onedomain']);
				}else if($parseDate['fz_type']=='2'&&$domain['hy']){
					$parseDate['hyclass']=$domain['hy'];
					$parseDate['cityname']		=	$domain['webname'];
					setcookies(
						array(
						'province'=>'',
						'cityid'=>'',
						'three_cityid'=>''
						),time()-86400,"/"
					);
				}	
				if($domain['webname']){$parseDate['sy_webname']  =	$domain['webname'];}
				if($domain['webtitle']){$parseDate['sy_webtitle']  =	$domain['webtitle'];}
				if($domain['weblogo']){$parseDate['sy_logo']  =	$domain['weblogo'];}
				if($domain['webkeyword']){$parseDate['sy_webkeyword']  =	$domain['webkeyword'];}
				if($domain['webmeta']){$parseDate['sy_webmeta']  =	$domain['webmeta'];}
				if($domain['style']){$parseDate['style']  =	$domain['style'];}  
				$parseDate['sy_wapurl']  =	$host.'/wap';
				
				
				 
				$this->config = array_merge($this->config,$parseDate); 
				
				foreach($parseDate as $key=>$value){
					$this->cookie->SetCookie($key,$value,time()+86400);
				}
			}
		}		
		if(!$parseDate && $_COOKIE['fz_type']){
			setcookies(
				array(
				'sy_wapurl'=>'',
				'did'=>'',
				'fz_type'=>'',
				'province'=>'',
				'cityid'=>'',
				'hyclass'=>'',
				'three_cityid'=>'',
				'cityname'=>'',
				'sy_webkeyword'=>'', 
				'sy_logo'=>'',
				'style'=>'',
				'sy_webtitle'=>'',
				'sy_webmeta'=>'',
				'sy_webname'=>''

			),time()-86400,$domainUrl);
		
		}
		header("location:".$this->config['sy_wapdomain']);die;
	}
}
?>