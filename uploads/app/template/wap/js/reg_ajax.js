function exitsid(id) {
	if(document.getElementById(id)) {
		return true;
	} else {
		return false;
	}
}

function check_comname() {
	var comname = $("#comname").val();
	if(comname == "") {
		$("#comname_yes").hide();
	}

	$.post(wapurl + "index.php?c=register&a=regcomname", {
		comname: comname
	}, function(data) {

		if(data == 0 && comname != "") {
			$("#comname_yes").show();
		} else {
				
			$("#like_com_list").html(data);
			layer.open({
				type: 1,
				title: '注册提示',
				closeBtn: 1,
				border: [10, 0.3, '#000', true],
				content: $("#like_company").html()
			});
			
			

		}
	});

}

function CloseToast(){
	layer.closeAll();
}


function check_com_address() {
	var address = $("#address").val();
	if(address == "") {
		$("#comaddress_yes").hide();
	} else {
		$("#comaddress_yes").show();
	}
}

function checkRegUser(target_form) {
	var regway = $("#regway").val();

	var isRealnameCheck = $("#isRealnameCheck").val();

	var authcode;
	var geetest_challenge;
	var geetest_validate;
	var geetest_seccode;

	var usertype = $("#usertype").val();

	if(exitsid("username")) {
		var username = $("#username").val();
		if(username == '') {
			layermsg("用户名不能为空！");
			return false;
		} else if(username.length < 2 || username.length > 16) {
			layermsg("用户名应在2-16位字符之间！");
			return false;
		}
	}

	if(exitsid("moblie")) {
		var reg = /^[1][3456789]\d{9}$/; //验证手机号码  
		var moblie = $("#moblie").val();
		if(moblie == "") {
			layermsg("请填写手机号！");
			return false;
		} else if(!reg.test(moblie)) {
			layermsg("手机格式不正确！");
			return false;
		}
	}

	if(exitsid("email")) {
		var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
		var email = $("#email").val();
		if(email == "") {
			layermsg("邮箱不能为空！");
			return false;
		} else if(!myreg.test(email)) {
			layermsg("邮箱格式不正确！");
			return false;
		}
	}

	var codesear = new RegExp('注册会员');
	if(codesear.test(code_web)) {
		if(code_kind == 1) {
			authcode = $.trim($("#checkcode").val());
			if(!authcode) {
				layermsg('图片验证码不能为空！');
				return false;
			}
		} else if(code_kind == 3) {
			geetest_challenge = $('input[name="geetest_challenge"]').val();
			geetest_validate = $('input[name="geetest_validate"]').val();
			geetest_seccode = $('input[name="geetest_seccode"]').val();
			if(geetest_challenge == '' || geetest_validate == '' || geetest_seccode == '') {
				$("#popup-submit").trigger("click");
				layermsg('请点击按钮进行验证！');
				return false;
			}
		}
	}

	var password = $("#password").val();
	if(password == "") {
		layermsg("密码不能为空！");
		return false;
	} else if(password.length < 6 || password.length > 20) {
		layermsg("密码长度应在6-20位！");
		return false;
	}
	if(exitsid("passconfirm")) {
		var passconfirm = $("#passconfirm").val();
		if(passconfirm == "") {
			layermsg("确认密码不能为空！");
			return false;
		} else if(password != passconfirm) {
			layermsg("两次密码不一致！");
			return false;
		}
	}

	if(exitsid("moblie_code")) {
		if($("#moblie_code").val() == "") {
			layermsg('短信验证码不能为空！');
			return false;
		}
	}

	if(usertype == 1) {
		if(exitsid("name")) {
			var name = $("#name").val();
			if(name == "") {
				layermsg("真实名称不能为空！");
				return false;
			}
		}
	} else if(usertype == 2) {
		if(exitsid("comname")) {
			var comname = $("#comname").val();
			if(comname == "") {
				layermsg("企业名称不能为空！");
				return false;
			}
		}

		if(exitsid("address")) {
			var address = $("#address").val();
			if(address == "") {
				layermsg("企业地址不能为空！");
				return false;
			}
		}

		if(exitsid("linkman")) {
			var linkman = $("#linkman").val();
			if(linkman == "") {
				layermsg("联系人不能为空！");
				return false;
			}
		}
	}

	if($("#xieyi").attr("checked") != 'checked') {
		layermsg('您必须同意注册协议才能成为本站会员！');
		return false;
	}

	post2ajax(target_form);
	return false;
}

