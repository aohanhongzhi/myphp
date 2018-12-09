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
***全局的权限验证 数据验证库
 */
class common{
	public $tpl='';
	public $db='';
	public $obj='';
	public $config = '';
	public $uid="";
	public $data="";
	public $username="";
	public $usertype="";
	public $dirname="";
	public $protocol="";
	public $cookie = '';

	
	function common($tpl,$db,$def="",$model="index",$m="") {
		global $config;
		$this->config = $config;
		$this->tpl=$tpl;
		$this->db=$db;
		$this->def=$def;
		$this->m=$m;
				
		require(APP_PATH.'app/public/action.class.php');
		require(APP_PATH.'app/model/cookie.model.php');
		$this->cookie = new cookie_model($this->db,$this->def,
			array('uid'=>$this->uid,'username'=>$this->username,'usertype'=>$this->usertype));

		$this->protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
		
		if($_COOKIE['uid']){
			$shell=$this->GET_user_shell($_COOKIE['uid'],$_COOKIE['shell']);
			if(!is_array($shell) || empty($shell)){
                $this->cookie->unset_cookie();
				$this->uid='';
				$this->userdid='';
				$this->username='';
				$this->usertype='';
				$checkUrl = $this->protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				header("location:".$checkUrl);
				die;
			}else{ 
				$this->uid=intval($shell['uid']);
				$this->userdid = isset($_COOKIE['userdid']) ? intval($_COOKIE['userdid']) : '';
				$this->username=$shell['username'];
				$this->usertype=$shell['usertype'];
				$this->yunset("uid",intval($shell['uid']));
				$this->yunset("usertype",intval($shell['usertype']));
				$this->yunset("username",$shell['username']); 
			}
		}else{
			$this->uid='';
		}
		
		$this->obj= new model($this->db,$def,array('uid'=>$this->uid,'username'=>$this->username,'usertype'=>$this->usertype)); 
		$path['style']=$this->config['sy_weburl']."/app/template/".$this->config['style'];
		$path['client']=$this->config['sy_weburl']."/about";
		$path['tplstyle']=TPL_PATH.$this->config['style'];
		$path['tpldir']=$this->tpl->template_dir;
		$path['ask_style']=$this->config['sy_weburl']."/app/template/ask";
		$path['askstyle']=TPL_PATH."ask";
		$path['lietoustyle']=TPL_PATH."member/lietou/";
		$path['lietou_style']=$this->config['sy_weburl']."/app/template/member/lietou";
		$path['adminstyle']=TPL_PATH."admin";
		$path['userstyle']=TPL_PATH."member/user";
		$path['user_style']=$this->config['sy_weburl']."/app/template/member/user";
		$path['comstyle']=TPL_PATH."member/com";
		$path['com_style']=$this->config['sy_weburl']."/app/template/member/com";
		$path['wapstyle']=TPL_PATH."wap";
		$path['wap_style']=$this->config['sy_weburl']."/app/template/wap";
		$path['wapadminstyle']=TPL_PATH."wapadmin";
		$path['plusstyle']=$this->config['sy_weburl']."/data/plus";
		
		$_SERVER['HTTP_REFERER'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		
		if($_SERVER['PHP_SELF']!='/admin/index.php'){
			$this->cookie->setcookie("checkurl",$_SERVER['HTTP_REFERER'],time()+3600);
			$path['cookie']=$_COOKIE;
		}
		$this->yunset($path);
		
		if(!($this->config['sy_wapdomain'])){

			if($this->config['sy_web_site']=='1' && $this->config['sy_indexdomain']){

				$this->config['sy_wapdomain'] = $this->config['sy_indexdomain'].'/'.$this->config['sy_wapdir'];
			}else{
				$this->config['sy_wapdomain'] = $this->config['sy_weburl'].'/'.$this->config['sy_wapdir'];
			}
			
			$this->yunset("config_wapdomain",$this->config['sy_wapdomain']);

		}else{
			if($this->config['sy_wapssl']=='1'){
				$protocol = 'https://';
			}else{
				$protocol = 'http://';
			}
			if(strpos($this->config['sy_wapdomain'],'http://')===false && strpos($this->config['sy_wapdomain'],'https://')===false)
			{
				$this->config['sy_wapdomain'] = $protocol.$this->config['sy_wapdomain'];
			}

			$this->yunset("config_wapdomain",$this->config['sy_wapdomain']);
		}

		$this->yunset("config",$this->config);
		$this->$model();

		if(!file_exists(PLUS_PATH."config.php")){
			$this->web_config();
		}
		$this->job_auto();
		
	}
	
	function DoException(){
		$this->ACT_msg("index.php","您请求的页面不存在！");
	}
	
	function yuntpl($tplarr=array()){
		if(is_array($tplarr) && $tplarr!=''){
			foreach($tplarr as $v){
				if($v!=''){
				    $this->tpl->display($v.".htm");
				}
			}
		}else{
			echo "模版不能为空！";die;
		}
	}
	
	function yun_tpl($tplarr=array()){
		if(is_array($tplarr) && $tplarr!=''){
			foreach($tplarr as $v){
				$this->tpl->display($this->config['style']."/".$this->m."/".$v.".htm");
			}
		}else{
			echo "模版不能为空！";die;
		}
	}
	
	
	function remind_msg($uid,$usertype,$weburl='',$login='',$cookietype=''){
		$num=0;
		if($_COOKIE['sysmsg'.$usertype]=="" || $login==1 || $cookietype=='sysmsg'.$usertype){
			$message=$this->obj->DB_select_num("sysmsg","`fa_uid`='".$uid."' and `remind_status`='0'");
			$this->cookie->setcookie("sysmsg".$usertype,$message,time()+3600);
		}
		if($usertype=="1"){
			if($_COOKIE['userid_msg']=="" || $login==1 || $cookietype=='userid_msg'){
				$userid_msg=$this->obj->DB_select_num("userid_msg","`uid`='".$uid."' and `is_browse`='1'");
				$this->cookie->setcookie("userid_msg",$userid_msg,time()+3600);
			}
			if($_COOKIE['usermsg']=="" || $login==1 || $cookietype=='usermsg'){
				$msg=$this->obj->DB_select_num("msg","`uid`='".$uid."' and `user_remind_status`='0'");
				$this->cookie->setcookie("usermsg",$msg,time()+3600);
			}
		}elseif($usertype=="2"){
			if($_COOKIE['userid_job']=="" || $login==1 || $cookietype=='userid_job'){
				$userid_job=$this->obj->DB_select_num("userid_job","`com_id`='".$uid."' and `is_browse`='1'");
				$this->cookie->setcookie("userid_job",$userid_job,time()+3600);
			}
			if($_COOKIE['commsg']=="" || $login==1 || $cookietype=='commsg'){
				$msg=$this->obj->DB_select_num("msg","`job_uid`='".$uid."' and `com_remind_status`='0'");
				$this->cookie->setcookie("commsg",$msg,time()+3600);
			}
		}
		$num=$num+$_COOKIE['sysmsg'.$usertype];
		if($usertype==1){
			$num=$num+$_COOKIE['userid_msg'];
			$num=$num+$_COOKIE['usermsg'];
		}elseif($usertype==2){
			$num=$num+$_COOKIE['commsg'];
			$num=$num+$_COOKIE['userid_job'];
		}
		if($num>0){
			$this->cookie->setcookie("remind_num",$num,time()+3600);
		}else{
			$this->cookie->SetCookie("remind_num","",time() - 3600);
		}
		$this->yunset("cookie",$_COOKIE);

		
		$time=(time()-strtotime(date("Y-m-d")))%1800;
		if($time=="0"){
				$this->cookie->SetCookie("sysmsg".$usertype,"",time() - 3600);
				$this->cookie->SetCookie("userid_msg","",time() - 3600);
				$this->cookie->SetCookie("usermsg","",time() - 3600);
				$this->cookie->SetCookie("userid_job","",time() - 3600);
				$this->cookie->SetCookie("commsg","",time() - 3600);
				$this->cookie->SetCookie("userid_job3","",time() - 3600);
				$this->cookie->SetCookie("entrust","",time() - 3600);
				$this->cookie->SetCookie("commsg3","",time() - 3600);
				$this->cookie->SetCookie("remind_num","",time() - 3600);
				$this->cookie->SetCookie("message","",time() - 3600);
				$this->cookie->SetCookie("sign_up","",time() - 3600);
		}
	}
	
	function unset_remind($cooke,$usertype){
		if($this->config['sy_onedomain']!=""){
			$weburl=get_domain($this->config['sy_onedomain']);
		}else{
			$weburl=get_domain($this->config['sy_weburl']);
		}
		$this->remind_msg($this->uid,$usertype,$weburl,"",$cooke);
	}
	
	function uc_edit_pw($post,$old="1",$url){
		$old_info = $this->obj->DB_select_once("member","`uid`='".$post['uid']."'","`name_repeat`,`username`");
		if($post['password']!=""){
			if($this->config['sy_uc_type']=="uc_center" &&$old_info['name_repeat']!="1"){
				$this->uc_open();
				$ucresult = uc_user_edit($old_info['username'], $post['oldpw'], $post['password'], $post['email'],$old);
				if($ucresult == -1){
					$msg= '旧密码不正确';
				} elseif($ucresult == -4) {
					$msg= 'Email 格式有误';
				} elseif($ucresult == -5) {
					$msg= 'Email 不允许注册';
				} elseif($ucresult == -6) {
					$msg= '该 Email 已经被注册';
				}
				if($msg!=""){
					$this->ACT_msg($url, $msg);
				}
			}

			$salt = substr(uniqid(rand()), -6);
			$pass = md5(md5($post['password']).$salt);
			$where="`password`='$pass',`salt`='$salt',";
			
		}
		if(is_array($post)){
			foreach($post as $k=>$v){
				if($k!="password"&&$k!="oldpw"){
					$where.="`$k`='$v',";
				}
			}
			$where.="`username`='".$old_info['username']."'";
		}
		$nid = $this->obj->DB_update_all("member",$where,"`uid`='".$post['uid']."'");
		return $nid;
	}
	
	
	function yunset($name,$value=null){
        if(is_array($name)){
            foreach($name as $k=>$v){
                $this->tpl->assign($k,$v);
            }
        }else{
		    $this->tpl->assign($name,$value);
        }
	}
  
	function city_info($city,$city_name){
		if(is_array($city)){
			foreach($city as $key=>$value){
				$city_area[] = array("id"=>$value,"name"=>$city_name[$value]);
			}
			return $city_area;
		}
	}
	
	function integrated(){
		
		$city_area =$this->obj->DB_select_all("city_class","`keyid`='0'");
		return $city_area;
	}
	
	
	function company(){
		$this->tpl->is_fun();
		$company=$this->obj->DB_select_once("company","`uid`='".(int)$_GET['id']."'","`name`,`content`");
		$data['company_name']=$company['name'];
		$data['company_name_desc']=$company['content'];
		if($_GET['nid']){
			$news=$this->obj->DB_select_once("company_news","`id`='".(int)$_GET['nid']."'","`title`");
			$data['company_news']=$news['title'];
		}
		if($_GET['pid']){
			$product=$this->obj->DB_select_once("company_product","`id`='".(int)$_GET['pid']."'","`title`");
			$data['company_product']=$product['title'];
		}
		$this->data=$data;
	}
	
	
	function appadmin(){
		$this->get_appadmin_source();
	}
	
	
	function index(){
		
		$UA = strtoupper($_SERVER['HTTP_USER_AGENT']);
		if(strpos($UA, 'WINDOWS NT') === true){
			
			header("location:".Url('index',array('c'=>'wap')));
		}
		$this->tpl->is_fun();
		
		
		if(!$_GET['keyword'])$_GET['keyword']='';
		
		$qq=@explode(",",$this->config['sy_qq']);
		$this->yunset("qq",$qq);
		
		
		
	}
	
	function wap(){
		
		if($this->config['sy_wap_web']=="2"){
			header("location:".$this->config['sy_weburl']);
		}
		$UA = strtoupper($_SERVER['HTTP_USER_AGENT']);
		if($this->config['sy_pc_jump_wap']!='1' && strpos($UA, 'WINDOWS NT') !== false){
			
			header("location:".Url('index',array('c'=>'wap')));
			
		}
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
			
			if($this->config['wx_appid'] && $this->config['wx_appsecret']){
				
				$signPackage = getWxJsSdk();

				$this->yunset("signPackage",$signPackage);
			}	
			$this->yunset("isweixin",1);
		}
		$typename=array('1'=>'个人账户','2'=>'企业账户');
		$this->yunset('typename',$typename);
		$this->tpl->is_fun();
		include PLUS_PATH."/tplmoblie.cache.php";
		if($tplmoblie['wapdiy']==1){
			$this->yunset('tplmoblie',$tplmoblie);
		}
	}

  
	function wap_member(){

		if($this->config['sy_wap_web']=="2"){
			header("location:".$this->config['sy_weburl']);
		}
		$UA = strtoupper($_SERVER['HTTP_USER_AGENT']);
		if($this->config['sy_pc_jump_wap']!='1' && strpos($UA, 'WINDOWS NT') !== false){
			header("location:".Url('index',array('c'=>'wap')));
		}

		if(!$this->uid || !$this->username){
			if(is_weixin()){

				$Url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				$this->cookie->SetCookie("wxUrl",$Url,time() + 3600);
				header("location:".$this->config['sy_wapdomain']."/index.php?c=wxoauth");die;
			}
			$this->cookie->unset_cookie();
			
			$data['msg']='请先登录！';
			$data['url']=$this->config['sy_weburl'].'/wap/index.php?c=login';
			$this->yunset("layer",$data);
		}else{

			$shell=$this->GET_user_shell($this->uid,$_COOKIE['shell']);

			if(!is_array($shell)){

				$this->cookie->unset_cookie();
			    
				$data['msg']='你无权操作，请重新登录！';
				$data['url']=$this->config['sy_weburl'].'/wap/index.php?m=login';
				$this->yunset("layer",$data);
				
			}else{
				if (in_array($_GET['c'],array('integral','finance')) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
					//取微信分享参数
					if($this->config['wx_appid'] && $this->config['wx_appsecret']){
						
						$signPackage = getWxJsSdk();

						$this->yunset("signPackage",$signPackage);
					}	
					$this->yunset("isweixin",1);
				}
				$this->yunset("uid",$this->uid);
				$this->yunset("username",$this->username);
			}
		}

	}
	
