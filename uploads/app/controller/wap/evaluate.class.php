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
class evaluate_controller extends common{
	function index_action(){
		$M=$this->MODEL('evaluate'); 
		$urlarr['c']="evaluate";
		$urlarr['page']="{{page}}";
		$pageurl=Url('wap',$urlarr);
		$rows = $this->get_page("evaluate_group","`keyid`>0 order by `ctime` desc",$pageurl,"10");
        $this->yunset($rows);
		$this->seo("evaluate");
		$this->yunset("headertitle","职业测评");
		$this->yuntpl(array('wap/evaluatelist'));
	}
	function show_action(){
		$M = $this->MODEL('evaluate'); 
		$id = intval($_GET['id']); 
		$info=$M->GetExamBaseInfo(array('id'=>$id));  
		$info['pic'] = $this->config['sy_weburl']."/".$info['pic'];  
		if($info['id']==''){
			$this->ACT_msg_wap(Url("wap",array('c'=>"evaluate")),"没有找到相关测评哦！");
		} 
		$questions = $M->GetQuestions(array('gid'=>$id));
		
		$data['exampaper']=$info['name'];		
		$this->data=$data;
		$this->yunset('info',$info);
		$this->yunset('questions',$questions);		
		$this->seo('exampaper');
		$this->yunset("headertitle","职业测评");
		$this->yuntpl(array('wap/evaluateshow'));
	}
	function paper_action(){
		$M = $this->MODEL('evaluate'); 
		$id = intval($_GET['id']); 
		$info=$M->GetExamBaseInfo(array('id'=>$id));  
		$info['pic'] = $this->config['sy_weburl']."/".$info['pic'];  
		if($info['id']==''){
			$this->ACT_msg_wap(Url("wap",array('c'=>"evaluate")),"没有找到相关测评哦！");
		}
		$arr=array();
		$questions = $M->GetQuestions(array('gid'=>$id));
		foreach($questions as $key=>$val){
			$questions[$key]['option']= mb_unserialize($val['option']);
			$questions[$key]['score']= mb_unserialize($val['score']);
			$questions[$key]['letters']=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			$arr[]=$val['id'];
 		}   
		$visits = $info['visits']+'1';
		$M->UpdateExamBaseInfo(array('visits'=>$visits),array('id'=>$id));	 
		  
		$data['exampaper']=$info['name'];		
		$this->data=$data;
		$this->yunset('arr',"['".@implode("','",$arr)."']");
		$this->yunset('info',$info);
		$this->yunset('questions',$questions);	
		$this->seo('exampaper');
		$this->yunset("headertitle","职业测评");
		$this->yuntpl(array('wap/evaluatepaper'));
	}
	
	function grade_action(){
		$M=$this->MODEL('evaluate');
		if($_POST['examid']){
			
			$uid = $this->uid;
			$uname = $this->username;
			$examid = (int)$_POST['examid'];
			$questions = $M->GetQuestions(array('gid'=>$examid)); 
			$info=$M->GetExamBaseInfo(array('id'=>$examid));
			$score=$pid=array();
			foreach($questions as $val){
				$pid[]=$val['id'];
				$score['q'.$val['id']]= mb_unserialize($val['score']);
			}  
			$scores=0;
			foreach($pid as $val){  
				$scores+=$score['q'.$val][$_POST['q'.$val]]; 
			}  
			if($this->uid &&$this->username){
				$uid=$this->uid;
				$type='uid';
			}else if($_COOKIE['nuid']){ 
				$uid=$_COOKIE['nuid']; 
				$type='nuid';
			}else{
				$uid=$this->create_uuid();
				$type='nuid';
				$this->cookie->setcookie("nuid",$uid,time()+3600); 
			}
			$result = $M->GetGradeOne(array($type=>$uid,'examid'=>$examid));
			if($result['id']){
				$M->SaveGrade(array('grade'=>$scores,'ctime'=>time()),array('id'=>$result['id']));
			}else{
				$result['id']=$M->SaveGrade(array($type=>$uid,'examid'=>$examid,'grade'=>$scores,'ctime'=>time()));
			} 
			$this->layer_msg('提交成功！',9,0,Url("wap",array('c'=>"evaluate","a"=>'gradeshow',"id"=>$result['id'])),2);
		}else{
			$this->ACT_msg_wap(Url("wap",array('c'=>"evaluate")),"没有找到相关测评哦！");
		}
	}
	function gradeshow_action(){ 
		$M=$this->MODEL('evaluate');
		$id=(int)$_GET['id']; 
		if($this->uid &&$this->username){
			$uid=$this->uid;
			$info=$M->GetGradeOne(array("id"=>$id,"uid"=>$uid));
		}else{
			$uid=$_COOKIE['nuid'];
			$info=$M->GetGradeOne(array("id"=>$id,"nuid"=>$uid));
		}
		if($info['id']==''){
			$this->ACT_msg_wap(Url("wap",array('c'=>"evaluate")),"试卷不存在哦！");
		}
		$exambase=$M->GetExamBaseInfo(array('id'=>$info['examid']));
		$comment = $this->getComment($exambase,$info['grade']);
		$exambase['fromscore'] = mb_unserialize($exambase['fromscore']);
		$exambase['toscore'] = mb_unserialize($exambase['toscore']);
		$exambase['comment'] = mb_unserialize($exambase['comment']);
		
		$this->yunset('exambase',$exambase); 
		$this->yunset('comment',$comment);
		$this->yunset('info',$info);
		$data['exampaper']=$exambase['name'];		
		$this->data=$data;
		$this->seo('gradeshow');
		$this->yunset("headertitle","职业测评");
		$this->yuntpl(array('wap/evaluategradeshow'));
	}
	
	function getComment($examBaseInfo,$grade){
		$comment = '';
		
		$examBaseInfo['fromscore'] = mb_unserialize($examBaseInfo['fromscore']);
		$examBaseInfo['toscore'] = mb_unserialize($examBaseInfo['toscore']);
		$examBaseInfo['comment'] = mb_unserialize($examBaseInfo['comment']);
		
		for($i=0; $i<count($examBaseInfo['fromscore']); $i++){
			if($examBaseInfo['fromscore'][$i]<=$grade && $grade<= $examBaseInfo['toscore'][$i]){
				$comment = $examBaseInfo['comment'][$i];
				brake;
			}
		}
		return $comment;
	} 
	function create_uuid($prefix = "yun"){    
		$str = md5(uniqid(mt_rand(), true));   
		$uuid  = substr($str,0,12);   
		return $prefix.$uuid; 
	}
}
?>