function sendmsg(img) {
	var send = $("#send").val();
	var reg = /^[1][3456789]\d{9}$/; //验证手机号码  
	var moblie = $("#moblie").val();
	var code;
	var geetest_challenge;
	var geetest_validate;
	var geetest_seccode;
	var codesear = new RegExp('注册会员');
	if(moblie == "") {
		layermsg("请填写手机号！");
		return false;
	}
	var date = $("#moblie").attr("date");
	if(send > 0) {
		layermsg('请不要频繁重复发送！');
		return false;
	}
	if(date == 1 && send == 0) {
		if(codesear.test(code_web)) {
			if(code_kind == 1) {
				code = $.trim($("#checkcode").val());
				if(!code) {
					layermsg('请填写图片验证码！');
					return false;
				}
			} else if(code_kind == 3) {

				geetest_challenge = $('input[name="geetest_challenge"]').val();
				geetest_validate = $('input[name="geetest_validate"]').val();
				geetest_seccode = $('input[name="geetest_seccode"]').val();

				if(geetest_challenge == '' || geetest_validate == '' || geetest_seccode == '') {
					$("#popup-submit").trigger("click");
					layermsg('请点击按钮进行验证！');
					return false;
				}
			}
		}
		layer_load('执行中，请稍候...');
		$.post(wapurl + "/index.php?c=ajax&a=regcode", {
			moblie: moblie,
			code: code,
			geetest_challenge: geetest_challenge,
			geetest_validate: geetest_validate,
			geetest_seccode: geetest_seccode
		}, function(data) {
			layer.closeAll();
			if(data == 0) {
				layermsg('手机不能为空！', 2, function() {
					if(code_kind == 1) {
						checkCode(img);
					} else if(code_kind == 3) {
						$("#popup-submit").trigger("click");
					}
				});
				return false;
			} else if(data == 1) {
				layermsg('同一手机号一天发送次数已超！', 2, function() {
					if(code_kind == 1) {
						checkCode(img);
					} else if(code_kind == 3) {
						$("#popup-submit").trigger("click");
					}
				});
			} else if(data == 2) {
				layermsg('同一IP一天发送次数已超！', 2, function() {
					if(code_kind == 1) {
						checkCode(img);
					} else if(code_kind == 3) {
						$("#popup-submit").trigger("click");
					}
				});
			} else if(data == 3) {
				layermsg('短信还没有配置，请联系管理员！', 2, function() {
					if(code_kind == 1) {
						checkCode(img);
					} else if(code_kind == 3) {
						$("#popup-submit").trigger("click");
					}
				});
				return false;
			} else if(data == 4) {
				layermsg('请不要频繁重复发送！', 2, function() {
					if(code_kind == 1) {
						checkCode(img);
					} else if(code_kind == 3) {
						$("#popup-submit").trigger("click");
					}
				});
				return false;
			} else if(data == 5) {
				layermsg('图片验证码错误！', 2, function() {
					checkCode(img);
				});
				return false;
			} else if(data == 6) {

				layermsg('请点击按钮进行验证！', 2, 8, function() {
					if(code_kind == 1) {
						checkCode(img);
					} else if(code_kind == 3) {
						$("#popup-submit").trigger("click");
					}

				});
				return false;

				$("#popup-submit").trigger("click");
				return false;
			} else if(data == "发送成功!") {
				sendtime("121");
			} else {
				layermsg(data, 2, function() {
					if(code_kind == 1) {
						checkCode(img);
					} else if(code_kind == 3) {
						$("#popup-submit").trigger("click");
					}
				});
				return false;
			}
		})
	}
}

function sendtime(i) {
	i--;
	if(i == -1) {
		$("#time").html("重新获取");
		$("#send").val(0)
	} else {
		$("#send").val(1)
		$("#time").html(i + "秒");
		setTimeout("sendtime(" + i + ");", 1000);
	}
}