	function member() {
		
		$this->tpl->is_fun();
		if(!$this->uid && !$this->username){
			$login=Url("login",array("usertype"=>"1"),"1");
			$this->ACT_msg($login,"请先登录");
		}else{
			$shell=$this->GET_user_shell($this->uid,$_COOKIE['shell']);
			if(!is_array($shell)){
				$this->ACT_msg("../index.php","你无权操作，请重新登录");
			}else{
				if($_COOKIE['usertype']==2 || $_COOKIE['usertype']==3){
					
					if($_COOKIE['usertype']==2){
						
						$this->obj->DB_update_all("partjob","`state`='2'","`edate`<'".time()."' and `edate`>'0' and `uid`='".$this->uid."' and `state`<>'2'");
					}
					
				}
			}
		}
		$this->yunset("uid",$this->uid);
		$this->yunset("username",$this->username);
		$this->yunset("useremail",$_COOKIE['email']);
	}

	
	function web_config(){
		$config=$this->obj->DB_select_all('admin_config');
		if(is_array($config)){
			foreach($config as $v){
				$configarr[$v['name']]=$v['config'];
			}
		}
		if(!empty($configarr)){
			made_web(PLUS_PATH.'config.php',ArrayToString($configarr),'config');
		}
		
		if(!file_exists(PLUS_PATH.'pimg_cache.php')){
			$this->advertise_cache();
		}
		if(!file_exists(PLUS_PATH.'dbstruct.cache.php')){
			include_once(LIB_PATH."cache.class.php");
			$cacheclass= new cache(PLUS_PATH,$this->obj);
			$cacheclass->database_cache("dbstruct.cache.php");
		}
	}
	
