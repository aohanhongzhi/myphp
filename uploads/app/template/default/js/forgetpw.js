 function uppassword(id){
	var password = $("#password").val();
	S_level=checkStrong(password);
	switch(S_level) { 
	case 0:
		$(".slist_dan").removeClass("psw_span_cur");
	case 1:
		$("#pass1_"+id).addClass("psw_span_red");
		$("#pass2_"+id).removeClass("psw_span_yellow");
		$("#pass3_"+id).removeClass("psw_span_green");
	break; 
	case 2: 
		$("#pass1_"+id).removeClass("psw_span_red");
		$("#pass2_"+id).addClass("psw_span_yellow");
		$("#pass3_"+id).removeClass("psw_span_green");
	break; 
	default: 
		$("#pass1_"+id).removeClass("psw_span_red");
		$("#pass2_"+id).removeClass("psw_span_yellow");
		$("#pass3_"+id).addClass("psw_span_green");
	} 
}
function checkStrong(sPW){
	if (sPW.length<=4) 
	return 0; 
	Modes=0; 
	for (i=0;i<sPW.length;i++){
	Modes|=CharMode(sPW.charCodeAt(i)); 
	}
	return bitTotal(Modes); 
} 
function CharMode(iN){ 
	if (iN>=48 && iN <=57) 
	return 1; 
	if (iN>=65 && iN <=90) 
	return 2; 
	if (iN>=97 && iN <=122)  
	return 4; 
	else 
	return 8; 
} 

 function bitTotal(num){ 
	modes=0; 
	for (i=0;i<4;i++){ 
	if (num & 1) modes++; 
	num>>>=1; 
	} 
	return modes; 
} 

function isChecked(id) {
	var obj = $.trim($("#" + id).val());
	var msg;
	if(id == "mobile") {
		if(obj == '') {
			msg = "请填写手机号码！";
			layer.msg(msg, 2, 8);
			return false;
		} else if(isjsMobile(obj) == false) {
			msg = "手机号码格式错误！";
			layer.msg(msg, 2, 8);
			return false;
		}
	}

	if(id == "email") {
		var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
		if(obj == "") {
			msg = '邮箱不能为空！';
			layer.msg(msg, 2, 8);
			return false;
		} else if(!myreg.test(obj)) {
			msg = "邮箱格式错误！";
			layer.msg(msg, 2, 8);
			return false;
		}
	}
}

function send_msg() {
 	var mobile = $('#mobile').val();
	if(mobile == "") {
		layer.msg("请填写手机号码！", 2, 8);
		return false;
	} else if(isjsMobile(mobile) == false) {
		layer.msg("手机号码格式错误！", 2, 8);
		return false;
	}
	var i = layer.load('执行中，请稍候...', 0);
	$.ajaxSetup({
		cache: false
	});
	$.post(weburl + "/index.php?m=forgetpw&c=sendCode", {sendtype:'mobile',mobile: mobile}, function(data) {
		layer.close(i);
		var data = eval('(' + data + ')');
  		if(data.error == 0) {
			layer.msg('发送成功！', 2, 9,function(){
				send(121);
			});
		}else{
			layer.msg(data.msg, 2, 8);
			return false;
		}
	})
}

function send_email() {
 	var email = $('#email').val();
 	var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	if(email == "") {
		layer.msg("请填写邮箱！", 2, 8);
		return false;
	} else if(!myreg.test(email)) {
		layer.msg("邮箱格式错误！", 2, 8);
		return false;
	}
	var i = layer.load('执行中，请稍候...', 0);
	$.ajaxSetup({
		cache: false
	});
	$.post(weburl + "/index.php?m=forgetpw&c=sendCode", {sendtype:'email',email: email}, function(data) {
		layer.close(i);
		var data = eval('(' + data + ')');
  		if(data.error == 0) {
			layer.msg('发送成功！', 2, 9,function(){
				send(121);
			});
		}else{
			layer.msg(data.msg, 2, 8);
			return false;
		}
	})
}

function send(i) {
 	i--;
	if(i == -1) {
		$("#send_msg_tip").val("重新获取");
 	} else {
 		$("#send_msg_tip").val(i + "秒");
		setTimeout("send(" + i + ");", 1000);
	}
}

