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
class integral_model extends model{	

	
	function company_invtal($uid, $integral, $auto=true, $name="", $pay=true, $pay_state=2, $type="integral", $pay_type=''){

		if( $pay && $integral!='0'){

			$integral = abs(intval($integral));

			$member=$this->DB_select_once("member","`uid`='".$uid."'","usertype,did");
			
			if($member['usertype']=="1"){
			
				$table="member_statis";

			}elseif($member['usertype']=="2"){

				$table="company_statis";

			}

			if($auto){

				$nid=$this->DB_update_all($table,"`$type`=`$type`+'".$integral."'","`uid`='".$uid."'");

			}else{

				$nid=$this->DB_update_all($table,"`$type`=`$type`-'".$integral."'","`uid`='".$uid."'");

				$integral="-".$integral;

			}

			$dingdan=mktime().rand(10000,99999);

			$data['order_id']=$dingdan;
			$data['did']=$member['did'];
			$data['com_id']=$uid;
			$data['pay_remark']=$name;
			$data['pay_state']=$pay_state;
			$data['pay_time']=time();
			$data['order_price']=$integral;
			$data['pay_type']=$pay_type;
			
			if($type=="integral"){
			
				$data['type']=1;

			}else{

				$data['type']=2;

			}

			$this->insert_into("company_pay",$data);

			return $nid;

		}else{

			return true;

		}
	}

	function get_integral_action($uid,$type,$msg){

		if($this->config[$type.'_type']=="1"){

			$auto=true;

		}else{

			$auto=false;

		}

		$this->company_invtal($uid,$this->config[$type],$auto,$msg,true,2,'integral');

	}

 	function save_integral($uid,$integral,$remark,$type="1"){

	    if($type=="1"){

	        $auto=true;

	    }else{

	        $auto=false;

	    }

	    $this->company_invtal($uid,$integral,$auto,$remark,true,2,"integral");
	}

	function insert_company_pay($integral,$pay_state,$uid,$msg,$type,$pay_type='',$ptype=false){

		if($integral!='0'){

			if($ptype){

				$pay['order_price']=$integral;

			}else{

				$pay['order_price']='-'.$integral;

			}

			$pay['order_id']=time().rand(10000,99999);
			$pay['pay_time']=time();
			$pay['pay_state']=$pay_state;
			$pay['com_id']=$uid;
			$pay['pay_remark']=$msg;
			$pay['type']=$type;
			$pay['pay_type']=$pay_type;
			$pay['did']=$this->userdid;

			return $this->insert_into("company_pay",$pay);

		}else{

			return false;

		}

	}

}
?>