	function advertise_cache(){
		include_once(APP_PATH.'admin/model/model/advertise_class.php');
		$adver = new advertise($this->obj);
		$adver->model_ad_arr_action();
	}
   
	
	function send_message($uidarr=array(),$title="",$content="",$messagealert=false,$user="admin"){
		if(is_array($uidarr)){
			foreach($uidarr as $v){
				$data=array('uid'=>$v,'title'=>$title,'content'=>$content,'status'=>0,'user'=>$user,'ctime'=>time());
				$insert_id=$this->MODEL()->insert_into('message',$data);
			}
			if($messagealert){
				$this->ACT_layer_msg("发送成功！",9,$_SERVER['HTTP_REFERER']);
			}else{
				return $insert_id;
			}
		}else{
			$this->ACT_layer_msg("参数有误，请检查参数！",8,$_SERVER['HTTP_REFERER']);
		}
	}
	function header_desc($title="",$keyword="",$description=""){
		$this->yunset("title",$title);
		$this->yunset("keywords",$keyword);
		$this->yunset("description",$description);
	}
    
	
	function get_page($table,$where="",$pageurl="",$limit=20,$field="*",$rowsname="rows",$pagenavname="pagenav"){
		
		$rows=array();
		$page=$_GET['page']<1?1:$_GET['page'];
		$ststrsql=($page-1)*$limit;
		$num=$this->obj->DB_select_num($table,$where);
		$this->yunset("total",$num);
		if($num>$limit){
			$pages=ceil($num/$limit);
            $pagenav=Page($page,$num,$limit,$pageurl,$notpl=false,$this->tpl,$pagenavname);
			$this->yunset("pages",$pages);
		}
		$rows=$this->obj->DB_select_all($table,"$where limit $ststrsql,$limit",$field);
		$this->yunset($rowsname,$rows);
		return $rows;
	}
	
	function array_action($job_info,$array=array()){
		if(!empty($array)){
			$comclass_name = $array["comclass_name"];
			$city_name = $array["city_name"];
			$job_name = $array["job_name"];
			$industry_name = $array["industry_name"];
		}else{
			include PLUS_PATH."/city.cache.php";
			include PLUS_PATH."/com.cache.php";
			include PLUS_PATH."/job.cache.php";
			include PLUS_PATH."/industry.cache.php";
		}
		$job_info['exp_info'] = $comclass_name[$job_info['exp']];
		$job_info['edu_info'] = $comclass_name[$job_info['edu']];
		$job_info['age_info'] = $comclass_name[$job_info['age']];
		if($job_info['minsalary']&&$job_info['maxsalary']){
			$job_info['salary_info'] ="￥".$job_info['minsalary']."-".$job_info['maxsalary'];
		}elseif($value['minsalary']){
			$job_info['salary_info'] ="￥".$job_info['minsalary']."以上";
		}else{
			$job_info['salary_info'] ="面议";
		}
		$job_info['number_info'] = $comclass_name[$job_info['number']];
		$job_info['mun_info'] = $comclass_name[$job_info['mun']];
		
		$job_info['type_info'] = $comclass_name[$job_info['type']];
		$job_info['marriage_info'] = $comclass_name[$job_info['marriage']];
		$job_info['report_info'] = $comclass_name[$job_info['report']];
		$job_info['prv_info'] = $city_name[$job_info['provinceid']];
		$job_info['comprv_info'] = $city_name[$job_info['com_provinceid']];
		$job_info['prov_info'] = $city_name[$job_info['prov']];
		$job_info['cty_info'] = $city_name[$job_info['city']];
		$job_info['pr_info'] = $comclass_name[$job_info['pr']];
		$job_info['city_info'] = $city_name[$job_info['cityid']];
		$job_info['city2_info'] = $city_name[$job_info['three_cityid']];
		$job_info['three_info'] = $city_name[$job_info['three_city']];
		$job_info['hy_info'] = $industry_name[$job_info['hy']];
		$job_info['pr_info'] = $comclass_name[$job_info['pr']];
		$job_info['mun_info'] = $comclass_name[$job_info['mun']];
		
		if($job_info['lang']!=""){
			$lang = @explode(",",$job_info['lang']);
			foreach($lang as $key=>$value){
				$langinfo[]=$comclass_name[$value];
			}
			$job_info['lang_info'] = @implode(",",$langinfo);
		}else{
			$job_info['lang_info'] ="";
		}
		$job_info['welfare_info']=$job_info['welfare'];
		
		return $job_info;
	}

	function FetchMoblie($moblie) {
		$member_arr = $this->obj->DB_select_once("member","`moblie`='".$moblie."'",'uid,username');
		return $member_arr;
	}
	
