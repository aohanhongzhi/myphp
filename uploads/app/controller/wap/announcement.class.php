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
class announcement_controller extends common{
	function index_action(){
		$id=(int)$_GET['id'];
		$M=$this->MODEL('announcement');
		$row=$M->GetAnnouncementOne(array("id"=>$id));
		$row['content']=str_replace(array("&nbsp;","&"),array(" ","&amp;"),$row['content']);
		preg_match_all('/<img(.*?)src=("|\'|\s)?(.*?)(?="|\'|\s)/',$row['content'],$res);
		
        if(!empty($res[3])){
            foreach($res[3] as $v){
                if(strpos($v,'http:')===false && strpos($v,'https:')===false){
                  
                    $row['content'] = str_replace($v,$this->config['sy_weburl'].$v,$row['content']);
                }
            }
        }
		$this->yunset("row",$row);
		
		$data['gg_title']=$row['title'];
		$data['gg_desc']=$this->GET_content_desc($row['description']);
		$this->data=$data;
	    $this->seo("announcement");

		$this->yunset("headertitle","网站公告");
		$this->yuntpl(array('wap/announcements'));
	}	
}
?>