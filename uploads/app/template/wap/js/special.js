/*
*公用报名方法
*/ 
 
function comapply(id,integral,url){
	if(integral>0&&integral){
		layer.open({
			content: "参加专题招聘，将扣除您"+integral+pricename+"，审核不通过将退还，是否继续？",
			btn: ['继续', '取消'],
			shadeClose: false,
			yes: function(){
				layer.closeAll();
				layer_load('执行中，请稍候...');
				$.post(url,{id:id},function(data){
					layer.closeAll();
					var data=eval('('+data+')');
					if(data.url=='1'){ 
						layermsg(data.msg,Number(data.tm),function(){location.reload();});return false;
					}else{
						layermsg(data.msg,Number(data.tm),function(){location.href=data.url;});return false;
					}
				});
			} 
		});
	}else{
		layer_load('执行中，请稍候...');
		$.post(url,{id:id},function(data){
			layer.closeAll();
			var data=eval('('+data+')');
			if(data.url=='1'){
				layermsg(data.msg, Number(data.tm),function(){location.reload();});return false;
			}else{
				layermsg(data.msg, Number(data.tm),function(){location.href=data.url;});return false;
			}
		});
	}
}