	function send_autocode($moblie, $length = 6, $validity = 90, $is_nomember = false){
		
		if(!$this->config["sy_msguser"] || !$this->config["sy_msgpw"] || !$this->config["sy_msgkey"]||$this->config['sy_msg_isopen']!='1'){
			return 4;
		}
		
		if(!CheckMoblie($moblie)){
			return 5;
		}
		
		$member_arr = $this->FetchMoblie($moblie);
		if(!is_array($member_arr) && $is_nomember){
			if ($is_nomember) {
                $member_arr['uid'] = 0;
		        $member_arr['username'] = '系统'; 
            } else {
                return 3;
            }
		}
		
		$cert_arr=$this->obj->DB_select_once("company_cert","`check2`='".$moblie."' and type='2'");
		if (is_array($cert_arr) && ((time() - $cert_arr['ctime']) < $validity)) {
		 	return 6;
		}
		
		$num=$this->obj->DB_select_num("moblie_msg","`moblie`='".$moblie."' and `ctime`>'".strtotime(date("Y-m-d"))."'");
		if($num>=$this->config['moblie_msgnum']){
			return 1;
		}
		
		$ipnum=$this->obj->DB_select_num("moblie_msg","`ip`='".fun_ip_get()."' and `ctime`>'".strtotime(date("Y-m-d"))."'");
		if($ipnum>=$this->config['ip_msgnum']){
			return 2;
		}
		$randstr = gt_Generate_code($length);
		
		$data['uid']=$member_arr['uid'];
		$data['name']=$member_arr['username'];
		$data['cuid']=$member_arr['uid'];
		$data['cname']=$member_arr['username'];
		$data['moblie']=$moblie;
		$data['type']="login";
		$data['autocode']=$randstr;
		$notice = $this->MODEL('notice');
		$result = $notice->sendSMSType($data);
		if($result['status'] != -1){
			$this->cookie->setcookie("moblie_code",$randstr,time()+120);
			$sql['status']=0;
			$sql['step']=1;
			$sql['check']=$moblie;
			$sql['check2']=$randstr;
			$sql['ctime']=time();
			if(is_array($cert_arr)){
				$where['uid']=$member_arr['uid'];
				$where['type']='2';
				$this->obj->update_once("company_cert",$sql,$where);
				$this->obj->member_log("手机登录验证码重新获取");
			}else{
				$sql['uid']=$member_arr['uid'];
				$sql['statusbody']='手机登录验证码';
				$sql['did']=$this->userdid;
				$sql['type']=2;
				$this->obj->insert_into("company_cert",$sql);
				$this->obj->member_log("手机登录验证码获取");
			}
		}
		return $result['msg'];
	}
		
	function logout($result=true){
		if($this->config['sy_uc_type']=="uc_center"){
			$this->uc_open();
			$logout = uc_user_synlogout();
			$this->cookie->unset_cookie();
			echo $logout;
		}elseif($this->config["sy_pw_type"]){
			include(APP_PATH."/api/pw_api/pw_client_class_phpapp.php");
			$username=$_SESSION["username"];
			$pw=new PwClientAPI($username,"","");
			$logout=$pw->logout();
			$this->cookie->unset_cookie();
		}else{
			$this->cookie->unset_cookie();
		}
		session_start();
		unset($_SESSION['qq']);
		unset($_SESSION['wx']);
		unset($_SESSION['sina']);
		
		
		if($result){echo 1;die;}
	}
	function del_dir($dir_adds='',$del_def=0) {
	    $result = false;
	    if(! is_dir($dir_adds)){
	   		return false;
	    }
	    $handle = opendir($dir_adds);
	    while(($file = readdir($handle)) !== false){
		    if($file != '.' && $file != '..') {
		        $dir = $dir_adds . DIRECTORY_SEPARATOR . $file;
		        is_dir($dir) ? $this->del_dir($dir) : @unlink($dir);
		    }
	    }
	    closedir($handle);
	    if($del_def==0){
			$result = @rmdir($dir_adds) ? true : false;
	    }else {
	    	$result = true;
	    }
	    return $result;
	}
	function seo($ident,$title='',$keyword='',$description=''){
		include PLUS_PATH."/seo.cache.php";
		$seo=$seo[$ident];
		if(is_array($seo)){
			foreach($seo as $k=>$v){
				if($this->config['did']!="" && $this->config['did']==$v['did']){
					$fzseo=$v;
				}else{
					$seo=$v;
				}
			}
			if($fzseo){
				$seo=$fzseo;
			}
		}
		$data=$this->data;
		
		if(is_array($seo)){
			$cityname = $this->config['cityname']?$this->config['cityname']:$this->config["sy_indexcity"];
			if($title){
				$this->config['sy_webname'] = $title;
				$seo['title'] = $title;
			}
			if($keyword){
				$this->config['sy_webkeyword'] = $keyword;
				$seo['keywords'] = $keyword;
			}
			if($description){
				$this->config['sy_webmeta'] = $description;
				$seo['description'] = $description;
			}
			foreach($seo as $key=>$value){
				$seo[$key] = str_replace("{webname}",$this->config['sy_webname'],$seo[$key]);
				$seo[$key] = str_replace("{webkeyword}",$this->config['sy_webkeyword'],$seo[$key]);
				$seo[$key] = str_replace("{webdesc}",$this->config['sy_webmeta'],$seo[$key]);
				$seo[$key] = str_replace("{weburl}",$this->config['sy_weburl'],$seo[$key]);
				$seo[$key] = str_replace("{city}",$cityname,$seo[$key]);

				if(is_array($data)){
					foreach($data as $k=>$v){
						$seo[$key] = str_replace("{".$k."}",$v,$seo[$key]);
					}
				}
				if(!@strpos('{seacrh_class}',$seo[$key])){
					$rdata=$this->get_search_class($ident,$key);
					if($rdata && !empty($rdata) && (count($rdata)==1&&$rdata[0])){
						$seo[$key] = str_replace("{seacrh_class}",$rdata,$seo[$key]);
					}else{
						$seo[$key] = str_replace("{seacrh_class}",'',$seo[$key]);
					}
				}
				$seo[$key]=str_replace('  ',' ',$seo[$key]);
				$seo[$key]=str_replace(array("-  -","- -"),'-',$seo[$key]);
				$seo[$key]=str_replace(array("-  -","- -"),'-',$seo[$key]);
			}
		}
		$this->yunset("title",$seo['title']);
		$this->yunset("keywords",$seo['keywords']);
		$this->yunset("description",mb_substr(str_replace("	","",str_replace("\r","",str_replace("\n","",strip_tags($seo['description'])))),0,200,'utf-8'));
	}
	
	function get_search_class($ident,$type="title"){
		include PLUS_PATH."/city.cache.php";
		include PLUS_PATH."/job.cache.php";
		include PLUS_PATH."/industry.cache.php";
		include(CONFIG_PATH."db.data.php");
		$uptime=array(1=>'今天',3=>'最近3天',7=>'最近7天',30=>'最近一个月','90'=>'最近三个月');
		if($ident=="com_search" || $ident=="part"){
			include PLUS_PATH."/com.cache.php";
		}
		if($ident=="user_search"){
			include PLUS_PATH."/user.cache.php";
		}
		
		$data = array();
		foreach($_GET as $key=>$v){
			switch($key){
				case "hy":
				$data[]=$industry_name[$v];
				break;
				case "job1":
				case "job1_son":
				case "job_post":
				$data[]=$job_name[$v];
				break;
				case "provinceid":
				case "cityid":
				case "three_cityid":
				$data[]=$city_name[$v];
				break;
				case "rec":
				$data[]='推荐';
				break;
				case "urgent":
				$data[]='紧急';
				break;
				case "pic":
				$data[]='照片';
				break;
				default:
				if(!in_array($key,array('idcard','work','cert'))){
					if($comdata["job_".$key]&&$comclass_name[$v]){
						$data[]=$comclass_name[$v];
					}else if($key=='salary'){
						$data[]=$v;
					}else if($key=='sex'&&$arr_data['sex'][$v]){
						$data[]=$arr_data['sex'][$v];
					}else if($key=='uptime'&&$uptime[$v]){
						$data[]=$uptime[$v];
					}else if(($userdata["user_".$key]||$key=='exp')&&$userclass_name[$v]){
						$data[]=$userclass_name[$v];
					} 
				}
				
				break;
			}
		}
		foreach($data as $key=>$val){
			if($val){
				$alldata[]=$val;
			}
		}
		if(is_array($alldata)){
			if($type=="title"){
				$data = implode('-',$alldata);
			}else{
				$data = implode(',',$alldata);
			}
		}
		return $data;
	}
	
