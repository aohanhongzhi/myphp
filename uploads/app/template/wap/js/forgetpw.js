var sendtypeData = [{
	type: 'moblie',
	text: '通过手机找回密码'
}, {
	type: 'email',
	text: '通过邮箱找回密码'
}, {
	type: 'shensu',
	text: '账号申诉'
}];
(function($, doc) {
	$.init();
	$.ready(function() {
		var sendtypePicker = new $.PopPicker();
		sendtypePicker.setData(sendtypeData);
		var sendtypePickerBtn = doc.getElementById('sendtypePicker');
		var sendtype = doc.getElementById('sendtype');
		var pickerText = doc.getElementById('pickerText');
		sendtypePickerBtn.addEventListener('tap', function(event) {
			sendtypePicker.show(function(items) {
				sendtype.value = items[0].type;
				pickerText.innerText = items[0].text;
				if(items[0].type == 'moblie') {
					doc.getElementById("moblieshow").style.display = 'block';
					doc.getElementById("emailshow").style.display = "none";
					doc.getElementById("shensushow").style.display = "none";
				} else if(items[0].type == 'email') {
					doc.getElementById("moblieshow").style.display = 'none';
					doc.getElementById("emailshow").style.display = "block";
					doc.getElementById("shensushow").style.display = "none";
				} else if(items[0].type == 'shensu') {
					doc.getElementById("moblieshow").style.display = 'none';
					doc.getElementById("emailshow").style.display = "none";
					doc.getElementById("shensushow").style.display = "block";
				}
			});
		}, false);
	});
})(mui, document);

var Timer;
var smsTimer_time = 90; //倒数 90
var smsTimer_flag = 90; //倒数 90
var smsTime_speed = 1000; //速度 1秒钟
//发送手机短信
function send_msg() {
	var moblie = $('#moblie').val();
	if(moblie == "") {
		return mui.toast('请输入手机号');
	} else if(isjsMobile(moblie) == false) {
		return mui.toast('手机号格式错误');
	}
	if(smsTimer_time == smsTimer_flag) {
		Timer = setInterval("smsTimer($('#send_msg_tip'))", smsTime_speed);
		mui.post(wapurl + "?c=forgetpw&a=sendcode", {
			sendtype: 'moblie',
			moblie: moblie
		}, function(data) {
			if(data.error != 0) {
				clearInterval(Timer);
				return mui.toast(data.msg);
			}
		}, 'json');
	} else {
		return mui.toast('请勿重复发送！');
	}
}

function send_email() {
	var email = $('#email').val();
	var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	if(email == "") {
		return mui.toast('请输入邮箱');
	} else if(!myreg.test(email)) {
		return mui.toast('邮箱格式错误');
	}
	if(smsTimer_time == smsTimer_flag) {
		Timer = setInterval("smsTimer($('#send_email_tip'))", smsTime_speed);
		mui.post(wapurl + "?c=forgetpw&a=sendcode", {
			sendtype: 'email',
			email: email
		}, function(data) {
			if(data.error != 0) {
				clearInterval(Timer);
				return mui.toast(data.msg);
			}
		}, 'json');
	} else {
		return mui.toast('请勿重复发送！');
	}
}

function exitsid(id) {
	if(document.getElementById(id)) {
		return true;
	} else {
		return false;
	}
}
//倒计时
function smsTimer(obj) {
	if(smsTimer_flag > 0) {
		$(obj).html('重新发送(' + smsTimer_flag + 's)');
		$(obj).attr({
			'style': 'background:#909394;'
		});
		smsTimer_flag--;
	} else {
		$(obj).html('重新发送');
		$(obj).removeAttr('style');
		smsTimer_flag = smsTimer_time;
		clearInterval(Timer);
	}
}