function check_email() {
	
	var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	var email = $("#email").val();
	if(email == "") {
		$("#email_yes").hide();
		layermsg("邮箱不能为空！");
		return false;
	}else if(!myreg.test(email)) {
		layermsg("邮箱格式不正确！");
		return false;
	}
	
	$.post(wapurl + "index.php?c=register&a=regemail", {
		email: email
	}, function(data) {

		if(data == 0 && email != "") {
			$("#email_yes").show();
		} else {
			 
				var data = eval('(' + data + ')');

				$("#email").val("");
				$("#zy_uid").val(data.uid);
				$("#zy_email").val(email);
				
				$("#desc_toast").html("2. 解除邮箱与该账号的绑定，解除绑定后，您无法继续用该邮箱登录");
				
				if(data.usertype == '1') {
					$("#zy_type").html("该邮箱已被注册为个人账号");
					if(data.name){
						$("#zy_name").html("个人名称：<span class=reg_have_tip_tit_name>" + data.name.substr(0, 1) + "**</span>");
					}

				} else if(data.usertype == '2') {
					$("#zy_type").html("该邮箱已被注册为企业账号");
					if(data.name){
						$("#zy_name").html("企业名称：<span class=reg_have_tip_tit_name>" + data.name + "</span>");
					}

				} 
				layer.open({
					type: 1,
					title: '邮箱已被占用',
					closeBtn: 1,
					border: [10, 0.3, '#000', true],
					content: $("#written_off").html()
				});
			
		}
	});
}

function check_moblie() {
	var moblie = $("#moblie").val();
	if(moblie == "") {
		$("#moblie_yes").hide();
		layermsg("手机不能为空！");
		return false;
	}else if(!isjsMobile(moblie)){
		layermsg("手机格式不正确！");
		return false;
	}
	
	$.post(wapurl + "index.php?c=register&a=regmoblie", {
		moblie: moblie
	}, function(data) {

		if(data == 0 && moblie != "") {
			$("#moblie").attr('date', '1');
			$("#moblie_yes").show();
		} else {
			
			if(data == 2) {
				msg = "该手机号已被禁止使用！";
			} else {
				var data = eval('(' + data + ')');

				$("#moblie").val("");
				$("#zy_uid").val(data.uid);
				$("#zy_mobile").val(moblie);
				if(data.usertype == '1') {
					$("#zy_type").html("该手机号已被注册为个人账号");
					if(data.name){
						$("#zy_name").html("个人名称：<span class=reg_have_tip_tit_name>" + data.name.substr(0, 1) + "**</span>");
					}

				} else if(data.usertype == '2') {
					$("#zy_type").html("该手机号已被注册为企业账号");
					if(data.name){
						$("#zy_name").html("企业名称：<span class=reg_have_tip_tit_name>" + data.name + "</span>");
					}

				}

				layer.open({
					type: 1,
					title: '手机号已被占用',
					closeBtn: 1,
					border: [10, 0.3, '#000', true],
					content: $("#written_off").html()
				});
			}
		}
	});

}

function check_username() {
	var username = $("#username").val();
	if(username == "") {
		$("#username_yes").hide();
	} else {
		$("#username_yes").show();
	}
}

function check_password() {
	var password = $("#password").val();
	if(password == "") {
		$("#password_yes").hide();
	} else {
		$("#password_yes").show();
	}
}

function check_passconfirm() {
	var passconfirm = $("#passconfirm").val();
	if(passconfirm == "") {
		$("#passconfirm_yes").hide();
	} else {
		$("#passconfirm_yes").show();
	}
}

function check_code() {
	var checkcode = $("#checkcode").val();
	if(checkcode == "") {
		$("#checkcode_yes").hide();
	} else {
		$("#checkcode_yes").show();
	}
}

function check_moblie_code() {
	var moblie_code = $("#moblie_code").val();
	if(moblie_code == "") {
		$("#moblie_code_yes").hide();
	} else {
		$("#moblie_code_yes").show();
	}
}

function check_realname() {
	var name = $("#name").val();
	if(name == "") {
		$("#realname_yes").hide();
	} else {
		$("#realname_yes").show();
	}
}

function check_linkman() {
	var linkman = $("#linkman").val();
	if(linkman == "") {
		$("#linkman_yes").hide();
	} else {
		$("#linkman_yes").show();
	}
}

function showservices() {
	$('#services').show();
}