	function assignhtm($contents,$id){
		$job_info = $this->obj->DB_select_alls("company_job","company","a.`state`='1' and a.`r_status`!=2 and a.`id`='$id' and b.`uid`=a.`uid`","a.*,b.*,a.name as comname,a.cityid as cityid,a.three_cityid as three_cityid");
		$job_info = $this->array_action($job_info[0]);
		if(is_array($job_info)){
			foreach($job_info as $key=>$value){
				$contents = str_replace("{yun:}".$key."{/yun}",$value,$contents);
			}
			$contents = str_replace("{yun:}now{/yun}",date("Y-m-d H:i:s"),$contents);
			$contents = str_replace("{yun:}sy_weburl{/yun}",$this->config['sy_weburl'],$contents);
			$contents = str_replace("{yun:}sy_webname{/yun}",$this->config['sy_webname'],$contents);
			$contents = str_replace("{yun:}comurljob{/yun}",Url("job",array("c"=>"comapply","id"=>$id),"1"),$contents);
			$contents = str_replace("{yun:}comurl{/yun}",Url('company',array("id"=>$job_info[uid])),$contents);
		}else{
			$contents = "";
		}
		return $contents;
	}
	function addkeywords($type,$keyword){
        $M=$this->MODEL();
		$info = $M->DB_select_once('hot_key',"`key_name`='$keyword' AND `type`='$type'");
		if(is_array($info)){
			$M->DB_update_all('hot_key',"`num`=`num`+1","`key_name`='".$keyword."' and `type`='".$type."'");
		}else{
			$M->insert_into('hot_key',array('key_name'=>$keyword,'num'=>1,'type'=>$type,'check'=>0));
		}
	}
	
	function addstate($content,$type=1,$uid=''){
		$uid=$this->uid?$this->uid:$uid;
		$this->MODEL()->insert_into('friend_state',array('uid'=>$uid,'content'=>$content,'type'=>$type,'ctime'=>time()));
	}
	
	function automsg($content,$uid){
        $M=$this->MODEL();
		$member=$M->DB_select_once('member',"`uid`='".$uid."'",'`username`');
		$M->insert_into('sysmsg',array('fa_uid'=>$uid,'content'=>$content,'username'=>$member['username'],'ctime'=>time(),'remind_status'=>0));
	}
	function post_trim($data){
		foreach($data as $d_k=>$d_v){
			if(is_array($d_v)){
				$data[$d_k]=$this->post_trim($d_v);
			}else{
				$data[$d_k]=trim($data[$d_k]);
			}
		}
		return $data;
	}
	
	function get_moblie(){
		if($this->config['sy_wap_web']=="2"){
			header("location:".$this->config['sy_weburl']);
		}
		$UA = strtoupper($_SERVER['HTTP_USER_AGENT']);
		if($this->config['sy_pc_jump_wap']!='1' && strpos($UA, 'WINDOWS NT') !== false){
			header("location:".Url('index',array('c'=>'wap')));
		}
		$now_url=@explode("/",$_SERVER['REQUEST_URI']);
		$now_url=$now_url[count($now_url)-1];

		$this->yunset("now_url",$now_url);
	}
	
	function rightinfo(){
		if($this->uid){
			if($this->usertype=='1'){
				$rightinfo=$this->obj->DB_select_once("resume","`uid`='".$this->uid."'","`photo`");
				if($rightinfo['photo']==''){
					$rightinfo['photo']=$this->config['sy_member_icon'];
				}
				$rightinfo['msgnum']=$this->obj->DB_select_num("userid_msg","`uid`='".$this->uid."'");
				$rightinfo['expectnum']=$this->obj->DB_select_num("resume_expect","`uid`='".$this->uid."'");
			}else if($this->usertype=='2'){
				$rightinfo=$this->obj->DB_select_once("company","`uid`='".$this->uid."'","`logo`");
				if($rightinfo['logo']==''){
					$rightinfo['logo']=$this->config['sy_unit_icon'];
				}
				$rightinfo['jobnum']=$this->obj->DB_select_num("company_job","`uid`='".$this->uid."'");
				$rightinfo['sqnum']=$this->obj->DB_select_num("userid_job","`com_id`='".$this->uid."'");

			}
			$this->yunset("rightinfo",$rightinfo);
		}
	}
	function send_dingyue($id,$type){
    $notice = $this->MODEL('notice');
		if($type=="2"){
			$job=$this->obj->DB_select_once("company_job","`id`='".$id."'","`name`,`hy`,`uid`");
			if($job['hy']>0){
				$user=$this->obj->DB_select_all("resume","FIND_IN_SET('".$job['hy']."',hy_dy)","`email_dy`,`msg_dy`,`email`,`telphone`,`uid`,`name`");
				if(is_array($user)&&$user){
          foreach($user as $v){
						$data['uid']=$v['uid'];
						$data['name']=$v['name'];
						$data['type']="userdy";
						$data['jobname']=$job['name'];
						if($v['email_dy']=="1"){
							$data['email']=$v['email'];
              $notice->sendEmailType($data);
						}
						if($v['msg_dy']=="1"){
							$data['moblie']=$v['telphone'];
              $notice->sendSMSType($data);
						}
					}
				}
			}
		}else{
			$expect=$this->obj->DB_select_once("resume_expect","`id`='".$id."'","`hy`,`name`");
			$user=$this->obj->DB_select_all("company","FIND_IN_SET('".$expect['hy']."',hy_dy)","`email_dy`,`msg_dy`,`uid`,`email`,`linktel`,`name`");
			if(is_array($user)&&$user){
				foreach($user as $v){
					$data['uid']=$v['uid'];
					$data['name']=$v['name'];
					$data['type']="comdy";
					$data['resumename']=$expect['name'];
					if($v['email_dy']=="1"){
						$data['email']=$v['email'];
						$notice->sendEmailType($data);
					}
					if($v['msg_dy']=="1"){
						$data['moblie']=$v['linktel'];
						$notice->sendSMSType($data);
					}
				}
			}
		}
	}
	
	function ACT_layer_msg($msg = "操作已成功！", $st = 9,$url='',$tm = 2,$type='0'){
		
		header("Content-Type: text/html; charset=UTF-8");
			
		echo '<meta charset="utf-8"/>';
        if(is_array($msg)){
            foreach($msg as $k=>$v){
                $Html.='<div id="'.$k.'">'.$v.'</div>';
            }
            echo $Html;die;
        }

		if($st==9&&$type=='1'){$this->MODEL('log')->admin_log($msg);}
		$msg = preg_replace('/\([^\)]+?\)/x',"",str_replace(array("（","）"),array("(",")"),$msg));
		echo '<input id="layer_url" type="hidden" value="'.$url.'"><input id="layer_msg" type="hidden" value="'.$msg.'"><input id="layer_time" type="hidden" value="'.$tm.'"><input id="layer_st" type="hidden" value="'.$st.'">';exit();
	}
	
