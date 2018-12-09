function toDate(str) {
	var sd = str.split("-");
	return new Date(sd[0], sd[1], sd[2]);
}
//关注
function ltatn(id) {
	var atn_ok = '';
	var atn_cancel = '';
	var tag_name = $("#guanzhu" + id)[0].tagName;
	atn_ok = '+关注';
	atn_cancel = '取消关注';
	if(id) {
		layer_load('执行中，请稍候...', 0);
		$.post(wapurl + "/index.php?c=ajax&a=atn", {
			id: id
		}, function(data) {
			layer.closeAll();
			var num = $("#atn" + id).html();
			if(data == 0) {
				layermsg('只有个人用户才可以关注！');
				return false;
			} else if(data == "1") {
				num = parseInt(num) + 1;
				$("#atn" + id).html(num);
				if(tag_name == 'INPUT') {
					$("#guanzhu" + id).val(atn_cancel);
				} else {
					$("#guanzhu" + id).html(atn_cancel);
				}
				layermsg('关注成功！');
				return false;
			} else if(data == "2") {
				num = parseInt(num) - 1;
				if(num < 1) {
					num = "0";
				}
				$("#atn" + id).html(num);
				if(tag_name == 'INPUT') {
					$("#guanzhu" + id).val(atn_ok);
				} else {
					$("#guanzhu" + id).html(atn_ok);
				}
				layermsg('取消关注！');
				return false;
			} else if(data == 3) {
				layermsg('您还没有登录！');
				return false;
			} else if(data == 4) {
				layermsg('自己不能关注自己！');
				return false;
			}
		});
	}
}
//猎头发布咨询
function ltmsg(img) {
	var msg_content = $.trim($("#msg_content").val());
	var authcode = $("#msg_CheckCode").val();
	var jobid = $("#jobid").val();
	var job_uid = $("#job_uid").val();
	var com_name = $("#com_name").val();
	var job_name = $("#job_name").val();
	if(msg_content == '') {
		layermsg('咨询内容不能为空！');
		return false;
	} else if(authcode == '') {
		layermsg('验证码不能为空！');
		return false;
	} else {
		layer_load('执行中，请稍候...', 0);
		$.post(wapurl + "/index.php?c=ajax&a=pl", {
			content: msg_content,
			authcode: authcode,
			jobid: jobid,
			job_uid: job_uid,
			com_name: com_name,
			job_name: job_name
		}, function(data) {
			layer.closeAll();
			if(data == 0) {
				layermsg('只有个人用户才可以关注！');
				return false;
			} else if(data == 1) {
				layermsg('留言成功！', 2, function() {
					location.reload();
				});
				return false;
			} else if(data == 2) {
				layermsg('咨询内容不能为空！');
				return false;
			} else if(data == 3) {
				layermsg('您还没有登录！');
				return false;
			} else if(data == 4) {
				layermsg('验证码不能为空！');
				return false;
			} else if(data == 5) {
				layermsg('验证码错误！', 2, function() {
					checkCode(img);
				});
				return false;
			} else if(data == 6) {
				layermsg('咨询失败！');
				return false;
			} else if(data == 7) {
				layermsg('该企业暂不接受相关咨询！');
				return false;
			}

		});
	}
}
//申请职位
function ypjob(type, uid, job_id) {
	if(uid == "") {
		layermsg('您还没有登录！');
		return false;
	} else {
		//layer.confirm('确定申请该职位吗？', function(){
		layer_load('执行中，请稍候...', 0);
		$.post(wapurl + "/index.php?c=ajax&a=yqjob", {
			type: type,
			job_id: job_id
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			layermsg(data.msg, 2, function() {
				location.reload();
			});
			return false;
		})
		//});
	}
}
//收藏猎头职位 type:普通职位 1 ，公司发布的猎头职位 2，猎头发布的职位 3
function fav_hjob(id) {
	layer_load('执行中，请稍候...', 0);
	$.post(wapurl + "/index.php?c=ltjob&a=favjob", {
		id: id
	}, function(data) {
		layer.closeAll();
		if(data == '0') {
			layermsg('请先登录！！');
			return false;
		} else if(data == '1') {
			layermsg('您已收藏过该职位！');
			return false;
		} else if(data == '2') {
			layermsg('收藏成功！', 2, function() {
				location.reload();
			});
			return false;
		} else if(data == '3') {
			layermsg('对不起，您不是个人用户，无法收藏职位！');
			return false;
		}
	});
}
//推荐人才
function ltrecuser() {
	var uid = document.getElementById('uid').value;
	var job_uid = document.getElementById('job_uid').value;
	var job_id = document.getElementById('job_id').value;
	var name = document.getElementById('name').value;
	var content = document.getElementById('content').value;
	var phone = document.getElementById('phone').value;
	var hy = document.getElementById('hy').value;
	var job_post = document.getElementById('job_post').value;
	var provinceid = document.getElementById('provinceid').value;
	var cityid = document.getElementById('cityid').value;
	var three_cityid = document.getElementById('three_cityid').value;
	var minsalary = document.getElementById('minsalary').value;
	var maxsalary = document.getElementById('maxsalary').value;
	var type = document.getElementById('type').value;
	var report = document.getElementById('report').value;
	var birthday = document.getElementById('birthday').value;
	var edu = document.getElementById('edu').value;
	var exp = document.getElementById('exp').value;
	var email = document.getElementById('email').value;
	var sex = document.getElementById('sex').value;
	var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	var reg = /^[1][3456789]\d{9}$/; //验证手机号码  
	if(name == "") {
		return mui.toast('姓名不能为空！');
	}
	if(sex == '') {
		return mui.toast('请选择性别！');
	}
	if(birthday == '') {
		return mui.toast('请选择出生年月！');
	}
	if(edu == '') {
		return mui.toast('请选择最高学历！');
	}
	if(exp == '') {
		return mui.toast('请选择工作经验！');
	}
	if(phone == "") {
		return mui.toast('手机不能为空！');
	}
	if(!reg.test(phone)) {
		return mui.toast('手机格式不正确！');
	}
	if(email != "" && !myreg.test(email)) {
		return mui.toast('邮箱格式不正确！');
	}
	if(hy == '') {
		return mui.toast('请选择从事行业！');
	}
	if(job_post == '') {
		return mui.toast('请选择期望职位！');
	}
	if(three_cityid == '') {
		return mui.toast('请选择期望城市！');
	}
	if(minsalary == '') {
		return mui.toast('请填写期望薪资！');
	}
	if(maxsalary) {
		if(parseInt(minsalary) >= parseInt(maxsalary)) {
			return mui.toast('最低薪资必须小于最高薪资！');
		}
	}
	if(type == '') {
		return mui.toast('请选择工作性质！');
	}
	if(report == '') {
		return mui.toast('请选择到岗时间！');
	}
	if(content == "") {
		return mui.toast('推荐描述不能为空！');
	}

	$.post('index.php?c=ltjob&a=recusersave', {
		uid: uid,
		job_uid: job_uid,
		job_id: job_id,
		name: name,
		content: content,
		phone: phone,
		hy: hy,
		job_classid: job_post,
		provinceid: provinceid,
		cityid: cityid,
		three_cityid: three_cityid,
		minsalary: minsalary,
		maxsalary: maxsalary,
		type: type,
		report: report,
		birthday: birthday,
		edu: edu,
		exp: exp,
		email: email,
		sex: sex,
		submit: 'submit'
	}, function(data) {

		if(data == '1') {
			//mui.openWindow({
			//url: 'index.php?c=ltjob&a=recuser&id='+job_id
			//})
			mui.alert("人才推荐成功","提示",function(){
				location.reload();
			});
			return false;

		} else if(data == '2') {
			return mui.toast('推荐失败！');
		} else if(data == '3') {
			return mui.toast('好友姓名不能为空！');
		} else if(data == '4') {
			return mui.toast('好友手机不能为空！');
		} else if(data == '5') {
			return mui.toast('手机格式不正确！');
		} else if(data == '6') {
			return mui.toast('推荐描述不能为空！');
		}
	});
}

