//积分兑换表单
function checkform_redeem_show(){
	var num=$("#num").val();
	var stock=$("#stock").val();
	var uid=$("#uid").val();
	var restriction=$("#restriction").val();
	var memberintegral=$("#memberintegral").val();
	var redeemintegral=$("#redeemintegral").val();
	if(!uid){
		window.location.href=wapurl+'?c=login';
		//layermsg('您还没有登录，请先登录！');
		return false;
	}else if(num==0){
		layermsg('请正确填写兑换数量！');
		return false;
	}else if(Number(num)>Number(restriction) && restriction!="0"){
		layermsg('超出限购数量,请正确填写！');
		return false;
	}else if(Number(num)>Number(stock)){
		layermsg('超出库存数量,请正确填写！');
		return false;
	}else if(Number(num)*redeemintegral>memberintegral){
		layermsg('您的'+integral_pricename+'不足！',2,function(){
			window.location.href=wapurl+'member/index.php?c=pay';
		});
		return false;
	}
}
//商品分类、排序
$(document).ready(function(){
	$('.nav_ft').hover(function(){ 
		$(this).find('.nav_ft_list').show(); 
	},function(){ 
		$(this).find('.nav_ft_list').hide(); 
	});
	$('.nav_rt').hover(function(){ 
		$(this).find('.nav_rt_list').show(); 
	},function(){ 
		$(this).find('.nav_rt_list').hide(); 
	});
})
function redeem_dh(){
	var id=$("#id").val();
	var num=$("#num").val();
	var linkman=$("#linkman").val();
	var linktel=$("#linktel").val();
	var dhbody=$("#dhbody").html();
	
	var body='';
	
	if(dhbody!=''){
		body="收货地址："+dhbody;
	}
	var other = $("#other").val();
	if(other!=''){
		body = body+" 用户留言："+other;
	}
	if(!linkman||!linktel||!dhbody){
		layermsg('请填写收货人信息！');;
	}else{
		mui.prompt('请输入账号登录密码', '账号登录密码', '' ['取消', '确定'], function(e) {
			if (e.index == 1) {
				$.post(wapurl+"/index.php?c=redeem&a=savedh",{linkman:linkman,linktel:linktel,id:id,num:num,body:body,password:e.value},function(data){
					var data=eval('('+data+')');
					if(data.type==9){
						layermsg(data.msg,2,function(){window.location.href=data.url});
					}else{
						layermsg(data.msg);
					}
					//$("#passshow").html(passshow);
				});
				return true;
			} else {
				//$("#passshow").html(passshow);
				return false;
			}
		})
		
	}
}