	function layer_msg($msg,$st='9',$type='0',$url='',$tm='2',$error='0'){
		if($type=='1'){
			$this->ACT_layer_msg($msg,$st,$url);
		}else{
			if($st==9){$this->MODEL('log')->admin_log($msg);}
			$msg = preg_replace('/\([^\)]+?\)/x',"",str_replace(array("（","）"),array("(",")"),$msg));
			$layer_msg['msg']= $msg;
			$layer_msg['tm']=$tm;
			$layer_msg['st']=$st;
			$layer_msg['url']=$url;
			$layer_msg['error']=$error;
			$msg = json_encode($layer_msg);

			
			header("Content-Type: text/html; charset=UTF-8");

			echo $msg;die;
		}
	}
	
	
	function CheckAppUser(){
		if(isset($_POST["username"]) && isset($_POST["desname"]) && isset($_POST["desword"])){
			$desname = trim($_POST["desname"]);
			$desword = trim($_POST["desword"]);
			$uid = intval($_POST["uid"]);
			unset($_POST["desname"]);unset($_POST["desword"]);
			if($uid){
				$User = $this->obj->DB_select_once('member',"`uid`='".$uid."'","`salt`,`password`");
			}else{
				$User = $this->obj->DB_select_once('member',"`username`='".$desname."'","`salt`,`password`");
			}
			
		}
		if($User['password'] && $User['salt'] &&( md5(md5($desword).$User['salt'])==$User['password'])){
			return $_POST;
		}else{
			$data['error']=1008;
			echo json_encode($data);
			exit();
		}
	}
	function wapheader($url,$point=''){
        if(!($this->config['sy_wapdomain'])){
            $sy_wapdomain=$this->config['sy_weburl'].'/'.$this->config['sy_wapdir'];
        }else{
			if($this->config['sy_wapssl']=='1'){
				$protocol = 'https://';
			}else{
				$protocol = 'http://';
			}
			if(strpos($this->config['sy_wapdomain'],'http://')===false && strpos($this->config['sy_wapdomain'],'https://')===false)
			{
				$sy_wapdomain = $protocol.$this->config['sy_wapdomain'];
			}else{
				$sy_wapdomain = $this->config['sy_wapdomain'];
			}


        }
        $url=$sy_wapdomain."/".$url;
        if($point!=''){
            $point = 'point='.$point;
        }
        header('Location: '.$url.$point);
        exit();
	}
	function wapheaderLayer($url,$point=''){
		if(!($this->config['sy_wapdomain'])){
			$sy_wapdomain=$this->config['sy_weburl'].'/'.$this->config['sy_wapdir'];
		}else{
			if($this->config['sy_wapssl']=='1'){
				$protocol = 'https://';
			}else{
				$protocol = 'http://';
			}
			if(strpos($this->config['sy_wapdomain'],'http://')===false && strpos($this->config['sy_wapdomain'],'https://')===false)
			{
				$sy_wapdomain = $protocol.$this->config['sy_wapdomain'];
			}else{
				$sy_wapdomain = $this->config['sy_wapdomain'];
			}
		}
		$url=$sy_wapdomain."/".$url;
		if($point!=''){
			$point = 'layer='.$point;
		}
		header('Location: '.$url.$point);
		exit();
	}
	
	function check_token(){
		if($this->config['sy_iscsrf']!='2'){
			if($_SESSION['pytoken']!=$_GET['pytoken'] || !$_SESSION['pytoken']){
				unset($_SESSION['pytoken']);
				$this->ACT_layer_msg("来源地址非法！",8,'index.php');
				exit();
			}
		}

	}
	
	function job_auto(){
		if($this->config['autodate'] != date('Ymd')){

		
			if(($this->config['sy_autoref']>0 && date('G')>=$this->config['sy_autoref']) || ($this->config['sy_autoref']<1)){
								
				if($this->config['sy_autorefrand']=='1'){
					
					$count = $this->obj->DB_select_num("company_job","`autotime`>='".strtotime(date('Y-m-d'))."'");
					$size = 1000; 
					
					$num = ceil($count/$size); 
					for($i=0;$i<$num;$i++){
						$offset = $i * $size;
						$autoList = $this->obj->DB_select_all("company_job","`autotime`>='".strtotime(date('Y-m-d'))."' limit {$offset},{$size} ","`id`");
					
						$jobId = array();
						$SqlCase = 'lastupdate = CASE id ';
						foreach($autoList as $key=>$value){
							
							$jobId[] = $value['id'];
							$LastTime = strtotime('-'.rand(1,59).' minutes', time());

							$SqlCase .= sprintf("WHEN %d THEN %d ", $value['id'], $LastTime); 

						}
						$SqlCase .= 'END';

						$nid = $this->obj->DB_update_all("company_job",$SqlCase,"`id` IN (".@implode(',',$jobId).")");
					}
					$msg = '，本次刷新职位数：'.$count;
				}else{
					$sqlCase ="`lastupdate` =  '".time()."' ";
					$nid = $this->obj->DB_update_all('company_job',$sqlCase,"`autotime`>='".strtotime(date('Y-m-d'))."'");
					
				}
				if($nid){
					
					$this->MODEL('log')->admin_log('系统执行职位自动刷新任务'.$msg);
					
				}
				
				$this->obj->DB_delete_all("admin_config","`name`='autodate'"," ");
				$this->obj->DB_insert_once("admin_config","`config`='".date('Ymd')."',`name`='autodate'");
					 
				$this->web_config();
			}
		}
	}

	function insertfinder($para,$id='',$name='',$noiconv=''){
		$data['name']=$name;
		$data['uid']=$this->uid;
		$data['para']=$para;
		
		$M=$this->MODEL();
		if($id){
			$M->member_log("修改搜索器");
			return $M->update_once("finder",$data,"`id`='".$id."'");
		}else{
			$data['usertype']=$this->usertype;
			$data['addtime']=time();
			$M->member_log("添加搜索器");
			return $M->insert_into("finder",$data);
		}
	}

  
	function stringfilter($string){
		$str=trim($string);

		$regex = "/\\$|\'|\\\|/";
		$str=preg_replace($regex,"",$str);
		return $str;
	}
	function CheckIdCard($idcard){
		if(!preg_match("/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/",$idcard)){
			return false;
		}else{
			return true;
		}
	}

	
	function forsend($data){
		if($data['usertype']=='1'){
			$info=$this->obj->DB_select_once("resume","`uid`='".$data['uid']."'","`name`,`uid`");
		}
		if($data['usertype']=='2'){
			$info=$this->obj->DB_select_once("company","`uid`='".$data['uid']."'","`name`,`uid`");
		}
		
		
		return $info;
	}
	 