//猎头基本信息擅长行业、职位清除
function ltremoves(type) {
	if(type == 'lthy') {
		$("#qw_hy").val("");
	} else if(type == 'ltjob') {
		$("#job").val("");
	}
	$("#" + type + "name").val("请选择");
	$("input[name='" + type + "class']").removeAttr("checked");
	$("input[name='" + type + "classone']").removeAttr("checked");
	$("input[name='" + type + "classone']").removeAttr("disabled");
}
//猎头基本信息擅长行业、职位确认
function ltrealy(type) {
	var info = "";
	var value = "";
	$("input[name='" + type + "class']:checked").each(function() {
		var obj = $(this).val();
		var name = $(this).attr("data");
		if(info == "") {
			info = obj;
			value = name;
		} else {
			var jclass = $(this).attr("class");
			var rej = jclass.split("jobone")[1];
			if(info.indexOf(rej) < 0) {
				info = info + "," + obj;
				value = value + "," + name;
			}
		}
	})
	$("input[name='" + type + "classone']:checked").each(function() {
		obj = $(this).val();
		name = $(this).attr("data");
		if(info == "") {
			info = obj;
			value = name;
		} else {
			var oneclass = $(this).attr("class");
			var ret = oneclass.split("one")[1];
			if(info.indexOf(ret) < 0) {
				info = info + "," + obj;
				value = value + "," + name;
			}
		}
	})

	if(info == "") {
		layermsg("请选择！");
		return false;
	} else {
		if(type == 'lthy') {
			$("#qw_hy").val(info);
		} else if(type == 'ltjob') {
			$("#job").val(info);
		}
		if(type == 'lthy' || type == 'ltjob') {
			$("#" + type + "name").html(value);
		}

		$("#" + type + "name").val(value);
		Closes(type);
	}
}
//猎头基本信息擅长行业、职位选择
function ltchecked_input(id, type) {
	if(type == 'lthy') {
		if($("#r" + id).is(':checked')) {
			$("#r" + id).addClass('xz');
			$(".one" + id).removeClass('xz');
			$(".one" + id).attr('checked', false);
			$(".one" + id).attr('disabled', 'disabled');
		} else {
			$("#r" + id).removeClass('xz');
			$(".one" + id).attr('disabled', false);
			$(".one" + id).attr('checked', false);
		}
	} else if(type == 'ltjob') {
		if($("#j" + id).is(':checked')) {
			$("#j" + id).addClass('xzj');
			$(".jobone" + id).removeClass('xzj');
			$(".jobone" + id).attr('checked', false);
			$(".jobone" + id).attr('disabled', 'disabled');
		} else {
			$("#j" + id).removeClass('xzj');
			$(".jobone" + id).attr('disabled', false);
			$(".jobone" + id).attr('checked', false);
		}
	}
	//var class_length = $("input[name='"+type+"class']:checked").length;
	/*if((class_length)>2){
		layermsg('搜索条件过多！',2,function(){
			if(type=='lthy'){
				$("#r"+id).attr("checked",false);
				$(".one"+id).attr("checked",false);
				$(".one"+id).attr('disabled',false);
			}else if(type=='ltjob'){
				$("#j"+id).attr("checked",false);
				$(".jobone"+id).attr("checked",false);
				$(".jobone"+id).attr('disabled',false);
			}
		}); 	
	}*/
	var r_length = $(".xz").length;
	if(r_length > 5) {
		layermsg('您最多只能选择五个！', 2, function() {
			$("#r" + id).attr("checked", false);
			$("#r" + id).removeClass('xz');
		})
	}
	var j_length = $(".xzj").length;
	if(j_length > 5) {
		layermsg('您最多只能选择五个！', 2, function() {
			$("#j" + id).attr("checked", false);
			$("#j" + id).removeClass('xzj');
		})
	}
}

