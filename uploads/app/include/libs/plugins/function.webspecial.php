<?php
//百度自动推送功能
function smarty_function_webspecial($paramer,&$smarty){
		 global $config;
		//加载首页主题
		include(PLUS_PATH."/indextpl.cache.php");
		if($_GET['tpltype']){//预览主题
			$tplindex=$indextpl[$_GET['tpltype']];
		}else{
			$time=time();
			foreach($indextpl as $key=>$v){
				if($v['status']==1 && $v['stime']<$time && $v['etime']>$time){
					$tplindex=$v;
					break;
				}
			}
		}
		
		if($tplindex['pic']&&file_exists(APP_PATH.$tplindex['pic'])){
			$tplindex['pic']=$config['sy_weburl'].'/'.$tplindex['pic'];
			$content='';
			if($tplindex['pic']){
				if($tplindex['height']>0){//定义头部高度
					$height='$(".hp_head").css("margin-top","50px");';
				}
				
				$content.='<script>
						window.onload = function() {
							$("body").css("background","url('.$tplindex['pic'].') no-repeat center 38px");
							$(".hp_head").css("background","none");
							'.$height.'
						}; 
					 </script>';					
			}
			if($tplindex['se']==1){
				$content.='<script src="'.$config['sy_weburl'].'/js/grayscale.js" language="javascript"></script>
					 <script>grayscale(document.body);</script>';
			}
			return $content;
		}
	}
?>