	function subscribe(){
		$time=strtotime(date("Y-m-d"));
		if($this->config['subscribe_time']<$time){
			$subscribe = $this->obj->DB_select_all("subscribe","status='1'");
			if(is_array($subscribe)){
				foreach($subscribe as $v){
					$time=86400*$v['time']+$v['ctime'];
					if($time<time()){
						if($v['type']=="1"){
							$this->select_job($v);
						}else{
							$this->select_resume($v);
						}
					}
				}
			}
			$subscribe_time = $this->obj->DB_select_num("admin_config","`name`='subscribe_time'");
			if($subscribe_time>1){
				$this->obj->DB_delete_all("admin_config","`name`='subscribe_time'"," ");
				$this->obj->DB_insert_once("admin_config","`config`='".time()."',`name`='subscribe_time'");
			}else{
				
				if($subscribe_time>0){
					$this->obj->DB_update_all("admin_config","`config`='".time()."'","`name`='subscribe_time'");
				}else{
					$this->obj->DB_insert_once("admin_config","`config`='".time()."',`name`='subscribe_time'");
				}
			}
			
			$this->web_config();
		}
	}
	function select_job($v){
		$where="`state`='1' and `job1`='".$v['job1']."' and `job1_son`='".$v['job1_son']."' and `job_post`='".$v['job_post']."' and `provinceid`='".$v['provinceid']."'";
		if($v['salary']>"0"){
			$where.=" and `salary`='".$v['salary']."'";
		}
		if($v['cityid']>"0"){
			$where.=" and `cityid`='".$v['cityid']."'";
		}
		if($v['three_cityid']>"0"){
			$where.=" and `three_cityid`='".$v['three_cityid']."'";
		}
		$job=$this->obj->DB_select_all("company_job",$where." order by sdate desc limit 5","name");
		$data=array();
		if(!empty($job)){
			foreach($job as $val){
				$name[]=$val['name'];
			}
			$data['jobname']=@implode(",",$name);
			$data['email']=$v['email'];
			$data['type']="userdy";
      $notice = $this->MODEL('notice');
      $notice->sendEmailType($data);
		}
		
		$this->obj->DB_insert_once("subscriberecord","`sid`='".$v['id']."',`uid`='".$v['uid']."',`type`='".$v['type']."',`content`='".$data['jobname']."',`time`='".time()."'");
		
		$this->obj->DB_update_all("subscribe","`ctime`='".time()."'","`id`='".$v['id']."'");
	}
	function select_resume($v){
		$where = "a.status<>'2' and a.`r_status`<>'2' and b.`open`='1' and a.`uid`=b.`uid`";
		$where.=" and b.provinceid='".$v['provinceid']."' and FIND_IN_SET('".$v['job_post']."',b.job_classid)";
		if($v['salary']>"0"){
			$where.=" and b.`salary`='".$v['salary']."'";
		}
		if($v['cityid']>"0"){
			$where.=" and b.`cityid`='".$v['cityid']."'";
		}
		if($v['three_cityid']>"0"){
			$where.=" and b.`three_cityid`='".$v['three_cityid']."'";
		}
		$userlist=$this->obj->DB_select_alls("resume","resume_expect",$where." order by b.lastupdate desc limit 5","b.name");
		$data=array();
		if(is_array($userlist)){
			foreach($userlist as $val){
				$name[]=$val['name'];
			}
			$data['resumename']=@implode(",",$name);
			$data['email']=$v['email'];
			$data['type']="comdy";
      $notice = $this->MODEL('notice');
      $notice->sendEmailType($data);
		}
		
		$this->obj->DB_insert_once("subscriberecord","`sid`='".$v['id']."',`uid`='".$v['uid']."',`type`='".$v['type']."',`content`='".$data['resumename']."',`time`='".time()."'");
		
		$this->obj->DB_update_all("subscribe","`ctime`='".time()."'","`id`='".$v['id']."'");
	}

    public function MODEL($ModelName=null,$ModelPath=null){
        require_once(APP_PATH.'app/public/action.class.php');
        if($ModelName){
            if($ModelPath){
                if(file_exists($ModelPath.'/'.$ModelName.'.class.php')){
                    require_once($ModelPath.'/'.$ModelName.'.class.php');
                }else{
                    return null;
                }
            }else{
                $ModelPath=APP_PATH.'app/model/';
                $ModelFileName=$ModelName.'.model.php';
                if(file_exists($ModelPath.$ModelFileName)){
                    require_once($ModelPath.$ModelFileName);
                }else{
                    return null;
                }
            }
            $ModelName=$ModelName.'_model';
        }else{
            $ModelName='model';
          
        }
		$Model=new $ModelName($this->db,$this->def,array('uid'=>$this->uid,'username'=>$this->username,'usertype'=>$this->usertype));
        return $Model;
    }
	public function get_admin_msg($url, $show = '操作已成功！') {
		$msg = '<!DOCTYPE html PUBLIC "-
		<html xmlns="http://www.w3.org/1999/xhtml"><head>
				<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
				<meta http-equiv="refresh" content="2; URL=' . $url . '" />
				<title>消息提示 Powered by PHPYUN_JOB!</title>
				<style>
				a,a:visited{
				color:#0066FF; text-decoration:none;
				}
				a:hover{
				color:blue; text-decoration:underline;
				}
				</style>
				</head>
				<body style="font-size:12px;">
				<div id="man_zone">
				  <table width="30%" border="0" align="center"  cellpadding="0" cellspacing="1" class="table" bgcolor="#dfdfdf" style="margin-top:100px;">
				    <tr>
				      <th height="25" align="center"><font style="font-size:12px;" color="#000">信息提示</font></th>
				    </tr>
				    <tr>
				      <td bgcolor="#FFFFFF"><p style="line-height:20px;">&nbsp;<font color=red>' . $show . '</font><br />
				      &nbsp;2秒后返回指定页面！<br />
				      &nbsp;如果浏览器无法跳转，<a href="' . $url . '">请点击此处</a>。</p></td>
				    </tr>
				  </table>
				</div>
				</body>
				</html>';
		echo $msg;
		exit ();
	}
	
	function GET_content_desc($cont){
	    return mb_substr(strip_tags($cont),0,200,"utf-8");
	}
	
	
	function ACT_msg($url='', $msg = "操作已成功！", $st = 8,$tm = 3) {
		if($url==''){
			$url=$this->config['sy_weburl'];
		}
		$this->yunset(array('msg'=>$msg,'url'=>$url));
 		if(isMobileUser()){
  			$this->yuntpl(array('wap/msg'));	
		}else{
    		$this->yuntpl(array('member/msg'));
		}
		exit();
	}
	
	function ACT_msg_wap($url='', $msg = "操作已成功！", $st = 8,$tm = 3) {
		if($url==''){
			$url=$this->config['sy_weburl'];
		}
		$this->yunset(array('msg'=>$msg,'url'=>$url));
  		$this->yuntpl(array('wap/msg'));	
		exit();
	}
	
	
	function GET_web_default($id,$power){
		if($this->config['sy_web_site']=='1'&&$this->config['did']){
			$where=" and `dids`='1'";
		}
		$web=$this->obj->DB_select_all("admin_navigation","`keyid` in (".implode(",",$id).") order by `sort` asc");
		if(is_array($web)){
			foreach($web as $v){
				if(@in_array($v['id'],$power)){
					$arr[]=$v['id'];
					$arr2[$v['id']]=$v['keyid'];
				}
			}
			$webaa=$this->obj->DB_select_all("admin_navigation","`keyid` in (".implode(",",$arr).") ".$where." order by `sort` asc");
			if(is_array($webaa)){
				foreach($webaa as $va){
					if(@in_array($va['id'],$power)){
						$value[$arr2[$va['keyid']]]=$va['url'];
					}
				}
			}
		}
		return $value;
	}
	