function checkRegLt(target_form) {
	var username = $("#username").val();
	if(username == '') {
		layermsg("用户名不能为空！");
		return false;
	} else if(username.length < 2 || username.length > 16) {
		layermsg("用户名应在2-12位字符之间！");
		return false;
	}
	var password = $("#password").val();
	if(password == "") {
		layermsg("密码不能为空！");
		return false;
	} else if(password.length < 6 || password.length > 20) {
		layermsg("密码长度应在6-20位！");
		return false;
	}
	if(exitsid("passconfirm")) {
		var passconfirm = $("#passconfirm").val();
		if(passconfirm == "") {
			layermsg("确认密码不能为空！");
			return false;
		} else if(password != passconfirm) {
			layermsg("两次密码不一致！");
			return false;
		}
	}
	var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	var email = $("#email").val();
	if(email == "") {
		layermsg("邮箱不能为空！");
		return false;
	} else if(!myreg.test(email)) {
		layermsg("邮箱格式不正确！");
		return false;
	}
	var reg = /^[1][3456789]\d{9}$/; //验证手机号码  
	var moblie = $("#moblie").val();
	if(moblie == "") {
		layermsg("请填写手机号！");
		return false;
	} else if(!reg.test(moblie)) {
		layermsg("手机格式不正确！");
		return false;
	}

	var isRealnameCheck = $("#isRealnameCheck").val();
	if(isRealnameCheck == 1) {

		if(exitsid("name")) {
			var name = $("#name").val();
			if(name == "") {
				layermsg("真实名称不能为空！");
				return false;
			}
		}

		var moblie_code = $("#moblie_code").val();
		if(moblie_code.length < 4) {
			layermsg('请输入正确的短信验证码！');
			return false;
		}
	}

	if($("#xieyi").attr("checked") != 'checked') {
		layermsg('您必须同意注册协议才能成为本站会员！');
		return false;
	}

	var authcode;
	var geetest_challenge;
	var geetest_validate;
	var geetest_seccode;
	var codesear = new RegExp('注册会员');

	if(codesear.test(code_web)) {

		if(code_kind == 1) {
			authcode = $.trim($("#checkcode").val());
			if(!authcode) {
				layermsg('请填写验证码！');
				return false;
			}
		} else if(code_kind == 3) {

			geetest_challenge = $('input[name="geetest_challenge"]').val();
			geetest_validate = $('input[name="geetest_validate"]').val();
			geetest_seccode = $('input[name="geetest_seccode"]').val();

			if(geetest_challenge == '' || geetest_validate == '' || geetest_seccode == '') {
				$("#popup-submit").trigger("click");
				layermsg('请点击按钮进行验证！');
				return false;
			}
		}
	}
	post2ajax(target_form);
	return false;
}

function CheckPW() {

	$("#postpw .pw").html("<div class=tiny_show_tckbox_cont_p>请输入登录密码</div><div class=tiny_show_tckbox_p><input type=\"password\" value=\"\" id=\"login_password\" class=tiny_show_tckbox_text></div><div class=tiny_show_tckbox_bth><input type=\"submit\" value=\"确定\" class=tiny_show_tckbox_bth1 onclick=\"post_pass();\" /></div>")
	postpwhtml = $("#postpw").html();
	$("#postpw").html('');

	layer.open({
		type: 1,
		title: '验证身份',
		closeBtn: [0, true],
		border: [10, 0.3, '#000', true],
		content: postpwhtml,
		cancel: function() {
			$("#postpw").html("<div class=\"tiny_show_tckbox_cont pw\"></div>");
		}
	});
}

function post_pass() {
	var zyuid = $("#zy_uid").val();
	var mobile = $("#zy_mobile").val();
	var email = $("#zy_email").val();
	var pw = $("#login_password").val();
	if(zyuid == "") {
		layermsg('该用户不存在');
		return false;
	}
	if(pw == "") {
		layermsg('请输入密码');
		return false;
	}

	$.post(wapurl + "index.php?c=register&a=writtenoff", {
		zyuid: zyuid,
		mobile: mobile,
		email: email,
		pw: pw
	}, function(data) {
		if(data == 2) {
			layermsg('密码错误！');
			return false;
		} else if(data == 1){
			layer.closeAll();
			layermsg("解绑成功", 2, function() {
				location.reload();
			});
		}
	})
}