function forgetPwNext() {
	var sendtype = $("#sendtype").val(),
		moblie = $("#moblie").val(),
		moblie_vcode = $("#moblie_vcode").val(),
		email = $("#email").val(),
		email_vcode = $("#email_vcode").val(),
		code = '';
	if(sendtype != "email" && sendtype != "moblie" && sendtype != "shensu") {
		return mui.toast("请选择找回密码方式");
	}
	if(sendtype == 'moblie') {
		if(moblie == "") {
			return mui.toast('请输入手机号');
		} else if(isjsMobile(moblie) == false) {
			return mui.toast('手机号格式错误');
		}
		if(moblie_vcode == "") {
			return mui.toast('请输入短信验证码');
		}
		code = moblie_vcode;
	} else if(sendtype == 'email') {
		var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
		if(email == "") {
			return mui.toast('请输入邮箱');
		} else if(!myreg.test(email)) {
			return mui.toast('邮箱格式错误');
		}
		if(email_vcode == "") {
			return mui.toast('请输入邮箱验证码');
		}
		code = email_vcode;
	}
	mui.post(wapurl + "?c=forgetpw&a=checksendcode", {
		sendtype: sendtype,
		moblie: moblie,
		email: email,
		code: code
	}, function(data) {
		if(data.error == 0) {
			$("#path1").attr('class', 'currents_er');
			$("#path2").attr('class', 'currents');
			$("#backpicker").hide();
			$("#moblieshow").hide();
			$("#emailshow").hide();
			$("#shensushow").hide();
			$("#resetpw").show();
			$("#fuid").val(data.uid);
			$("#username").val(data.username);
			$("#fmobile").val(moblie);
			$("#femail").val(email);
			$("#fcode").val(code);
		} else {
			return mui.toast(data.msg);
		}
	}, 'json');
}

function editpw() {
	var uid = $("#fuid").val(),
		username = $("#username").val(),
		mobile = $.trim($("#fmobile").val()),
		email = $.trim($("#femail").val()),
		code = $("#fcode").val(),
		pwd = $.trim($("#password").val()),
		pwdconfirm = $.trim($("#passwordconfirm").val());
	if($.trim(uid) == "" || $.trim(username) == "") {
		mui.toast('请先验证后再修改密码', '提示', '确定', function() {
			location.reload();
		});
		return false;
	} else if(pwd.length < 6) {
		return mui.toast('密码长度必须大于等于6');
	} else if(pwd != pwdconfirm) {
		return mui.toast('两次输入密码不一致');
	} else {
		layer_load('执行中，请稍候...');
		mui.post(wapurl + "?c=forgetpw&a=editpw", {
			username: username,
			uid: uid,
			mobile: mobile,
			email: email,
			code: code,
			password: pwd,
			passwordconfirm: pwdconfirm
		}, function(data) {
			layer.closeAll();
			if(data.error == 0) {
				$("#path2").attr('class', 'currents_er');
				$("#path3").attr('class', 'currents');
				$("#resetpw").hide();
				$("#succeed").show();
			} else {
				return mui.toast(data.msg);
			}
		}, 'json');
	}
}

function checklink(img) {
	var username = $("#username").val();
	var linkman = $("#linkman").val();
	var linkphone = $("#linkphone").val();
	var linkemail = $("#linkemail").val();
	if(linkman == '') {
		return mui.toast("请填写联系人！");
	}
	if(linkphone == '') {
		return mui.toast("请填写联系电话！");
	} else if(isjsMobile(linkphone) == false && isjsTell(linkphone) == false) {
		return mui.toast("联系电话格式错误！");
	}
	var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	if(linkemail == '') {
		return mui.toast("请填写联系邮箱！");
	} else if(!myreg.test(linkemail)) {
		return mui.toast("邮箱格式错误！");
	}
	layer_load('执行中，请稍候...');
	mui.post(wapurl + "?c=forgetpw&a=checklink", {
		username: username,
		linkman: linkman,
		linkphone: linkphone,
		linkemail: linkemail,
	}, function(data) {
		layer.closeAll();
		if(data.error == 0) {
			$("#path1").attr('class', 'currents_er');
			$("#path3").attr('class', 'currents');
			$("#backpicker").hide();
			$("#shensushow").hide();
			$("#finish").show();
		}else if(data.error == 8){
      layermsg(data.msg, 2);
      return false;
    }else {
			layermsg("系统正忙", 2, function() {
				location.reload();
			})
		}
	}, 'json');
}