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

 
class adminCommon extends common{
 	function admin(){
		$r=$this->get_admin_user_shell(); 
		
 		if($this->config['sy_iscsrf']!='2'){
			if(!$_SESSION['pytoken']){
				$_SESSION['pytoken'] = substr(md5(uniqid().$_SESSION['auid'].$_SESSION['ausername'].$_SESSION['ashell']), 8, 12);
			}
			if($_POST){
				if($_POST['pytoken']!=$_SESSION['pytoken']){
					$this->ACT_layer_msg("来源地址非法！",8,$this->config['sy_weburl']);
				}
				unset($_POST['pytoken']);
			}
			$this->yunset('pytoken',$_SESSION['pytoken']);
		}
  }

   function wapadmin(){
		$UA = strtoupper($_SERVER['HTTP_USER_AGENT']);
		if(strpos($UA, 'WINDOWS NT') !== false){
 		}
		
		if($_SESSION['wuid'] || $_GET['c']){
			$shell = $this->admin_get_user_shell($_SESSION['wuid'],$_SESSION['wshell']);

			if(!is_array($shell)){
				unset($_SESSION['authcode']);
				unset($_SESSION['wuid']);
				unset($_SESSION['wusername']);
				unset($_SESSION['wshell']);
				unset($_SESSION['md']);
				unset($_SESSION['tooken']);
				unset($_SESSION['xsstooken']);
				header("location:".$this->config['sy_weburl'].'/wapadmin/');
				exit();
			}
		}
	}
  
  function get_admin_user_shell(){
 		if($_SESSION['auid'] && $_SESSION['ashell']){
			$row=$this->admin_get_user_shell($_SESSION['auid'],$_SESSION['ashell']);

			if(!$row){
        $this->adminlogout();
        exit("登录超时，请刷新后重新登录！");
			}

			if($_GET['m']=="" || $_GET['m']=="index" || $_GET['m']=="ajax" || $_GET['m']=="admin_nav"){$_GET['m']="admin_right";}
			if($this->config['did']&&$_GET['a']){
				$_GET['c']=$_GET['a'];
			}
			$c=$_GET['c'];
			$m=$_GET['m'];
			if($_GET['m']!="admin_right"){
 				$url="index.php?m=".$m;
				$c_array=array("cache","markcom","markuser","advertise","zhaopinhui","admin_user","wx","admin_siteadmin");
				if($c && $c!='savagroup'&& in_array($m,$c_array)){
					if($m=='admin_user' && $c == 'pass'){
						$c ='myuser';
					}
					$url.="&c=".$c;
                    $info=$this->obj->DB_select_once("admin_navigation","`url`='".$url."'");
					if(empty($info)){
						$url="index.php?m=".$m;
					}
				}

				$nav=$this->get_shell($row["m_id"],$url);
				if(!$nav){
 				  echo "登录超时，请刷新后重新登录！";
				  exit();
				}

				if(is_numeric($this->config['did'])){
				  if($m=="admin_user"){
					$where="(`url`='index.php?m=admin_user&c=pass' or `url`='index.php?m=admin_user&c=myuser') and `dids`=1";
				  }else{
					$where="`url`='".$url."' and `dids`=1";
				  }
				  $info=$this->obj->DB_select_once("admin_navigation",$where);
				}else{
							$info=$this->obj->DB_select_once("admin_navigation","`url`='".$url."'");
				}
				
				if(!$info){
				  echo "登录超时，请刷新后重新登录！";
							exit();
				}
			}
		}else{
			if($_GET['m']!=""){
				$this->adminlogout();
				 echo "登录超时，请刷新后重新登录！";
				 exit();
			}
		}
  }