function checklink() {
	var username = $("#username").val();
	var linkman = $("#linkman").val();
	var linkphone = $("#linkphone").val();
	var linkemail = $("#linkemail").val();
	if(linkman == '') {
		layer.msg("请填写联系人！", 2, 8);return false;
	}
	if(linkphone == '') {
		layer.msg("请填写联系电话！", 2, 8);return false;
 	} else if(isjsMobile(linkphone) == false && isjsTell(linkphone) == false) {
 		layer.msg("联系电话格式错误！", 2, 8);return false;
 	}
	var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	if(linkemail == '') {
		layer.msg("请填写联系邮箱！", 2, 8);return false;
 	} else if(!myreg.test(linkemail)) {
		layer.msg("邮箱格式错误！", 2, 8);return false;
	}
	var i = layer.load('执行中，请稍候...', 0);
	
	$.ajaxSetup({
		cache: false
	});
	$.post(weburl + "/index.php?m=forgetpw&c=checklink", {
		username: username,
		linkman: linkman,
		linkphone: linkphone,
		linkemail: linkemail
	}, function(data) {
		layer.close(i);
		var data = eval('(' + data + ')');
   		if(data.error == 0) {
 			$("#nav2").attr('class', 'flowsfrist');
			$("#nav3").attr('class', 'flowsfrist');
			$("#pw_style").hide();
			$("#shensu_type").hide();
			$("#finish").show();
		}else if(data.error==8){
      layer.msg(data.msg, 2, 8);
      return false;
		
		}else{
      	layer.msg(系统正忙, 2, 8);
			location.reload();
    }
	})
}

function forgetPwNext() {
	var sendtype = $("#sendtype").val(),
		mobile = $("#mobile").val(),
		mobile_vcode = $("#mobile_vcode").val(),
		email = $("#email").val(),
		email_vcode = $("#email_vcode").val(),
		code = '';
	if(sendtype != "email" && sendtype != "mobile" && sendtype != "shensu") {
		layer.msg("请选择密码找回方式！",2,8);return false;
	}
	if(sendtype == 'mobile') {
		if(mobile == "") {
			layer.msg("请填写手机号码！", 2, 8);
			return false;
		} else if(isjsMobile(mobile) == false) {
			layer.msg("手机号码格式错误！", 2, 8);
			return false;
		}
		if(mobile_vcode == "") {
			layer.msg("请输入短信验证码！", 2, 8);
			return false;
 		}
		code = mobile_vcode;
	} else if(sendtype == 'email') {
		var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
		if(email == "") {
			layer.msg("请填写邮箱！", 2, 8);
			return false;
		} else if(!myreg.test(email)) {
			layer.msg("邮箱格式错误！", 2, 8);
			return false;
		}
		if(email_vcode == "") {
			layer.msg("请输入邮箱验证码！", 2, 8);
			return false;
 		}
		code = email_vcode;
	}
	var i = layer.load('执行中，请稍候...', 0);
	
	$.post(weburl + "/index.php?m=forgetpw&c=checksendcode", {sendtype:sendtype,mobile: mobile,email: email,code:code}, function(data) {
		layer.close(i);
		var data = eval('(' + data + ')');
   		if(data.error == 0) {
  			$("#nav2").attr('class', 'flowsfrist');
 			$("#pw_style").hide();
			$("#mobile_type").hide();
			$("#email_type").hide();
			$("#shensu_type").hide();
			
			$("#resetpw").show();
			$("#fuid").val(data.uid);
			$("#uname").val(data.username);
			 
			$("#fmobile").val(mobile);
			$("#femail").val(email);
			$("#fcode").val(code);

		}else{
			layer.msg(data.msg, 2, 8);
			return false;
		}
	})
}

function editpw() {
	var username = $("#uname").val();
	var uid = $("#fuid").val();
	var mobile = $.trim($("#fmobile").val());
	var email = $.trim($("#femail").val());
	var code = $("#fcode").val();

	var password = $.trim($("input[name=password]").val());
	var passwordconfirm = $.trim($("input[name=passwordconfirm]").val());
	
	if(password != passwordconfirm) {
		layer.msg('两次输入密码不一致！', 2, 8);
		return false;
	} else if(password.length < 6) {
		layer.msg('密码长度必须大于等于6！', 2, 8);
		return false;
	} else {
		layer.load('执行中，请稍候...', 0);
		
		$.post(weburl + "/index.php?m=forgetpw&c=editpw", {
			username: username,
			uid: uid,
			mobile: mobile,
			email: email,
			code: code,
  			password: password,
			passwordconfirm: passwordconfirm
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == 0) {
 				$("#nav3").attr('class', 'flowsfrist');
 				$("#resetpw").hide();
 				$("#pw_success").show();
			}else{
				layer.msg(data.msg, 2, 8);
				return false;
			}
		});
	}
}
