function wxorderstatus(orderid,url) { 
	$.post('index.php?c=payment&act=wxpaystatus',{orderid:orderid},function(data){
		if(data==1){
			
			window.location.href='';
		}
	});
}

function payforms(){
	var pay_type=$("#pay_type").val();

	if(pay_type==''){
		layer.msg('请选择支付方式！', 2,8);

	}else if(pay_type == 'wxpay'){ 

		var orderId = $("#orderid").val();
		
		$.post('index.php?c=payment&act=wxurl',{orderId:orderId},function(data){
			
			$('.wx_payment_ewm').html(data);
			$.layer({
				type : 1,
				title :'微信扫码支付', 
				closeBtn : [0 , true],
				border : [10 , 0.3 , '#000', true],
				area : ['290px','380px'],
				page : {dom :"#wxpayTx"}
			});
			setInterval("wxorderstatus("+orderId+")", 3000); 
		})
		return false;  
	} else{
	
		$.layer({
			type : 1,
			title :'提示',
			closeBtn : [0 , true],
			border : [10 , 0.3 , '#000', true],
			area : ['450px','280px'],
			page : {dom :"#payshow"}
		});
	}
}

function getOrder(pay_type){
	
	$('#pay_type').val(pay_type);

	var jobid = $('#jobid').val();
 
	var price = parseFloat($('#price').html());

	var rewardid = $('#rewardid').val();
	
	if(price>0){

		
		var index = layer.load('执行中，请稍候...',0);
		$.ajax({
			async: false, 
			type: "POST",
			global: false,
			url :"index.php?c=jobpack&act=rewardpay",
			data:{jobid:jobid,rewardid:rewardid},
			success :function(data){
				var data=eval('('+data+')');
				if(data.error==1){

					layer.msg(data.msg, 2,8);

				}else if(data.error==0){

					$('#order_id').val(data.orderid);
					$('#orderid').val(data.id);
					//提交表单
					layer.close(index);
					$('#payform').submit();
				}
			}
		});
		
		
		
	}else{
		layer.msg('支付金额不合理，请重试！', 2,8);
	}
}

function rewardpay(jobid,rewardid,sqmoney,invitemoney,offermoney,money){

	$('#jobid').val(jobid);
	$('#rewardid').val(rewardid);
	$('.sqmoney').html(sqmoney+'元');
	$('.invitemoney').html(invitemoney+'元');
	$('.offermoney').html(offermoney+'元');
	$('#price').html(money);
	$.layer({
			type : 1,
			fix: false,
			zIndex:666,
			title : '赏金支付', 
			border : [0 , 0.3 , '#000', true],
			area : ['650px','400px'],
			page : {dom : '#rewardpay'},
			close: function(){
				
			}
	});
}
//状态操作
function getStatus(rewardid,status){
	if(status=='2'){//邀请面试
		
		$('#rewardid').val(rewardid);
		$.layer({
			type : 1,
			fix: false,
			zIndex:666,
			title : '邀请面试', 
			border : [0 , 0.3 , '#000', true],
			area : ['400px','460px'],
			page : {dom : '#job_box'},
			close: function(){
				
			}
		});
		
	}else if(status=='26'){//申请仲裁
		$('#rewardid').val(rewardid);
		$.layer({
			type : 1,
			fix: false,
			zIndex:666,
			title : '申请仲裁', 
			border : [0 , 0.3 , '#000', true],
			area : ['400px','300px'],
			page : {dom : '#job_box'},
			close: function(){
				
			}
		});
	}else{
		
		$.post('index.php?c=jobpack&act=logstatus',{rewardid:rewardid,status:status},function(data){
			
			var data=eval('('+data+')');
			if(data.error=='ok'){
				
				layer.msg('操作成功', 2, 9,function(){window.location.reload();window.event.returnValue = false;return false;});
			}else{
				layer.msg(data.error, 3, 8);
			}
			
			
		
		});
	
	}
}
function invite(){
		
		var rewardid = $('#rewardid').val();
		var linkman = $('#linkman').val();
		var linktel = $('#linktel').val();
		var intertime = $('#intertime').val();
		var address = $('#address').val();
		var content = $('#content').val();
		if(linkman==''){
			layer.msg('请填写联系人！', 2,8);return false;
		}
		if(linktel==''){
			layer.msg('请填写联系人电话！', 2,8);return false;
		}
		if(intertime==''){
			layer.msg('请选择面试日期！', 2,8);return false;
		}
		if(address==''){
			layer.msg('请填写面试地址！', 2,8);return false;
		}
		$.post('index.php?c=jobpack&act=logstatus',{rewardid:rewardid,status:2,linkman:linkman,linktel:linktel,intertime:intertime,address:address,content:content},function(data){
			
			var data=eval('('+data+')');
			if(data.error=='ok'){
				
				layer.msg('操作成功', 2, 9,function(){window.location.reload();window.event.returnValue = false;return false;});
			}else{
				layer.msg(data.error, 2, 8);
			}
		});
		
	}

	function withdraw_form(){
		var price=$("#price").val();
		var real_name=$("#real_name").val();
		if(!real_name){
			layer.msg('请填写真实姓名！', 2, 8);return false;  
		}
		if(Number(price)<1){
			layer.msg('请正确填写提现金额！', 2, 8);return false;  
		}
		
	}