 	function adminlogout(){
		unset($_SESSION['authcode']);
		unset($_SESSION['auid']);
		unset($_SESSION['ausername']);
		unset($_SESSION['ashell']);
		unset($_SESSION['md']);
		unset($_SESSION['tooken']);
		unset($_SESSION['xsstooken']);
	}
  
 
	function admin_get_user_login($username,$password,$url='index.php') {
		global $config;
		$username = str_replace(" ", "", $username);
		if($config['sy_web_site']=='1'){
			$query = $this->db->query("SELECT * FROM `".$this->def."admin_user` WHERE `username`='$username' and (`did`='".$config['did']."' or `isdid`='1') and `status` = 1 limit 1");
		}else{
			$query = $this->db->query("SELECT * FROM `".$this->def."admin_user` WHERE `username`='$username' and `status` = 1 limit 1");
		}

		$us = is_array($row = $this->db->fetch_array($query));
		$ps = $us ? md5($password) == $row['password'] : FALSE;
		
		if($ps){
			$this->cookie->setCookie("lasttime",$row['lasttime'],time()+ 80000);
			$_SESSION['auid']=$row['uid'];
			$_SESSION['ausername']=$row['username'];
			$_SESSION['xsstooken'] = sha1($config['sy_safekey']);
			$_SESSION['ashell']=md5($row['username'] . $row['password'] . $this->md);
			$this->cookie->setCookie("ashell", md5($row['username'] . $row['password'] . $this->md), time() + 80000);
			$this->obj->DB_update_all("admin_user","`lasttime`='".time()."'","`uid`='".$row['uid']."'");
			$this->ACT_layer_msg("登录成功！",9,$url);
			
		} else {
			$this->ACT_layer_msg("密码或用户错误！",8,$url);
		}
	}

   
	function wapadmin_get_user_login($username,$password,$url='index.php') {
		global $config;
		$username = str_replace(" ", "", $username);
		if($config['sy_web_site']=='1'){
			$query = $this->db->query("SELECT * FROM `".$this->def."admin_user` WHERE `username`='$username' and (`did`='".$config['did']."' or `isdid`='1') limit 1");
		}else{
			$query = $this->db->query("SELECT * FROM `".$this->def."admin_user` WHERE `username`='$username' limit 1");
		}

		$us = is_array($row = $this->db->fetch_array($query));
		$ps = $us ? md5($password) == $row['password'] : FALSE;
		
		if($ps){

			$_SESSION['wuid']=$row['uid'];
			$_SESSION['wusername']=$row['username'];
			$_SESSION['xsstooken'] = sha1($config['sy_safekey']);
			$_SESSION['wshell']=md5($row['username'] . $row['password'] . $this->md);
			$this->cookie->setCookie("wshell", md5($row['username'] . $row['password'] . $this->md), time() + 80000);
			$this->obj->DB_update_all("admin_user","`lasttime`='".time()."'","`uid`='".$row['uid']."'");
			return true;
			
		} else {
			return false;
		}
  }
   
	function admin_get_user_shell($uid,$shell){
        global $config;
        if(!preg_match("/^\d*$/",$uid)){return false;}
        if($config['sy_web_site']=='1'){
          $query = $this->db->query("SELECT * FROM `".$this->def."admin_user` WHERE `uid`='$uid' and (`did`='".$config['did']."' or `isdid`='1') limit 1");
        }else{
          $query = $this->db->query("SELECT * FROM `".$this->def."admin_user` WHERE `uid`='$uid'  limit 1");
        }
    
        $us = is_array($row = $this->db->fetch_array($query));
        $shell = $us ? $shell == md5($row['username'].$row['password'].$this->md):FALSE;
        return $shell ? $row : NULL;
    }
    
    function logo_reset($name,$value){
        $config = $this->obj->DB_select_once("admin_config","`name`='$name'");
        if (!$config){
            $this->obj->DB_insert_once("admin_config","`config`='$value',`name`='$name'");
        }elseif ($config['config'] != $value){
            $logo = $this->checksrc($value,$config['config']);
            $this->obj->DB_update_all("admin_config","`config`='$logo'","`name`='$name'");
        }
    }
}
?>