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
		
		if(!$this->config['did'] && $this->config['sy_gotocity']=='1' && !$_COOKIE['gotocity']){
			include(PLUS_PATH."domain_cache.php");
			go_to_city($site_domain);
		}
			$this->get_moblie();
			$this->seo("index");
			$this->yunset('indexnav',1);
			$this->yuntpl(array('wap/index'));
	
	}

	function loginout_action(){
		$this->cookie->unset_cookie();
		$this->wapheader('index.php');
	}
}
?>