<?php
/* *
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2018 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class pay_controller extends company
{
	
	function index_action()
	{
		$this->public_action();
		$statis=$this->company_satic();
		if($_GET['type']=='vip'){
			$rows=$this->obj->DB_select_all("company_rating","`service_price`<>'' and `display`='1' and `category`=1 order by sort desc","name,service_price,id");
			$this->yunset("rows",$rows);
		}
		$this->yunset("statis",$statis);
		$nopayorder=$this->obj->DB_select_num("company_order","`uid`=".$this->uid." and `order_state`=1");
		$this->yunset('nopayorder',$nopayorder);
		$this->yunset($this->MODEL('cache')->GetCache(array('integralclass')));
		$this->yunset("js_def",4);
		$this->com_tpl('pay');
	}
	function dingdan_action(){
 		if($_POST['price'] || $_POST['money_int'] || $_POST['comvip'] || $_POST['comservice']){
			$company=$this->obj->DB_select_once("company","`uid`='".$this->uid."'","`name`,`hy`");
			
			if($company['name']==''||$company['hy']==''){
				$this->ACT_layer_msg("请先完善基本资料！",8,$this->config['sy_weburl']."/member/index.php?c=info");
			}
			
			$statis = $this->obj->DB_select_once("company_statis","`uid`='".$this->uid."'","`integral`");
			
 			if($_POST['comvip']){
				
				$comvip=(int)$_POST['comvip'];
				
				$ratinginfo =  $this->obj->DB_select_once("company_rating","`id`='".$comvip."'");
				
				if($ratinginfo['time_start']<time() && $ratinginfo['time_end']>time()){
					$price = $ratinginfo['yh_price'];
				}else{
					$price = $ratinginfo['service_price'];
				}
				$dkjf = (int)$_POST['integral'];
				if($dkjf >= (int)$statis['integral']){
					$dkjf = $statis['integral'];
				}
				if($dkjf){
					$dkprice = $dkjf / $this->config['integral_proportion'];	
					$price = $price - $dkprice;			
				}
  				$data['type']='1';
 				
			}elseif($_POST['comservice']){
  				$id=(int)$_POST['comservice'];
				$dkjf=(int)$_POST['dkjf'];
				if($dkjf >= (int)$statis['integral']){
					$dkjf = (int)$statis['integral'];
				}
				$price = $_POST['dkprice'];
				$data['type']='5';
			}elseif($_POST['price_int']){
				$integral=intval($_POST['price_int']);
				if($this->config['integral_min_recharge']&&$integral<$this->config['integral_min_recharge']){
					$integral=$this->config['integral_min_recharge'];
				}
				$integralid=intval($_POST['integralid']);
				$CacheMclass=$this->MODEL('cache')->GetCache(array('integralclass'));
				$discount=$CacheMclass['integralclass_discount'][$integralid]/100;
				if($integralid&&$discount>0){
					$price =  $integral/$this->config['integral_proportion']*$discount;
				}else{
					$price = $integral/$this->config['integral_proportion'];
				}
				$price=floor($price*100)/100;
				$data['type']='2';
			}elseif($_POST['money_int']){
				$pay=intval($_POST['money_int']);
				if($this->config['money_min_recharge'] && $pay <$this->config['money_min_recharge']){
					$pay=$this->config['money_min_recharge'];
				}
				$price = $pay;
				$data['type']='4';
			}else {
				$this->ACT_layer_msg("参数不正确，请正确填写！",8,$_SERVER['HTTP_REFERER']);
			}
			if($data['type']=='2'&&$integral<1){
				$this->ACT_layer_msg("请正确填写购买数量！",8,$_SERVER['HTTP_REFERER']);
			}
			$dingdan=mktime().rand(10000,99999);
			$data['order_id']=$dingdan;
			
			if($dkjf){
				$data['order_dkjf']=$dkjf;
			}
			
			$data['order_price']=$price;
			
			$data['order_time']=mktime();
			$data['order_state']="1";
			$data['order_remark']=trim($_POST['remark']);
			$data['uid']=$this->uid;
			$data['did']=$this->userdid;
			$data['rating']=$_POST['comvip']?$_POST['comvip']:$_POST['comservice'];
			$data['integral']=$integral;

			if((int)$price==0 && $_POST['comvip']){ 
 
				$ratingM = $this->MODEL('rating');
 				$value=$ratingM->rating_info($_POST['comvip']);
				 
				$status=$this->obj->DB_update_all('company_statis',$value,"`uid`= '".$this->uid."' ");
				$this->obj->DB_update_all("company_job","`rating`='".$_POST['comvip']."'","`uid`='".$this->uid."'");
				if($status){
					$this->ACT_layer_msg("会员服务购买成功！",9,$this->config['sy_weburl']."/member/index.php?c=paylogtc");
				}else{
					$this->ACT_layer_msg("购买失败，请稍后重试！",8,$_SERVER['HTTP_REFERER']);
				}	
				
			}else{
				
				$id=$this->obj->insert_into("company_order",$data);
				if($id){
					if($_POST['comvip']){
						$this->MODEL('integral')->company_invtal($this->uid,$dkjf,$auto,"购买会员",true,2,'integral',11);
					}elseif($_POST['comservice']){
						$this->MODEL('integral')->company_invtal($this->uid,$dkjf,$auto,"购买增值包",true,2,'integral',11);
					}
					$this->obj->member_log("下单成功,订单ID".$dingdan);
					$this->ACT_layer_msg("下单成功，请付款！",9,$this->config['sy_weburl']."/member/index.php?c=payment&id=".$id);
				}else{
					$this->ACT_layer_msg("提交失败，请重新提交订单！",8,$_SERVER['HTTP_REFERER']);
				}

			}
		}else{

			$this->ACT_layer_msg("提交失败，请正确提交订单！",8,$_SERVER['HTTP_REFERER']);
		}
	}

	function dkzf_action(){
				
		$rows=$this->obj->DB_select_once("company","`uid`='".$this->uid."'");
		
		if(!$rows['name'] || !$rows['address'] || !$rows['pr']){
			echo json_encode(array('error'=>1,'msg'=>'请先完善企业资料！','url'=>$this->config['sy_weburl']."/member/index.php?c=info"));
		}

		if($_POST){
   			$M=$this->MODEL('jfdk');
			if($_POST['tcid']){
				$return = $M->buyPackOrder($_POST);
			}elseif($_POST['id']){
				$return = $M->buyVip($_POST);				
			}
			if($return['status']==1){
				
				echo json_encode(array('error'=>0,'msg'=>$return['msg']));
			}else{
				
				echo json_encode(array('error'=>1,'msg'=>$return['error'],'url'=>$return['url']));
			}
		}else{
			echo json_encode(array('error'=>1,'msg'=>'参数错误，请重试！'));
		}
	}
 
}
?>