$(function() {
	var ltjobsubmit = document.getElementById('ltjobsubmit');
	if(ltjobsubmit) {
		ltjobsubmit.addEventListener('tap', function(event) {
			var job_name = $.trim($("#job_name").val()),
				jobone = $.trim($("#jobone").val()),
				jobtwo = $.trim($("#jobtwo").val()),
				provinceid = $.trim($("#provinceid").val()),
				cityid = $.trim($("#cityid").val()),
				three_cityid = $.trim($("#three_cityid").val()),
				minsalary = $.trim($("#minsalary").val()),
				maxsalary = $.trim($("#maxsalary").val()),
				job_desc = $.trim($("#contenttext").val()),
				eligible = $.trim($("#eligibletext").val()),
				department = $.trim($("#department").val()),
				exp = $.trim($("#exp").val()),
				report = $.trim($("#report").val()),
				age = $.trim($("#age").val()),
				sex = $.trim($("#sex").val()),
				edu = $.trim($("#edu").val()),
				language = $.trim($("#lang").val()),
				constitute = $.trim($("#constitute").val()),
				welfare = $.trim($("#welfare").val()),
				rebates = $.trim($("#rebates").val()),
				other = $.trim($("#othertext").val()),
				com_name = $.trim($("#com_name").val()),
				pr = $.trim($("#pr").val()),
				hy = $.trim($("#hy").val()),
				mun = $.trim($("#mun").val()),
				desc = $.trim($("#desctext").val()),
				id = $.trim($("#id").val());

			if($.trim($("#job_name").val()) == "") {
				return mui.toast("请输入职位名称");

			}
			if($.trim($("#jobtwo").val()) == "") {
				return mui.toast("请选择职位分类");

			}
			if($.trim($("#cityid").val()) == "") {
				return mui.toast("请选择工作地点");

			}
			var job_desc = $.trim($("#contenttext").val());
			if(job_desc == "") {
				return mui.toast("请输入任职描述");

			}
			var eligible = $.trim($("#eligibletext").val());
			if(eligible == "") {
				return mui.toast("请输入任职资格");

			}
			if($.trim($("#department").val()) == "") {
				return mui.toast("请输入所属部门");

			}
			if($.trim($("#report").val()) == "") {
				return mui.toast("请输入汇报对象");

			}
			var min = $.trim($("#minsalary").val());
			var max = $.trim($("#maxsalary").val());

			if(min == "" || min == "0") {
				return mui.toast("请填写职位年薪");

			}
			if(max && parseInt(max) <= parseInt(min)) {
				return mui.toast("最高年薪必须大于最低年薪");

			}
			var constitute = [];
			$('input[name="xzgc"]:checked').each(function() {
				constitute.push($(this).val());
			});
			if(constitute.length == 0) {
				return mui.toast("请选择薪资构成！", 2);

			}
			var welfare = [];
			$('input[name="fldy"]:checked').each(function() {
				welfare.push($(this).val());
			});

			if($.trim($("#age").val()) < 1) {
				return mui.toast("请选择年龄要求");

			}
			if($.trim($("#sex").val()) < 1) {
				return mui.toast("请选择性别要求");

			}
			if($.trim($("#exp").val()) < 1) {
				return mui.toast("请选择工作经验");

			}

			if($.trim($("#edu").val()) < 1) {
				return mui.toast("请选择学历要求");
			}
			var language = [];
			$('input[name="yyyq"]:checked').each(function() {
				language.push($(this).val());
			});
			if($.trim($("#com_name").val()) == "") {
				return mui.toast("请输入公司名称");
			}
			if($.trim($("#pr").val()) < 1) {
				return mui.toast("请选择公司性质");
				return false;
			}
			if($.trim($("#hy").val()) < 1) {
				return mui.toast("请选择所属行业");
				return false;
			}
			if($.trim($("#mun").val()) < 1) {
				return mui.toast("请选择公司规模");
			}
			if($.trim($("#desctext").val()) == "") {
				return mui.toast("请输入公司介绍");
			}

			var sql = {
				job_name: job_name,
				jobone: jobone,
				jobtwo: jobtwo,
				provinceid: provinceid,
				cityid: cityid,
				three_cityid: three_cityid,
				minsalary: minsalary,
				maxsalary: maxsalary,
				department: department,
				job_desc: job_desc,
				constitute: constitute,
				welfare: welfare,
				exp: exp,
				report: report,
				age: age,
				sex: sex,
				edu: edu,
				eligible: eligible,
				rebates: rebates,
				other: other,
				language: language,
				com_name: com_name,
				pr: pr,
				hy: hy,
				mun: mun,
				desc: desc,
				id: id,
				submit: 'submit'
			}

			document.getElementById('ltjobsubmit').value = "提交中...";
			document.getElementById('ltjobsubmit').id = "submit";
			mui.post('index.php?c=jobadd', sql, function(data) {
				layermsg(data.msg, 2, function() {
					location.href = data.url;
				});
			}, 'json');
		})
	}

})

