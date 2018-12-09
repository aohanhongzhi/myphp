function check_email(strEmail) {
	 var emailReg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	 if (emailReg.test(strEmail))
	 return true;
	 else
	 return false;
 }
function setsite(id,name){
	$.post(wapurl+"/index.php?c=site&a=domain",{id:id,name:name},function(data){
		window.location.href=wapurl;
	});
}
function showMoreNav(){
	$(".subnav").toggle();
}
function show(obj){
	var obj=document.getElementById(obj);  
	if(obj.style.display=="block"){
		obj.style.display="none";
	}else{
		obj.style.display="block";
	}
}
function showImgDelay(imgObj,imgSrc,maxErrorNum){  
    if(maxErrorNum>0){ 
        imgObj.onerror=function(){
            showImgDelay(imgObj,imgSrc,maxErrorNum-1);
        };
        setTimeout(function(){
            imgObj.src=imgSrc;
        },500);
		maxErrorNum=parseInt(maxErrorNum)-parseInt(1);
    }
}
function layer_load(msg){
	 layer.open({
		 type: 2,
		 content: msg
	 });
	// layer.load();
};
function layer_del(msg,url){ 
	if(msg==''){ 
		layer_load('执行中，请稍候...');
		$.get(url,function(data){
			layer.closeAll();
			var data=eval('('+data+')');
			if(data.url=='1'){ 
				layermsg(data.msg,Number(data.tm),function(){location.reload();});return false;
			}else{
				layermsg(data.msg,Number(data.tm),function(){location.href=data.url;});return false;
			}
		});
	}else{
		layer.open({
			content: msg,
			btn: ['确认', '取消'],
			shadeClose: false,
			yes: function(){
				layer.closeAll();
				layer_load('执行中，请稍候...');
				$.get(url,function(data){
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
	}
}


function checkshowjob(type) {
    window.show_scrolltop = document.body.scrollTop;
    document.body.scrollTop = 0;
	if(type=='once'||type=='tiny'){
		layer.open({
			type:1,
			content: $("#"+type+"list").html(),
			shadeClose: false
		});return;
	}else{
		$("#"+type+"list").show();
		checkhide('info'); 
	}
}
function checkhide(id){ 
	$("#"+id+"button").show();
	$("#"+id).hide();
}
function checkjob1(id,type){
	var style=$("#"+type+"list"+id).attr("style");
	$(".yun_category_list li").removeClass("yun_category_on");	
	$(".qc"+id).addClass('yun_category_on');
	$(".yun_category_right_list").attr("style","display: none;");
	$(".lookhide").attr("style","display: none;");
	if(style=="display: none;"){
		$("#"+type+"list"+id).show();
		$("#"+type+id).removeClass("yun_category_on");
	}
}
function checkjob2(id,type){
	if($("#citylevel").length>0){
		if(parseInt($("#citylevel").val())==2){
			$("#cityclassbutton").val($(event.target).html());
			$("#cityclassbutton").html($(event.target).html());
			$("#three_cityid").val(id);
			$("#cityid").val(id);
			Close('city');
			return;
		}
	}
	var style=$("#"+type+"post"+id).attr("style");
	$(".post_show_three").attr("style","display: none;");
	if(style=="display: none;"){
		$("#"+type+"post"+id).show();
	}
} 
function checkedcity(id,name){
	$("#cityclassbutton").val(name);
	$("#cityclassbutton").html(name);
	$("#three_cityid").val(id);
	Close('city');
}
function checked_input(id){
	var one=$("input[name='jobclassone']:checked").val();
	var name=$("#r"+id).attr('name');
	if($("#r"+id).is(':checked')) {
		$(".one"+id).attr('checked',false); 
		$(".one"+id).attr('disabled','disabled');
	}else{
		$(".one"+id).attr("checked",false);
		$(".one"+id).attr('disabled',false);
	}
	var one_length=$("input[name='jobclassone']:checked").length;
	var check_length = $("input[name='jobclass']:checked").length;
	if((check_length+one_length)>5){
		$("#joblist").hide();	
		layermsg('您最多只能选择五个！',2,function(){
			$("#joblist").show();
				//if(name=='jobclassone'){
					$("#r"+id).attr("checked",false);
					$(".one"+id).attr("checked",false);
					$(".one"+id).attr('disabled',false);
				//}
			});
	}
	/*if((one_length)>5){
		$("#joblist").hide();	
		layermsg('搜索条件过多！',2,function(){
			$("#joblist").show();
				if(name=='jobclassone'){
					$("#r"+id).attr("checked",false);
					//$(".one"+id).attr("checked",false);
					$(".one"+id).attr('disabled',false);
				}
			}); 	
	}
	
	if(!one){
		
		if((check_length)>5){ 		
		$("#joblist").hide();	
			layermsg('您最多只能选择五个！',2,function(){
				$("#joblist").show();
				if(name=='jobclass'){
					$("#r"+id).attr("checked",false);
				}
				
			}); 	
		}
	}else{
		jobclass=$("#r"+id).attr("class");
		if(jobclass){
			$("#joblist").hide();	
			layermsg('搜索条件过多！',2,function(){
				$("#joblist").show();
				if(name=='jobclass'){
					$("#r"+id).attr("checked",false);
				}
			}); 
		}
	}*/
}
function realy() {
	var info="";
	var value=""; 
	$("input[name='jobclassone']:checked").each(function(){
		obj = $(this).val();
		name = $(this).attr("data");
		if(info==""){
			info=obj;
			value=name;
		}else{
			info=info+","+obj;
			value=value+","+name;
		}
	})
	//if(info.length<1){
		$("input[name='jobclass']:checked").each(function(){
			var obj = $(this).val();
			var name = $(this).attr("data");
			if(info==""){
				info=obj;
				value=name;
			}else{
				info=info+","+obj;
				value=value+","+name;
			}
		})
	//}
	
	if(info==""){
		$("#joblist").hide();	
		layermsg("请选择职位类别！",2,function(){
			$("#joblist").show();
		});return false;
	}else{
		var waptype=$("#waptype").val();
		if(waptype==1){
			var url=$("#searchurl").val();
			$.post(wapurl+"/?c=ajax&a=ajax_url",{url:url,type:"jobin",id:info},function(data){
				location.href=wapurl+data;
			})
		}else{
			$("#job_classid").val(info);
			$("#wapexpect").html(value);
			$("#jobclassbutton").val(value);
			Close("job");
		}
	}
}
function removes(){
	var waptype=$("#waptype").val();
	if(waptype==1){
		var url=$("#searchurl").val();
		$.post(wapurl+"/?c=ajax&a=ajax_url",{url:url,type:"jobin",id:''},function(data){
			location.href=wapurl+data;
		})
	}else{
		$("#jobclassbutton").val("请选择职位类别");
		$("#job_classid").val(""); 
		$(".onelist").attr("class","onelist lookshow");
		$(".onelist>.lookhide").hide();
		$(".post_show_three").hide();
		$("input[name='jobclass']").removeAttr("checked");
		$("input[name='jobclassone']").removeAttr("checked");
	}
}
function Close(type) {
    document.body.scrollTop = window.show_scrolltop;
	$("#"+type+"list>.onelist").attr("class","onelist lookshow");
	$("#"+type+"list>.onelist>.lookhide").hide();
	$("#"+type+"list>.post_show_three").hide();
	$("#"+type+"list").hide(); 
}
function checkfrom(target_form) {
	var username=$.trim($("#username").val());
	if(username==""){ 
		return mui.toast("用户名不能为空！");
	}else if(username.length<2||username.length>16){
		return mui.toast("用户名长度应在2-16位！");
	} 
	var email=$.trim($("#email").val()); 
    var myreg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/;
    if(!myreg.test(email)){
    	return mui.toast("邮箱格式错误！");
	} 
	var password=$.trim($("#password").val());
	var password2=$.trim($("#password2").val());
	if(password==""){
		return mui.toast("密码不能为空！");
	}else if(password.length<6||password.length>20){
		return mui.toast("密码长度应在6-20位！");
	}
	if(password!=password2){
		return mui.toast("两次密码不一致！");
	}
} 
function ckpwd(target_form) {
	var oldpassword=$.trim($("input[name='oldpassword']").val());
	var password1=$.trim($("input[name='password1']").val());
	var password2=$.trim($("input[name='password2']").val());
	if(oldpassword==''||password1==''||password2==''){
		return mui.toast("旧密码、新密码、确认密码均不能为空！");
	}
	if(oldpassword==password1){
		return mui.toast("旧密码和新密码一致，不需要修改！");
	}
	if(password1!=password2){
		return mui.toast("两次密码不一致！");
	}
	post2ajax(target_form);
	return false;
}
function isdel(url,msg){
	mui.confirm(msg ? msg : '是否删除该数据？', '提示', ['取消','确定'], function(e){
		if(e.index==1){
			location.href = url;
		}
	})
}
function islogout(url,msg) {
	mui.confirm(msg ? msg : '确认退出吗？', '提示', ['取消','确定'], function(e){
		if(e.index==1){
			location.href = url;
		}
	})
}
function comjob(id){
	if(id>0){ 
		$.post(wapurl+"/index.php?c=ajax&a=wap_job",{id:id,type:1},function(data){  
			$("select[name='job1_son']").html(data);
		})
	}
}
function comcity(id,name){
	if(id>0){
		$.post(wapurl+"/index.php?c=ajax&a=wap_city",{id:id,type:1},function(data){  
			$("select[name='"+name+"']").html(data); 
		})
	} 
	if(name=='cityid'){$("select[name='three_cityid']").html("<option value=\"\">--请选择--</option>");} 
}
function mlogin(target_form) {
	var act_login=$.trim($("#act_login").val()); 
	if(act_login == 1){
		var moblie=$.trim($("#usermoblie").val());
		var dynamiccode=$.trim($("#dynamiccode").val()); 
		if(moblie==''||dynamiccode==''){
			layermsg('手机号或验证码均不能为空！');return false; 
		}
	}else {
		var username=$.trim($("#username").val());
		var password=$.trim($("#password").val()); 
		if(username==''||password==''){
			layermsg('用户名或密码均不能为空！');return false; 
		}
		//验证码验证
		var authcode;
		var geetest_challenge;
		var geetest_validate;
		var geetest_seccode;
		var codesear=new RegExp('前台登录');
		if(codesear.test(code_web)){
			if(code_kind==1){
				authcode=$.trim($("#checkcode").val());  
				if(!authcode){
					layermsg('请填写验证码！');return false;
				}					
			}else if(code_kind==3){
				geetest_challenge = $('input[name="geetest_challenge"]').val();
				geetest_validate = $('input[name="geetest_validate"]').val();
				geetest_seccode = $('input[name="geetest_seccode"]').val();
				if(geetest_challenge =='' || geetest_validate=='' || geetest_seccode==''){
					$("#popup-submit").trigger("click");
					layermsg('请点击按钮进行验证！');return false;
				}
			}
		}
	}
	
	
	post2ajax(target_form);
	return false;
}  

function cktiny(target_form) {
	var name=$.trim($("input[name='username']").val()); 
	var job=$.trim($("input[name='job']").val());
	var mobile=$.trim($("input[name='mobile']").val());
	var production=$.trim($("#production").val());
	var password=$.trim($("input[name='password']").val());
	var id=$.trim($("input[name='id']").val()); 
	var sex=$.trim($("input[name='sex']").val()); 
	if(name==''){layermsg('姓名不能为空！');return false; }	
	if(sex==''){layermsg('请选择性别！');return false; }	
	if(mobile==''){
		layermsg('联系手机不能为空！');
		return false; 
	}else{
		var reg= /^[1][3456789]\d{9}$/;   
		if(!reg.test(mobile)){ 
			layermsg('联系手机格式错误！');
			return false;
		}
	}
	if(job==''){layermsg('请填写想要找的工作！');return false; }
	if(production==''){layermsg('自我介绍不能为空！');return false; }
	if (password == '') {
		if(id==''){
			layermsg('密码不能为空！'); return false;
		}else{			
			layermsg('请输入添加时的密码！'); return false;
		}
	}
	var authcode;
	var geetest_challenge;
	var geetest_validate;
	var geetest_seccode;
	var codesear=new RegExp('店铺招聘');
	
	if(codesear.test(code_web)){
	
		if(code_kind==1){
			authcode=$.trim($("#checkcode").val());  
			if(!authcode){
				layermsg('请填写验证码！');return false;
			}
		}else if(code_kind==3){

			geetest_challenge = $('input[name="geetest_challenge"]').val();
			geetest_validate = $('input[name="geetest_validate"]').val();
			geetest_seccode = $('input[name="geetest_seccode"]').val();
			
			if(geetest_challenge =='' || geetest_validate=='' || geetest_seccode==''){
				$("#popup-submit").trigger("click");
				layermsg('请点击按钮进行验证！');return false;
			}
		}
	}
	post2ajax(target_form);
	return false;
}

function islayer(){
	if($.trim($("#layermsg").val())){
		var msg=$.trim($("#layermsg").val());
		var url=$.trim($("#layerurl").val());
        if(msg){
		    if(url){
			    layermsg(msg,2,function(){location.href=url;});
		    }else{
			    layermsg(msg);
		    } 
	    }
	} 
}
function layermsg(content,time,end){ 
	layer.open({
		content: content, 
		time: time === undefined ? 2 : time,
		end: end
	});
	return false;
}
function layeralert(title,content,time,end){ 
	layer.open({
		title: [title,'background-color:#0099CC; color:#fff;'],
		content: content, 
		time: time === undefined ? 2 : time,
		end:end===undefined?'':function(){location.href = end;}
	});
}
function really(name){
	var chk_value =[];    
	$('input[name="'+name+'"]:checked').each(function(){    
		chk_value.push($(this).val());   
	});   
	if(chk_value.length==0){
		layermsg("请选择要删除的数据！",2);return false;
	}else{
		layer.open({
			content: '确定删除吗？',
			btn: ['确认', '取消'],
			shadeClose: false,
			yes: function(){
				setTimeout(function(){$('#myform').submit()},0); 
			} 
		});
	} 
}
//全选
function m_checkAll(form){
	for (var i=0;i<form.elements.length;i++){
		var e = form.elements[i];
		if (e.Name != 'checkAll'&&e.disabled==false)
		e.checked = form.checkAll.checked; 
	}
} 

function getDaysHtml(year,month){
	var days=30;
	if((month==1)||(month==3)||(month==4)||(month==7)||(month==8)||(month==10)||(month==12)){
		days=31;
	}else if((month==4)||(month==6)||(month==9)||(month==11)){
		days=30;
	}else{
		if((year%4)==0){
			days=29;
		}else{
			days=28;
		}
	}
	var daysHtml='';
	for(var i=1;i<=days;i++){
		daysHtml+="<option value='"+i+"'>"+i+"</option>";
	}
	return daysHtml;
}
function selectMonth(yearid,monthid,dayid){
	$("#"+dayid).html(getDaysHtml(parseInt($("#"+yearid).val()),parseInt($("#"+monthid).val())));
}
function setSelectDay(dayid,day){
	$("#"+dayid).val(day);
}
function isjsMobile(obj){
	var reg= /^[1][3456789]\d{9}$/;   
    if (obj.length != 11) return false;
    else if (!reg.test(obj)) return false;
    else if (isNaN(obj)) return false;
    else return true;
}
function isjsTell(str) {
    var result = str.match(/\d{3}-\d{8}|\d{4}-\d{7}/);
    if (result == null) return false;
    return true;
}
$(document).ready(function () {
    $(document).delegate('.tiny_show_tckbox_h1_icon', 'click', function () {
        layer.closeAll();
    });
	$("#price_int").blur(function(){
		var value=parseInt($(this).val());
		var min_recharge=$(this).attr("min");
		if(min_recharge>0&&value<min_recharge){
			value=min_recharge;
			$("#price_int").val(min_recharge);
			$("#com_vip_price").val(value/proportion);
			$("#span_com_vip_price").html(value/proportion);
			$("#bank_price").val(value/proportion);
		}
		var proportion=$(this).attr("int");
		$("#com_vip_price").val(value/proportion);
		$("#span_com_vip_price").html(value/proportion);
		$("#bank_price").val(value/proportion);
	});
    $(".repeat_list").click(function(){
        
		if($(this).attr("eid")){
			var eid = $(this).attr("eid");
			$("#eid").val($(this).attr("eid"));
		}
        if($(this).attr("uid")){
			var uid = $(this).attr("uid");
			$("#uid").val($(this).attr("uid"));
		}
        if($(this).attr("r_name")){
			var r_name = $(this).attr("r_name");
			$("#r_name").val($(this).attr("r_name"));
		}
        $.post(wapurl+"/index.php?c=ajax&a=indexajaxreport",{eid:eid},function(data){
            if(data==1){
                layermsg('您已经举报过简历！', 2, 8);return false;  
            }else if(data==2){
                var msg='你确定举报这份简历，是否继续？';
				layer.open({
					content: msg,
					btn: ['继续', '取消'],
					shadeClose: false,
					yes: function () {
						location.href = "index.php?c=reportlist&uid="+uid+"&eid="+eid+"&r_name="+r_name;
					}
				});
            }
        });
	});
	$(".sq_resume").click(function(){
		if($(this).attr("uid")){
			var uid = $(this).attr("uid");
			$("#uid").val($(this).attr("uid"));
		}
		if($(this).attr("jobid")){
			var jobid = $(this).attr("jobid");
		}
		if($(this).attr("jobtype")){
			var jobtype = $(this).attr("jobtype");
		}
 		layer_load('执行中，请稍候...');
		$.post(wapurl+"/index.php?c=ajax&a=indexajaxresume",{show_job:1,jobid:jobid,jobtype:jobtype},function(data){
			layer.closeAll();
   			var data=eval('('+data+')');
			var status=data.status;
			var type=data.type;
			var pro=data.pro;
			var integral=data.integral;
			if(status == '7'){
				pleaselogin('您还未登录企业账号，是否登录？','index.php?c=login')
			}else if(status == '6'){
				notuser(data.typename,data.username,'企业账户',data.typeurl,'');
			}else if(status == '5'){
				layermsg('未审核企业用户不能邀请面试！请联系客户加快审核进度', 2, 8);return false;
			}else if(status == '4'){
				layermsg('您还没有正在招聘的职位，请先发布职位吧！');return false;
			}else if(status == '3'){
 				var msg='会员已到期，您可以先购买特权，是否继续？';
				layer.open({
					content: msg,
					btn: ['继续', '取消'],
					shadeClose: false,
					yes: function () {
						window.location.href=wapurl+"member/index.php?c=rating";
					}
				});
			}else if(status == '2'){
				if(type!=4){
					if(type==3){
						var msg="套餐已用完，继续操作将会扣除"+integral*pro+"积分，是否继续？";
					}else{
						var msg="套餐已用完，继续操作将会扣除"+integral+"元，是否继续？";
					}
	 				layer.open({
						content: msg,
						btn: ['继续', '取消'],
						shadeClose: false,
						yes: function () {
							window.location.href=wapurl+"member/index.php?c=getserver&id="+uid+"&server="+11;
						}
					});
				}else{
					var msg='您的套餐已用完，您可以先购买特权，是否购买特权？';
					layer.open({
						content: msg,
						btn: ['购买', '取消'],
						shadeClose: false,
						yes: function () {
							window.location.href=wapurl+"member/index.php?c=rating";
						}
					});
				}
				
			}else if(status=='1'){
				if(jobid){
					location.href=wapurl+'index.php?c=resume&a=invite&uid='+uid+'&jobid='+jobid;
				}else{
					location.href=wapurl+'index.php?c=resume&a=invite&uid='+uid;
				}
			}
		});
	});
	
	$("#click_invite").click(function(){
		var uid=$("#uid").val();
		var content=$("#content").val();
		var username=$("#username").val();
		var job=$("#jobname").val();
 		var intertime=$("#intertime").val();
		var linkman=$("#linkman").val();
		var linktel=$("#linktel").val();
		var address=$("#address").val();
		
		job=job.split("##");
		var jobname=job[0];
		var jobid=job[1];
		
		if($("#update_yq").attr("checked")=='checked'){
			var update_yq=1;
		}else{
			var update_yq=0;
		}
		
		if($.trim(jobname)==""|| $.trim(jobid)==""){
			layermsg('请选择面试职位！', 2, 8);return false;
		}
		
		if($.trim(intertime)==""){
			layermsg('面试时间不能为空！', 2, 8);return false;
		}
		
		if($.trim(linktel)== ''){
			layermsg('联系电话不能为空！', 2); return false;
		}else if(isjsTell(linktel)==false&&isjsMobile(linktel)==false){
		    layermsg('联系电话格式错误！', 2); return false;
		}
		
		
		layer_load('执行中，请稍候...');
		$.post(wapurl+"/index.php?c=ajax&a=sava_ajaxresume",
			{uid:uid,content:content,username:username,jobname:jobname,
				update_yq:update_yq,address:address,linkman:linkman,
				linktel:linktel,intertime:intertime,jobid:jobid},
			function(data){
			layer.closeAll();
			var data=eval('('+data+')');
			var status=data.status;
			var integral=data.integral;
			if(status==8){
				layermsg(data.msg);return false;
			}else if(status==9){
				layermsg('该用户暂不接受面试邀请！');return false;
			}else if(status==4){
				layermsg(data.msg);return false;
			}else if(!status || status==0){ 
				layermsg('请先登录！',2);
			}else if(status==6){
				layermsg('您的套餐已用完，请先购买会员！',2,function(){
					location.href=wapurl+"member/index.php?c=rating";
				});
			}else if(status==5){
				layermsg('您还有'+integral+integral_pricename+'，无法邀请面试！',2,function(){history.back();}); 
			}else if(status==3){
				layermsg('您已成功邀请！',2,function(){
					location.href=wapurl+"member/index.php?c=invite";
				});
			}
		});
	});
});
function checkOncePassword(id){
	if($(".layermmain #once_password").val()==''){
		layermsg('请输入密码');
		return;
	}
	var operation_type=$("#operation_type").val();
	$.post(wapurl + "/index.php?c=ajax&a=checkOncePassword", { id: id, password: $(".layermmain #once_password").val(), operation_type: operation_type }, function (data) {
	    if (data == '1') {						
	        var url = '',msg='';
	        if (operation_type == 'refresh') {
	            url = wapurl + 'index.php?c=once&a=show&id=' + id;
	            msg = '刷新成功！';
	        } else if (operation_type == 'edit') {
	            url = wapurl + 'index.php?c=once&a=add&id=' + id;
	            msg = '验证通过！';
	        } else if (operation_type == 'remove') {
	            url = wapurl + 'index.php?c=once';
	            msg = '删除成功！';
	        }
	        layermsg(msg, 2, function () { location.href = url; });									
		}else if (data == '3'){
			layermsg('对不起你已达到一天最多刷新次数！',2,function(){});	
		}else{
			layermsg('密码错误！',2,function(){});			
		}
	});
}
function checkTinyPassword(id){
	if($(".layermmain #tiny_password").val()==''){
		layermsg('请输入密码');
		return;
	}
	var operation_type = $("#operation_type").val();
	$.post(wapurl + "/index.php?c=ajax&a=checkTinyPassword", { id: id, password: $(".layermmain #tiny_password").val(), operation_type: operation_type }, function (data) {
	    if (data == '1') {
	        var url = '', msg = '';
	        if (operation_type == 'refresh') {
	            url = wapurl + 'index.php?c=tiny&a=show&id=' + id;
	            msg = '刷新成功！';
	        } else if (operation_type == 'edit') {
	            url = wapurl + 'index.php?c=tiny&a=add&id=' + id;
	            msg = '验证通过！';
	        } else if (operation_type == 'remove') {
	            url = wapurl + 'index.php?c=tiny';
	            msg = '删除成功！';
	        }
	        layermsg(msg, 2, function () { location.href = url; });
	    } else {
	        layermsg('密码错误！', 2);
	    }
	});
}
function form2json(target_form) {
    var json_form = '';
    $(target_form).find('input,select,textarea').each(function () {
        if($(this).attr('type')=='radio'){
			if ($(this).attr('name')&&$(this).attr('checked')=='checked') {
				json_form += ',' + $(this).attr('name') + ':"' + $(this).val().replace(/[\r\n]+/g, '\\n')+'"';
			}
		}else{
			if ($(this).attr('name')) {
				json_form += ',' + $(this).attr('name') + ':"' + $(this).val().replace(/[\r\n]+/g, '\\n')+'"';
			}
		}
    });
    return eval('({' + json_form.substring(1) + '})');
}
function formfile2json(target_form) {
    var json_form = '';
    var formData = new FormData(target_form);
    $(target_form).find('input,select').each(function () {
        if ($(this).attr('name')) {
            //alert($(this)[0].type);
            if ($(this)[0].type == 'file') {
                //alert('adsfad');
                formData.append('file', $('input[type=file]', target_form).get(0).files[0]);
            } else {
                formData.append($(this).attr('name'), $(this).val());
            }
        }
    });
    
    //alert(formData.length);
    //formData.append('file', $('input[type=file]', target_form).get(0).files[0]);
    //alert(formData);
    return formData;
}
function form2string(target_form) {
    var json_form = '';
    $(target_form).find('input,select').each(function () {
        if ($(this).attr('name')) {
            json_form += '&' + $(this).attr('name') + '=' + $(this).val();
        }
    });
    return json_form;
}
function post2ajax(target_form) {
	layer_load('执行中，请稍候...');
    if ($('input[type=file]', target_form).length > 0) {
        $.ajax({
            url: $(target_form).attr('action'),
            data: formfile2json(target_form),
            processData: false,
            type: 'POST',
						async: false,  
						cache: false,
						contentType: false,
            success: function (data) {
								layer.closeAll();
                var json_data = eval('(' + data + ')');
                if (json_data.msg) {
										if($("#popup-captcha-mobile").length>0){
											$("#popup-submit").trigger("click");
										}
										if (json_data.st==10) {
										    checkCode('vcode_img'); 
										}
		                layermsg(json_data.msg, json_data.tm, function () { if (json_data.url) { location.href = json_data.url; } });
                } else if (json_data.url) {
                    location.href = json_data.url;
                }
            }
        });
    } else {
        if ($(target_form).attr('action') == 'get') {
            $.get($(target_form).attr('action') + form2string(target_form), function (data) {
				layer.closeAll();
                var json_data = eval('(' + data + ')');
                if (json_data.msg) {
					if($("#popup-captcha-mobile").length>0){
						$("#popup-submit").trigger("click");
					}
                    layermsg(json_data.msg, json_data.tm, function () { if (json_data.url) { location.href = json_data.url; } });
                } else if (json_data.url) {
                    location.href = json_data.url;
                }
            });
        } else {		
            $.post($(target_form).attr('action'), form2json(target_form), function (data) {
								layer.closeAll();
                var json_data = eval('(' + data + ')');
                if (json_data.msg) {
									if($("#popup-captcha-mobile").length>0){
										$("#popup-submit").trigger("click");
									}
									if(json_data.msg.indexOf('script')){
										$('#uclogin').html(json_data.msg);
									}
			            layermsg(json_data.msg, json_data.tm, function () {
										if (json_data.url) {
											location.href = json_data.url; 
										}  
									});
									if (json_data.st==10) {
									    checkCode('vcode_img'); 
									}
                } else if (json_data.url) {
                    location.href = json_data.url;
                }
            });
        }
    }
    return false;
} 
//修改用户名
function Savenamepost(){
	var username = $.trim($("#username").val());
	var pass = $.trim($("#password").val());
	if(username.length<2 || username.length>16){
		layermsg("用户名长度应该为2-16位！",2);return false;
	}
	if(pass.length<6 || pass.length>20){
		layermsg("密码长度应该为6-20位！",2);return false;
	}
	 
	$.post(wapurl+"/member/index.php?c=setname",{username:username,password:pass},function(data){
		if(data==1){
			layermsg("修改成功，请重新登录！", 2,function(){
				location.href=wapurl+"index.php?c=login"}
			);
			return false;
		}else{
			layermsg(data,2);return false;
		}
	})
}

function _addjob(num,integral_job,online,pro,gourl,checkType){
	if(num==1 || (integral_job==0 && num!=0)){
		location.href=gourl;
	}else if(num==2){
		if(integral_job>=0){
			if(online!=4){
				if(online==3){
					var msg='套餐已用完，继续操作将会消费'+integral_job*pro+'积分，是否继续？';
				}else{
					var msg='套餐已用完，继续操作将会消费'+integral_job+'元，是否继续？';
				}
				layer.open({
					content: msg,
					btn: ['继续', '取消'],
					shadeClose: false,
					yes: function () {
						if(checkType == "addjob"){
							window.location.href="index.php?c=getserver&server="+8;
						}else if(checkType == "addpart"){
							window.location.href="index.php?c=getserver&server="+9;
						}else if(checkType == "addltjob"){
							window.location.href="index.php?c=getserver&server="+10;
						}else{
							window.location.href="index.php?c=getserver&server="+3;
						}
						
					}
				});
			}else{
				layermsg("套餐已用完，请先购买套餐", 2,function(){
					location.href="index.php?c=rating"
				});
				return false;
			}
		}else{
			location.href = gourl;
		} 
	}else{
		layermsg("会员已到期，请先购买特权", 2,function(){
			location.href="index.php?c=rating"
		});
		return false;
	}
}

function jobadd_url(num,integral_job,type,online,pro){
	var checkType = '';
	var gourl='';
	if(type=="part"){
		gourl='index.php?c=partadd';
		checkType = 'addpart';
	}else if(type=="job"){
		gourl='index.php?c=jobadd';
		checkType = 'addjob';
	}else if(type=="ltjob"){
		gourl='index.php?c=lt_jobadd';
		checkType = 'addltjob';
	}else if(type='lietou'){
		gourl='index.php?c=jobadd';
	}
	if(checkType == ''){
		_addjob(num,integral_job,online,pro, gourl);
	}else{
		$.post(wapurl + '/index.php?c=ajax&a=ajax_day_action_check',
			{type:checkType},
			function(data){
				data = eval('(' + data + ')');
				if(data.status == -1){
					layermsg(data.msg);
				}
				else if(data.status == 1){
 					_addjob(num,integral_job,online,pro, gourl,checkType);
				}
			}
		);
	}

}//end function jobadd_url()

function checkCode(id){
	document.getElementById(id).src=wapurl+"/authcode.inc.php?"+Math.random();
}
//问答关注功能
function attention(id,type,url){
	$.post(url,{id:id,type:type},function(data){
   		var data=eval('('+data+')');  
		if(type==1){var msg='关注';}else{var msg='+  关注';} 
		if(data.st==8){
			layermsg(data.msg, 2);return false;	
		}else{		
			$(".num"+id).html(data.url+"人关注");
			$(".index_num"+id).html(data.url);
			if(data.tm==1){				
				$(".q"+id+">a").attr("class","watch_qxgz");
				$(".q"+id+">a").html("取消关注");
				layermsg("关注成功！", 2,function(){location.reload();});return false; 
			}else{
				$(".q"+id+">a").attr("class","watch_gz");
				$(".q"+id+">a").html(msg);
				layermsg("取消成功！", 2,function(){location.reload();});return false; 
			}				
		} 
	});
}

function get_show(eid){
	$("#eid").val(eid); 
	layer.open({
		type:1,
		content: $("#TB_window").html(),
		shadeClose: false
	});return; 
} 

function get_comment(aid,show,url){ 
	$(".pl_menu").hide();
	var style=$(".review"+aid).css("display");
	var info=$(".review"+aid+" ul").html();
	if(style=="none"||show>0){ 
		if((info==''||info==null)||show>0){
			$.post(url,{aid:aid},function(data){
				var html='';  
				var datas = Array();
				data = data.replace(/\s+/g,"[[space]]");//eval的字符串中有空格会出错			
				datas = eval("("+data+")");
				$.each(datas,function(key,val){
					html+="<li>"+
							"<div class=\"menu_p1_tx\"><img src=\""+val.pic.replace(/\[\[space\]\]/g,' ')+"\" onerror=\"showImgDelay(this,'"+val.errorpic.replace(/\[\[space\]\]/g,' ')+"',2);\"/></div>"+
							"<div class=\"menu_right\">"+
								"<div class=\"menu_rig_h2\">"+
									"<span class=\"menu_user\"><a href=\""+val.url.replace(/\[\[space\]\]/g,' ')+"\">"+val.nickname.replace(/\[\[space\]\]/g,' ')+"</a>：</span>"+
									"<span class=\"menu_mes\">"+val.content.replace(/\[\[space\]\]/g,' ')+"</span>"+
								"</div>"+ 
								"<div class=\"menu_date\">"+
									"<span>"+val.date.replace(/\[\[space\]\]/g,' ')+"</span>"+
								"</div>"+
							"</div>"+ 
						"</div>"+
						"<div class=\"clear\"></div>"+
					"</li>"; 
					$(".review"+aid+" ul").html(html); 
				});	 
			});
		}
		$(".review"+aid).show();
	}else{
		$(".review"+aid).hide();
	} 
} 
function for_comment(aid,qid,url,comurl){
	var content=$.trim($("#comment_"+aid).val()); 
	if(content=="" || content=="undefined"){
		layermsg('评论内容不能为空！');return false; 
	}else{
		$.post(url,{aid:aid,qid:qid,content:content},function(msg){
			if(msg=='1'){
				$("#comment_"+aid).val("");
				var com_num=$("#com_num_"+aid).html();  
				com_num=parseInt(com_num)+parseInt(1);
				$("#com_num_"+aid).html(com_num); 
				get_comment(aid,'1',comurl);
			}else if(msg=='0'){
				layermsg('评论失败！');return false; 
			}else if(msg=='no_login'){ 
				layermsg('请先登录！');return false; 
			}else{
				layermsg(msg);return false; 
			}
		});
	}
} 
function support(aid,url){
	$.post(url,{aid:aid},function(msg){
		if(msg=='0'){
			layermsg('提交失败！');return false; 
		}else if(msg=='1'){
			var num=$("#support_num_"+aid).html(); 
			$("#support_num_"+aid).html(parseInt(num)+parseInt(1)); 
			layermsg('点赞成功！');return false; 
		}else if(msg=='2'){
			layermsg('请勿重复点赞！');return false; 
		}
	});
}  
function checkform(img){	
	var title=$.trim($("input[name='title']").val());
	var cid=$("#cid").val();
	var content=$.trim($("textarea[name='content']").val());
	if(title==''){
		layermsg('请填写标题！'); return false;
	}else if(cid==''){
		layermsg('请选择类别！'); return false;
	}else if(content==''){
		layermsg('请填写内容！'); return false;
	}
	var authcode;
	var geetest_challenge;
	var geetest_validate;
	var geetest_seccode;
	var codesear=new RegExp('职场提问');
	
	if(codesear.test(code_web)){
	
		if(code_kind==1){
			authcode=$.trim($("#ask_CheckCode").val());  
			if(!authcode){
				layermsg('请填写验证码！');return false;
			}	
		}else if(code_kind==3){

			geetest_challenge = $('input[name="geetest_challenge"]').val();
			geetest_validate = $('input[name="geetest_validate"]').val();
			geetest_seccode = $('input[name="geetest_seccode"]').val();
			
			if(geetest_challenge =='' || geetest_validate=='' || geetest_seccode==''){
				$("#popup-submit").trigger("click");
				layermsg('请点击按钮进行验证！');return false;
			}
		}
	}
	$.post(wapurl+"/index.php?c=ask&a=addquestions",{
			title:title,
			cid:cid,
			content:content,
			authcode:authcode,
			geetest_challenge:geetest_challenge,
			geetest_validate:geetest_validate,
			geetest_seccode:geetest_seccode
		},function(data){
		if(data=='0'){
			layermsg('验证码错误！',2,function(){checkCode(img)});return false; 
		}else if(data==1){
			layermsg('发布问题成功！',2,function(){window.location.href = 'index.php?c=ask'});return false; 
		}else if(data == 5){
			layermsg('已发布，正在审核中！',2,function(){window.location.href = 'index.php?c=ask'});return false; 
		}else if(data==2){
			layermsg('发布问题失败！');return false; 
		}else if(data==3){
			layermsg(pricename+'不足，无法发布！');return false; 
		}else if(data==4){
			$("#popup-submit").trigger("click");
			layermsg('请点击按钮进行验证！',2);
			
			return false; 
		}else{
			data = eval('(' + data + ')');
			if(data.maxNum){
				layermsg('每天最多发布' + data.maxNum + '个提问');return false; 
			}
		}
	});	
}
function getclass(id,name,url){
	$(".quiz_box_first li").removeClass('tw_current');
	$(".qc"+id).addClass('tw_current');
	$(this).parent().attr('class','tw_current');
 	$.post(url,{id:id},function(data){
 		var datas = Array();
		var html='';
		datas = eval("("+data+")"); 
		$.each(datas,function(key,val){
			html +="<li class=\"qc"+key+"\"><a href='javascript:void(0)' onclick=\"selectclass('"+key+"','"+val+"')\">"+val+"</a></li>"; 
		}); 
		//$(".quiz_box_second .quiz_box_title").html(name+"分类：");
		$(".quiz_box_second .quiz_select").html(html);
		$(".quiz_box_second").show();		
		$('.quiz_box_first').hide();
	
	});
}
function selectclass(id,name){
	$(".quiz_box_second li").removeClass('tw_current');
	$(".qc"+id).addClass('tw_current');
	$(".tw_bx_z>span").html(name);
	$(".tw_bx_list_down").hide();
	$("input[name='cid']").val(id); 
}
$(document).ready(function(){
	$("input[name='cid']").val('');
	/*
	$("input[name='keyword']").focus(function(){
		$(".seek_menu").hide();
	},function(){ 
		searchli();
		$(".seek_menu").show(); 
	});
	$("input[name='keyword']").keyup(function(){
		searchli();
	});
	*/
	$(".menu_p1_nrtj span").click(function(){
		var aid=$(this).attr('aid');
		$(".review"+aid).hide();
	});
	$('body').click(function(evt) {
		if(evt.target.name!='keyword'){
			$(".seek_menu").hide();
		}
		if($(evt.target).parents(".tw_bx_z").length==0) {
			$('.tw_bx_list_down').hide();
		}
	});
	$(".tw_bx_z span").click(function(){ 
		$(".quiz_box_first").show();
		$(".quiz_box_second").hide();
		$(".tw_bx_list_down").show();
	});
	
});
function attention_user(uid,type,url){
	$.post(url,{id:uid},function(msg){ 
		if(msg=='4'){
			layermsg('不能关注自己！');return false; 
		}else if(msg=='3'){
			
			layermsg('请先登录！');return false; 
		}else if(type=='remove'){
			$(".atn"+uid).remove();
		}else{   
			var fans=$(".fans"+uid).attr('fans');
			if(msg=='1'){ 
				fans=parseInt(fans)+parseInt(1); 
				$(".user"+uid+">a").attr("class","watch_qxgz");
				$(".user"+uid+">a").html("取消关注");
			}else if(msg=='2'){ 
				fans=parseInt(fans)-parseInt(1); 
				$(".user"+uid+">a").attr("class","watch_gz");
				$(".user"+uid+">a").html("+ 关注");
			}
			$(".fans"+uid).attr('fans',fans);
			$(".fans"+uid+">span").html(fans);
		}
	});
}
function searchli(){
	var keyword=$.trim($("input[name='keyword']").val());
	var html='';
	$(".seek_menu .option>a").attr("href",wapurl+"&keyword="+keyword);
	$(".seek_menu .option>a").html(keyword);
	if(keyword){ 
		$.post(searchurl,{keyword:keyword},function(data){
			if(data){
				var datas = Array();			
				datas = eval("("+data+")"); 
				$.each(datas,function(key,val){
					html +="<li><p><a href=\""+val.url+"\" target=\"_blank\">"+val.title+"</a></p><span>"+val.answer_num+"个回复</span></li>"; 
				});
			}
			$(".searchli").html(html); 
			
		});
	}else{
		$(".searchli").html(''); 
		$(".seek_menu>span").html(''); 
	}
}
function checkanswer(uid,img){
	var id=$("input[name='id']").val();
	var content=$.trim($("textarea[name='content']").val());
	var authcode=$("#authcode").val();
	if(uid==""){
		window.location.href=wapurl+"?c=login";return false;
		//layermsg('请先登录！');return false;
	}
	if($.trim($("textarea[name='content']").val())==""){
		layermsg('回答内容不能为空！'); return false;
	}
	if($.trim($("#authcode").val())==""){
		layermsg('验证码不能为空！'); return false;
	}
	$.post(wapurl+"/index.php?c=ask&a=answer",{id:id,content:content,authcode:authcode},function(data){
		
		if(data=='0'){
			layermsg('验证码错误！',2,function(){checkCode(img)});return false; 
		}else if(data==1){
			layermsg('发布回答成功！',2,function(){window.location.reload();});return false; 
		}else if(data==2){
			layermsg('发布回答失败！');return false; 
		}else if(data==4){
			layermsg('积分不足！');return false; 
		}
	});	
}

function rtop(){
	var id=$("input:radio[name=dis]:checked").val();
	var eid=$("input[name='eid']").val();
	var price=$("#price").val();
	layer_load('执行中，请稍候...',0);
	$.post(wapurl+"/member/index.php?c=rtop",{id:id,eid:eid,price:price},function(data){
		layer.closeAll();
		if(data==1){ 
			layermsg('请选择时长！',2,function(){window.location.reload();});return false;
		}else if(data==2){ 
			layermsg('非法操作！',2,function(){window.location.reload();});return false;
		}else if(data==3){ 
			layermsg('余额不足，请充值！',2,function(){window.location.reload();});return false;
		}else if(data==4){ 
			layermsg('简历置顶成功！',2,function(){window.location.reload();});return false;
		}else if(data==5){ 
			layermsg('操作失败！',2,function(){window.location.reload();});return false;
		}
	})
}




function reason(url){
	var reason=$("#reasonid").val(); 
	if(reason==""){
		layermsg('请选择举报原因！');return false;
	}
	var eid=$("#eid").val(); 
	$.post(url,{reason:reason,eid:eid},function(data){ 
		layer.closeAll();
		if(data=='0'){
			layermsg('举报失败！');return false;
		}else if(data=='1'){
			layermsg('举报成功！');return false;
		}else if(data=='2'){
			layermsg('您已举报过该问题！');return false;
		}else if(data=='3'){
			layermsg('该问题已被他人举报！');return false;
		}else if(data=='no_login'){
			layermsg('请先登录！');return false;
		}
	});
} 
function ckReason(val){
	$("#reasonid").val(val);
}
function atn(id,url){//关注企业
	if(id){
		$.post(url,{id:id},function(data){
			if(data==1){
                layermsg('关注企业成功！',2,function(){window.location.reload();});return false; 
              /* layermsg('企业关注成功');
				$(".atn_"+id).removeClass('firm_name_gz');
				$(".atn_"+id).addClass('firm_name_gz_no'); 
				$("#atn_"+id).html("取消关注");*/
			}else if(data==2){
                layermsg('取消关注企业成功！',2,function(){window.location.reload();});return false; 
             /* layermsg('企业取消关注成功');
				$(".atn_"+id).removeClass('firm_name_gz_no');
				$(".atn_"+id).addClass('firm_name_gz'); 
				$("#atn_"+id).html("关注企业");*/
			}else if(data==3){
				layermsg('请先登录！只有个人用户才能关注');return false;
			}else if(data==4){
				layermsg('只有个人用户才能关注');return false;
			}
		});
	}
}

function addmsg(uid,img){
	var id=$("#content").attr('data-id');
	var content=$("#content").val();
	var authcode=$.trim($("#msg_CheckCode").val());
	if(content==""){
		layermsg('评论内容不能为空！'); return false;
	}
	if(authcode==""){
		layermsg('验证码不能为空！'); return false;
	}
	$.post(wapurl+"/index.php?c=company&a=savemsg",{id:id,content:content,authcode:authcode},function(data){
		if(data=='0'){
			layermsg('验证码错误！',2,function(){checkCode(img)});return false; 
		}else if(data==1){
			layermsg('请填写评论内容！');return false; 
		}else if(data==2){
			layermsg('发布评论成功，请等待审核！',2,function(){window.location.reload();});return false; 
		}else if(data==3){
			layermsg('发布评论成功！',2,function(){window.location.reload();});return false; 
		}else if(data==4){
			layermsg('发布评论失败！');return false; 
		}
		
	});	
}

function get_allmsg(id){ 
	var display=$("div[name='hide_"+id+"']").css("display");
	if(display=='none'){
		$("div[name='hide_"+id+"']").show();
		$("#click_"+id).html("收起评论");
	}else{
		$("div[name='hide_"+id+"']").hide();
		$("#click_"+id).html("查看全部评论");
	} 
} 

function submitreply(id,fid,url){
	var content = $("#reply_"+id).val();
	content=$.trim(content);
	if($.trim(content)==""){
		$("#reply_"+id).val("");
		layermsg('请输入回复内容！', 2, 8);return false; 
	}
	$.post(url,{nid:id,reply:content,fid:fid},function(data){
		if(data==1){ 
			layermsg('请先登录！', 2,8);return false;
		}else{
			var data = eval("("+data+")");
			var content = "";
			content = '<div class="Personals_cont_dy_pl"><div class="Personals_cont_dy_pl_tx"><img src="'+data.pic+'" width="28" height="35" onerror=\"showImgDelay(this,\''+errorimg+'\',2);\"></div><div class="Personals_cont_dy_pl_user"><div class="Personals_cont_dy_pl_user_n"><a href="'+data.url+'" target="_blank">'+data.nickname+'</a>: '+data.reply+'</div><div class="Personals_cont_dy_pl_user_m">'+data.ctime+'</div></div></div>';
			$("#commentlist_"+id).append(content);
			$("#comment_"+id).hide();
			$("#reply_"+id).val("");
			$("#comment"+id).show();	
		}
	});
}
function clicktext(id){ 
	$("#comment_"+id).show();
	$("#comment"+id).hide();
	$("#reply_"+id).focus(); 	
}

function onblurtext(id){
	var content = $("#reply_"+id).val();
	content=$.trim(content);
	if(content==""){
		$("#reply_"+id).val("");
		$("#comment_"+id).hide();
		$("#comment"+id).show();
	}
}
function checkLength(num,id) {
	var con = $("#reply_"+id).val();
	var content = con.length;
	
	if (con.length > num) { 
		con = con.substring(0, num);
		$("#reply_"+id).val(con); 
	} 
	if(con.length=="0"){
		$("#colornum_"+id).html("0");
	}else{
		$("#colornum_"+id).html(con.length);
	} 
}

function checksex(id){
	$(".yun_info_sex").removeClass('yun_info_sex_cur');
	$("#sex"+id).addClass('yun_info_sex_cur');
	$("#sex").val(id); 
	var addtype=$("#addtype").val();
	if(addtype=='addexpect'){
		$("#hidsex").attr("class","resume_tipok");
		$("#hidsex").html('');
	}
}
function footernav(type){
	var display =$("."+type).css('display');
	$("#footerjob").hide(); 
	if(display=='none'){ 
		$("."+type).show();
	}else{
		$("."+type).hide();
	}
}
//快捷登录绑定
function binduser(url){
	
	var username = $('#username').val();
	var password = $('#password').val();
	if(username && password){
		$.post(url,{username:username,password:password},function(data){
			var info = eval('('+data+')');
			if(info.url){
					layermsg('绑定成功！', 2,function(){window.location.href=wapurl+"member/index.php?c=set";}); 
			}else if(info.msg){
				layermsg(info.msg);return false;
			}
		});
	}else{
	
		layermsg('请输入需要绑定的账户、密码！');return false;
	}

}


function sign(){
	$.post(wapurl+"?c=ajax&a=sign",{rand:Math.random()},function(data){
		var data=eval("("+data+")");
			if(data.type=="-2"){
				layermsg('操作失败！',2,8);return false;
			}else{ 
				layermsg('签到成功！+'+data.integral,2,function(){window.location.reload()});return false;
			}
	});
}

function savepwd(){
	var password=$("#password").val();
	var passwordnew=$.trim($("#passwordnew").val());
	var passwordconfirm=$.trim($("#passwordconfirm").val());
	if(password<6){
		layermsg('原密码不正确！',2,8);return false;
	}
	if(passwordnew.length<6){
		layermsg('新密码长度必须大于等于6！',2,8);return false;
	}
	if(password == passwordnew){
		layermsg('请输入新密码不同于原密码！', 2, 8);return false;
	}
	if(passwordnew != passwordconfirm || passwordconfirm.length<6){
		layermsg('两次输入密码不一致！', 2, 8);return false;
	}
	$.post(wapurl+"?c=ajax&a=setpwd",{password:password,passwordnew:passwordnew,passwordconfirm:passwordconfirm},function(data){
		var data=eval("("+data+")");
		if(data.type==9){
			layermsg(data.msg,2,function(){
				window.location.href=wapurl+"index.php?c=login";
			});
		}else{
			layermsg(data.msg,2,data.type);return false;
		}
	});
}
//向左滚动
function marquee_l(time,id){
	$(function(){
		var _wrap=$(id);
		var _interval=time;
		var _moving;
		_wrap.hover(function(){
			clearInterval(_moving);
		},function(){
			_moving=setInterval(function(){
			var _field=_wrap.find('li:first');
			var _h=_field.height();
			_field.animate({marginLeft:-_h+'px'},800,function(){
			_field.css('marginLeft',10).appendTo(_wrap);
			})
		},_interval)
		}).trigger('mouseleave');
	});
}
//向上滚动
function marquee(time,id){
	$(function(){
		var _wrap=$(id);
		var _interval=time;
		var _moving;
		_wrap.hover(function(){
			clearInterval(_moving);
		},function(){
			_moving=setInterval(function(){
			var _field=_wrap.find('li:first');
			var _h=_field.height();
			_field.animate({marginTop:-_h+'px'},800,function(){
			_field.css('marginTop',0).appendTo(_wrap);
			})
		},_interval)
		}).trigger('mouseleave');
	});
}

// 关注机构
function teacheratn(uid){
	if(uid){
		$.post(wapurl+"index.php?c=ajax&a=atn_train",{uid:uid},function(data){
			if(data==1){
				$("#atn_"+uid).text(""); 
				$("#atn_"+uid).text("取消关注"); 
			}else if(data==2){
				$("#atn_"+uid).text(""); 
				$("#atn_"+uid).text("关注"); 
			}else if(data==3){
				layermsg('请先登录!',2,function(){window.location.href='index.php?c=login';});return false;
			}else if(data==4){
				layermsg('只有个人用户和hr才能关注', 2,8);return false;
			}
		});
	}
}
//请先登录
function pleaselogin(msg,url){
	layer.open({
		content:msg,
		btn: ['登录', '取消'],
		shadeClose: false,
		yes: function(){
			window.location.href=url;
		} 
	});
}
//切换账号
function notuser(type,name,mtype,url,id){
	var msg='<div id="notuser" style="width:350px;"> <div class="job_user_box"><div class="job_user_name">您当前帐号名为：<span class="job_user_name_s">'+name+'</span>，属于'+type+'。</div><div class="job_user_name_tip">请登录'+mtype+'</div><div><a href="javascript:void(0)" onclick="layer.closeAll()" class="job_user_box_hn">忽略</a> <a href="javascript:void(0)" onclick="switching(\''+url+'\',\''+id+'\')" class="job_user_box_qh">切换账户</a></div></div></div>'
	layer.open({
		type:1,
		shadeClose:false,
		content:msg 
	});
}

function switching(url,jobid){
	$.post(url,{jobid:jobid},function(data){  
		location.href=data; 
	})
}