	function get_shell($mid,$web,$type=""){
		
		
		$row=$this->obj->DB_select_alls("admin_user","admin_user_group","a.`m_id`=b.`id` and b.`id`='$mid'");

		$power=unserialize($row[0]['group_power']);

		$row=$this->obj->DB_select_once("admin_navigation","`url`='$web'");

		return @in_array($row['id'],$power)?true:false;
		
	}
	
	
	function GET_user_shell($uid,$shell) {
		if(!preg_match("/^\d*$/",$uid)){return false;}
		if(!preg_match("/^\d*$/",$_COOKIE['usertype'])){return false;}
		$SQL="SELECT * FROM `".$this->def."member` WHERE `uid`='$uid' AND `usertype`='".$_COOKIE['usertype']."' limit 1";
		$query = $this->db->query($SQL);
		$us = is_array($row = $this->db->fetch_array($query));
		
		if($row['usertype'] == $_COOKIE['usertype']){
			$shell = $us ? $shell == md5($row['username'].$row['password'].$row['salt']):FALSE;
		}else{
			$shell = FALSE;
		} 
		return $shell ? $row : NULL;
	}
	
	

    function GET_web_check($id){
		$nav=$this->obj->DB_select_once("admin_navigation","`id`='$id'");
		if(is_array($nav)){
			$value.=$this->GET_web_check($nav['keyid']);
			$value.=$nav['name']." - ";
		}
		return $value;
	}
	function uc_open()
	{
		include APP_PATH.'data/api/uc/config.inc.php';
		include APP_PATH.'/api/uc/include/db_mysql.class.php';
		include APP_PATH.'/api/uc/uc_client/client.php';

		return $ucinfo;
	}

	function max_time($remark){

		
		$num=$this->obj->DB_select_num("company_pay","`com_id`='".$this->uid."' AND `pay_remark`='".trim($remark)."' AND FROM_UNIXTIME(pay_time, '%Y-%m-%d') = '".date('Y-m-d')."'"); 
		if($num>0){
			return false;
		}else{
			return true;
		}
	}
	function CloseTags($html){
		$html = preg_replace('/<[^>]*$/','',$html);
		preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
		$opentags = $result[1];
		preg_match_all('#</([a-z]+)>#iU', $html, $result);
		$closetags = $result[1];
		$len_opened = count($opentags);
		if(count($closetags) == $len_opened) {
			return $html;
		}
		$opentags = array_reverse($opentags);
		$sc = array('br','input','img','hr','meta','link');
		for ($i=0; $i < $len_opened; $i++){
			$ot = strtolower($opentags[$i]);
			if (!in_array($opentags[$i], $closetags) && !in_array($ot,$sc)){
				$html .= '</'.$opentags[$i].'>';
			}else{
				unset($closetags[array_search($opentags[$i], $closetags)]);
			}
		}
		return $html;
	}
	
	function checksrc($post,$url='',$path=''){
	    $src = substr($post, strpos($post,'data/upload/'));
	    if (file_exists(APP_PATH.$src) && $post!=$url){
	        $picurl = $post;
	        if ($url!=''){
	            unlink_pic('../'.substr($url, strpos($url,'data/upload/')));
	        }
	        
	        if ($path!=''){
	            $UploadM=$this->MODEL('upload');
	            $upload=$UploadM->Upload_pic("./data/upload/".$path."/");
	            $picurl=$upload->movepic($src);
	            unlink_pic('../'.$src);
	        }
	    }else{
	        $picurl = $url;
	    }
	    return $picurl;
	}
	function morecity_insert($eid,$city_class,$uid){
        include PLUS_PATH."city.cache.php";
        include PLUS_PATH."cityfs.cache.php";
        $csclass = @explode(',', $city_class);
        foreach ($csclass as $v){
            $morecity=array();
            $morecity['eid']=$eid;
            $morecity['cityid']=0;
            $morecity['three_cityid']=0;
            if (in_array($v, $city_index)){
                $morecity['provinceid']=$v;
            }elseif (in_array($v, $city_two)){
                $morecity['cityid']=$v;
                foreach ($city_type as $k2=>$v2){
                    if (in_array($v, $v2)){
                        $morecity['provinceid']=$k2;
                    }
                }
            }elseif (in_array($v, $city_three)){
                $morecity['three_cityid']=$v;
                foreach ($city_type as $k32=>$v32){
                    if (in_array($v, $v32)){
                        $morecity['cityid']=$k32;
                        foreach ($city_type as $k3=>$v3){
                            if (in_array($k32, $v3)){
                                $morecity['provinceid']=$k3;
                            }
                        }
                    }
                }
            }
            $resume_city[]=$morecity;
        }
        if ($resume_city){
            $list = array();
            foreach ($resume_city as $k=>$v){
                $list[$k]['eid']=$v['eid'];
                $list[$k]['uid']=$uid;
                $list[$k]['provinceid']=$v['provinceid'];
                $list[$k]['cityid']=$v['cityid'];
                $list[$k]['three_cityid']=$v['three_cityid'];
            }
            foreach ($list as $v){
                $newval[] ='(\''.@implode('\',\'',$v).'\')';
            }
            $newkey=array('eid','uid','provinceid','cityid','three_cityid');
            include CONFIG_PATH."db.config.php";
            $sql = "INSERT INTO `".$db_config['def']."resume_cityclass` (`".@implode('`,`',$newkey)."`) VALUES ".@implode(',',$newval);
            $this->obj->DB_query_all($sql);
        }
    }
    
    function morejob_insert($eid,$job_class,$uid){
        include PLUS_PATH."job.cache.php";
        include PLUS_PATH."jobfs.cache.php";
        $csclass = @explode(',', $job_class);
        foreach ($csclass as $v){
            $morejob=array();
            $morejob['eid']=$eid;
            $morejob['job1_son']=0;
            $morejob['job_post']=0;
            if (in_array($v, $job_index)){
                $morejob['job1']=$v;
            }elseif (in_array($v, $job_two)){
                $morejob['job1_son']=$v;
                foreach ($job_type as $k2=>$v2){
                    if (in_array($v, $v2)){
                        $morejob['job1']=$k2;
                    }
                }
            }elseif (in_array($v, $job_three)){
                $morejob['job_post']=$v;
                foreach ($job_type as $k32=>$v32){
                    if (in_array($v, $v32)){
                        $morejob['job1_son']=$k32;
                        foreach ($job_type as $k3=>$v3){
                            if (in_array($k32, $v3)){
                                $morejob['job1']=$k3;
                            }
                        }
                    }
                }
            }
            $resume_job[]=$morejob;
        }
        if ($resume_job){
            $list = array();
            foreach ($resume_job as $k=>$v){
                $list[$k]['eid']=$v['eid'];
                $list[$k]['uid']=$uid;
                $list[$k]['job1']=$v['job1'];
                $list[$k]['job1_son']=$v['job1_son'];
                $list[$k]['job_post']=$v['job_post'];
            }
            foreach ($list as $v){
                $newval[] ='(\''.@implode('\',\'',$v).'\')';
            }
            $newkey=array('eid','uid','job1','job1_son','job_post');
            include CONFIG_PATH."db.config.php";
            $sql = "INSERT INTO `".$db_config['def']."resume_jobclass` (`".@implode('`,`',$newkey)."`) VALUES ".@implode(',',$newval);
            $this->obj->DB_query_all($sql);
        }
    }
}
?>