//发私信
function onmsg(fid, uid) {
	if(fid == uid) {
		layermsg('不可以给自己发私信！');
	} else {
		$("#fid").val(fid);
		replyhtml = $("#reply").html();
		$("#reply").html('');
		layer.open({
			type: 1,
			title: '发私信',
			offset: [($(window).height() - 192) / 2 + 'px', ''],
			closeBtn: [0, true],
			border: [10, 0.3, '#000', true],
			area: ['330px', '192px'],
			content: replyhtml,
			cancel: function() {
				$("#reply").html(replyhtml);
			}
		});
	}
}

function send_ltmsg() {
	var fid = $("#fid").val();
	var content = $.trim($("#content").val());
	if(content == "") {
		layermsg('内容不能为空！');
		return false;
	}
	layer_load('执行中，请稍候...', 0);
	$.post(wapurl + "index.php?c=ltjob&a=send_ltmsg", {
		content: content,
		fid: fid
	}, function(data) {
		layer.closeAll();
		if(data == '-1') {
			layermsg('请先登录！');
			return false;
		} else if(data > '1') {
			layermsg('发私信成功！');
			return false;
		} else {
			layermsg('发私信失败！');
			return false;
		}
	})
}
//委托简历
function entrust(uid, name) {
	layer_load('执行中，请稍候...', 0);
	$.post(wapurl + "index.php?c=ajax&a=entrust", {
		uid: uid,
		name: name
	}, function(data) {
		layer.closeAll();
		if(data == 1) {
			layermsg('您不是个人用户！');
			return false;
		} else if(data == 2) {
			layermsg('您已经委托过简历给该猎头！');
			return false;
		} else if(data == 3) {
			layermsg('委托简历成功！');
			return false;
		} else if(data == 4) {
			
			layer.open({
				content:'先完善简历，成为高级简历以后才可以申请猎头帮您找到合适的工作！',
				btn: ['确认', '取消'],
				shadeClose: false,
				yes: function(){
					window.location.href=wapurl+"member/index.php?c=resume";
				} 
			});
 			return false;
			
		}  
	})
}