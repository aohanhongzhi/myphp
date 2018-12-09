function checkinfo() {
	var name = document.getElementById('name').value,
		sex = document.getElementById('sex').value,
		birthday = document.getElementById('birthday').value,
		edu = document.getElementById('edu').value,
		exp = document.getElementById('exp').value,
		living = document.getElementById('living').value,
		telphone = document.getElementById('telphone').value,
		email = document.getElementById('email').value,
		address = document.getElementById('address').value,
		height = document.getElementById('height').value,
		weight = document.getElementById('weight').value,
		nationality = document.getElementById('nationality').value,
		marriage = document.getElementById('marriage').value,
		domicile = document.getElementById('domicile').value,
		qq = document.getElementById('qq').value,
		preview = document.getElementById('preview').value,
		homepage = document.getElementById('homepage').value;

	if(name == '') {
		return mui.toast('请填写姓名！');
	}
	if(sex == '') {
		return mui.toast('请选择性别！');
	}
	if(birthday == '') {
		return mui.toast('请选择出生日期！');
	}
	if(edu == '') {
		return mui.toast('请选择最高学历！');
	}
	if(exp == '') {
		return mui.toast('请选择工作经验！');
	}
	if(living == '') {
		return mui.toast('请填写现居住地！');
	}
	if(telphone == '') {
		return mui.toast('请填写手机！');
	}
	

	formData.append('name', name);
	formData.append('sex', sex);
	formData.append('birthday', birthday);
	formData.append('edu', edu);
	formData.append('exp', exp);
	formData.append('living', living);
	formData.append('telphone', telphone);
	formData.append('email', email);
	formData.append('address', address);
	formData.append('height', height);
	formData.append('weight', weight);
	formData.append('nationality', nationality);
	formData.append('marriage', marriage);
	formData.append('domicile', domicile);
	formData.append('qq', qq);
	formData.append('preview', preview);
	formData.append('homepage', homepage);
	formData.append('submit', 'submit');

	$.ajax({
		url: "index.php?c=info",
		type: 'post',
		data: formData,
		contentType: false,
		processData: false,
		dataType: 'json',
		success: function(res) {
			var res = JSON.stringify(res);
			var data = JSON.parse(res);
			if(data.url) {
				layermsg(data.msg, 2, function() {
					location.href = data.url;
				});
			} else {
				layermsg(data.msg, 2);
				return false;
			}
		}
	})
}

function kresume() {
	var table = document.getElementById('table').value,
		eid = document.getElementById('eid').value,
		id = document.getElementById('id').value;

	if(table == 'work') {
		var name = document.getElementById('name').value,
			title = document.getElementById('title').value,
			sdate = document.getElementById('sdate').value,
			edate = document.getElementById('edate').value,
			totoday = document.getElementById('totoday').value,
			content = document.getElementById('content').value;
		if(name == '') {
			return mui.toast('请填写单位名称！');
		}
		if(sdate == '') {
			return mui.toast('请选择入职时间！');
		} else if(edate) {
			if(sdate > edate) {
				return mui.toast('请确认日期先后顺序！');
			}
		}
		if(edate == '' && totoday != 1) {
			return mui.toast('请选择离职时间！');
		}
		var arr = {
			name: name,
			title: title,
			sdate: sdate,
			edate: edate,
			totoday: totoday,
			table: table,
			eid: eid,
			id: id,
			content: content,
			submit: 'submit'
		}
	} else if(table == 'edu') {
		var name = document.getElementById('name').value,
			title = document.getElementById('title').value,
			sdate = document.getElementById('sdate').value,
			edate = document.getElementById('edate').value,
			education = document.getElementById('education').value,
			specialty = document.getElementById('specialty').value;
		if(name == '') {
			return mui.toast('请填写学校名称！');
		}
		if(sdate == '' || edate == '') {
			return mui.toast('请正确填写在校时间！');
		}
		if(sdate > edate) {
			return mui.toast('入校时间不能大于离校时间！');
		}
		var arr = {
			name: name,
			title: title,
			sdate: sdate,
			edate: edate,
			table: table,
			eid: eid,
			id: id,
			education: education,
			specialty: specialty,
			submit: 'submit'
		}
	} else if(table == 'project') {
		var name = document.getElementById('name').value,
			title = document.getElementById('title').value,
			sdate = document.getElementById('sdate').value,
			edate = document.getElementById('edate').value,
			content = document.getElementById('content').value;
		if(name == '') {
			return mui.toast('请填写项目名称！');
		}
		if(sdate == '' || edate == '') {
			return mui.toast('请正确填写项目时间！');
		}
		if(sdate > edate) {
			return mui.toast('开始时间不能大于结束时间！');
		}
		var arr = {
			name: name,
			title: title,
			sdate: sdate,
			edate: edate,
			table: table,
			eid: eid,
			id: id,
			content: content,
			submit: 'submit'
		}
	} else if(table == 'training') {
		var name = document.getElementById('name').value,
			title = document.getElementById('title').value,
			sdate = document.getElementById('sdate').value,
			edate = document.getElementById('edate').value,
			content = document.getElementById('content').value;
		if(name == '') {
			return mui.toast('请填写培训中心！');
		}
		if(sdate == '' || edate == '') {
			return mui.toast('请正确填写培训时间！');
		}
		if(sdate > edate) {
			return mui.toast('开始时间不能大于结束时间！');
		}
		var arr = {
			name: name,
			title: title,
			sdate: sdate,
			edate: edate,
			table: table,
			eid: eid,
			id: id,
			content: content,
			submit: 'submit'
		}
	} else if(table == 'skill') {
		var name = document.getElementById('name').value,
			longtime = document.getElementById('longtime').value,
			preview = document.getElementById('preview').value;
		if(name == '') {
			return mui.toast('请填写技能名称！');
		}
		if(longtime == '') {
			return mui.toast('请填写掌握时间！');
		}

		formData.append('name', name);
		formData.append('longtime', longtime);
		formData.append('table', table);
		formData.append('preview', preview);
		formData.append('eid', eid);
		formData.append('id', id);
		formData.append('submit', submit);
		
	} else if(table == 'show') {
		var title = document.getElementById('title').value,
			sort = document.getElementById('sort').value,
 			id = document.getElementById('id').value,
		 	preview = document.getElementById('preview').value;
		
		if(title == '') {
			return mui.toast('作品标题不能为空！');
		}
		if(sort == '') {
			return mui.toast('作品排序不能为空！');
		}
		if(preview == '' && id == '') {
			return mui.toast('请上传作品！');
		}

		formData.append('title', title);
		formData.append('sort', sort);
		formData.append('table', table);
		formData.append('eid', eid);
		formData.append('preview', preview);
		formData.append('id', id);
		formData.append('submit', submit);
	} else if(table == 'other') {
		var name = document.getElementById('name').value,
			content = document.getElementById('content').value;
		if(name == '') {
			return mui.toast('请填写其他标题！');
		}
		if(content == '') {
			return mui.toast('请填写其他描述！');
		}
		var arr = {
			name: name,
			table: table,
			eid: eid,
			id: id,
			content: content,
			submit: 'submit'
		}
	} else if(table == 'resume') {
		var description = document.getElementById('description').value;
		var alltag = '';
		var num = 0;
		$('.tag').each(function(w, tag) {
			if(tag.checked == true) {
				alltag = alltag + tag.dataset.name + ',';
				num++;
			}
		});
		if(num > 5) {
			return mui.toast('最多只能选择五项！');
		}
		if(description == '') {
			return mui.toast('请填写自我评价！');
		}
		var arr = {
			description: description,
			table: table,
			eid: eid,
			id: id,
			tag: alltag,
			submit: 'submit'
		}
	} else if(table == 'doc') {
		var doc = UE.getEditor('doc').getContent();
		if(doc == '') {
			return mui.toast('请填写黏贴简历内容！');
		}
		var arr = {
			doc: doc,
			table: table,
			eid: eid,
			id: id,
			submit: 'submit'
		}
	}

	if(table == "skill" || table == "show") {
		$.ajax({
			url: "index.php?c=saveresumeson",
			type: 'post',
			data: formData,
			contentType: false,
			processData: false,
			dataType: 'json',
			success: function(res) {
				var res = JSON.stringify(res);
				var data = JSON.parse(res);
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = data.url;
					});
				} else {
					layermsg(data.msg, 2);
					return false;
				}
			}
		})
	} else {
		mui.post('index.php?c=saveresumeson', arr, function(data) {
			if(data.url) {
				mui.openWindow({
					url: data.url,
				});
			} else {
				layermsg(data.msg);
			}
		}, 'json');
	}
}

function saveexpect() {
	var name = document.getElementById('name'),
		job_classid = document.getElementById('job_classid'),
		hy = document.getElementById('hy'),
		city_classid = document.getElementById('city_classid'),
		//		provinceid = document.getElementById('provinceid'),
		//		cityid = document.getElementById('cityid'),
		//		three_cityid = document.getElementById('three_cityid'),
		type = document.getElementById('type'),
		report = document.getElementById('report'),
		minsalary = document.getElementById('minsalary'),
		maxsalary = document.getElementById('maxsalary'),
		eid = document.getElementById('eid'),
		jobstatus = document.getElementById('jobstatus');

	if(name.value == '') {
		return mui.toast('请填写期望岗位！');
	}
	if(job_classid.value == '') {
		return mui.toast('请选择工作职能！');
	}
	if(hy.value == '') {
		return mui.toast('请选择从事行业！');
	}
	if(city_classid.value == '') {
		return mui.toast('请选择期望城市！');
	}
	if(type.value == '') {
		return mui.toast('请选择工作性质');
	}
	if(report.value == '') {
		return mui.toast('请选择到岗时间！');
	}
	if(jobstatus.value == '' && linkphone.value == '') {
		return mui.toast('请选择求职状态！');
	}
	mui.post('index.php?c=expect', {
		name: name.value,
		job_classid: job_classid.value,
		hy: hy.value,
		city_classid: city_classid.value,
		//		provinceid: provinceid.value,
		//		cityid: cityid.value,
		//		three_cityid: three_cityid.value,
		type: type.value,
		report: report.value,
		jobstatus: jobstatus.value,
		minsalary: minsalary.value,
		maxsalary: maxsalary.value,
		eid: eid.value,
		submit: 'submit'
	}, function(data) {
		if(data > 0) {
			mui.openWindow({
				url: wapurl + "member/index.php?c=resume&eid=" + eid.value,
			});

		} else {
			return mui.toast('保存失败！');
		}
	}, 'json');
}

function addresume() {
	var name = document.getElementById('name'),
		hy = document.getElementById('hy'),
		job_classid = document.getElementById('job_classid'),
		city_classid = document.getElementById('city_classid'),
		//		provinceid = document.getElementById('provinceid'),
		//		cityid = document.getElementById('cityid'),
		//		three_cityid = document.getElementById('three_cityid'),
		minsalary = document.getElementById('minsalary'),
		maxsalary = document.getElementById('maxsalary'),
		report = document.getElementById('report'),
		type = document.getElementById('type'),
		jobstatus = document.getElementById('jobstatus'),
		uname = document.getElementById('uname'),
		sex = document.getElementById('sex'),
		birthday = document.getElementById('birthday'),
		edu = document.getElementById('edu'),
		exp = document.getElementById('exp'),
		telphone = document.getElementById('telphone'),
		email = document.getElementById('email'),
		living = document.getElementById('living');
	if(uname.value == "") {
		return mui.toast('请填写真实姓名！');
	}
	if(sex.value == '') {
		return mui.toast('请选择性别！');
	}
	if(birthday.value == '') {
		return mui.toast('请选择出生年月！');
	}
	if(edu.value == '') {
		return mui.toast('请选择最高学历！');
	}
	if(exp.value == '') {
		return mui.toast('请选择工作经验！');
	}
	if(telphone.value == '') {
		return mui.toast('请填写手机号码！');
	} else {
		var reg = /^[1][3456789]\d{9}$/; //验证手机号码  
		if(!reg.test(telphone.value)) {
			return mui.toast('手机号码格式错误！');
		}
	}
	var myreg = /^([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9\-]+@([a-zA-Z0-9\-]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	if(email.value != "" && !myreg.test(email.value)) {
		return mui.toast('邮箱格式错误！');
	}
	if(living.value == '') {
		return mui.toast('请选择现居住地！');
	}
	if(name.value == "") {
		return mui.toast('请填写期望岗位！');
	}
	if(hy.value == "") {
		return mui.toast('请选择从事行业！');
	}
	if(job_classid.value == "") {
		return mui.toast('请选择期望职位！');
	}
	if(minsalary.value == "") {
		return mui.toast('请填写期望薪资！');
	}
	if(parseInt(maxsalary.value)>0) {
		if(parseInt(maxsalary.value) <= parseInt(minsalary.value)) {
			return mui.toast('最高薪资必须大于最低薪资！');
		}

	}
	if(city_classid.value == "") {
		return mui.toast('请选择期望城市！');
	}
	if(type.value == "") {
		return mui.toast('请选择工作性质！');
	}
	if(report.value == "") {
		return mui.toast('请选择到岗时间！');
	}
	if(jobstatus.value == "") {
		return mui.toast('请选择求职状态！');
	}
	var arr = {};

	arr = {
		name: name.value,
		hy: hy.value,
		job_classid: job_classid.value,
		city_classid: city_classid.value,
		//		provinceid: provinceid.value,
		//		cityid: cityid.value,
		//		three_cityid: three_cityid.value,
		minsalary: minsalary.value,
		maxsalary: maxsalary.value,
		report: report.value,
		type: type.value,
		jobstatus: jobstatus.value,
		uname: uname.value,
		sex: sex.value,
		birthday: birthday.value,
		edu: edu.value,
		exp: exp.value,
		email: email.value,
		telphone: telphone.value,
		living: living.value
	};

	if(document.getElementById('resume_exp').value == '1') {
		var workname = document.getElementById('workname'),
			worksdate = document.getElementById('worksdate'),
			workedate = document.getElementById('workedate'),
			worktitle = document.getElementById('worktitle'),
			totoday = document.getElementById('totoday'),
			workcontent = document.getElementById('workcontent');
		if(workname.value == '') {
			return mui.toast('请填写公司名称！');
		}
		if(worktitle.value == '') {
			return mui.toast('请填写担任职务！');
		}
		if(worksdate.value == '') {
			return mui.toast('请填写入职时间！');

		}
		arr.workname = workname.value;
		arr.worksdate = worksdate.value;
		arr.workedate = workedate.value;
		arr.worktitle = worktitle.value;
		arr.totoday = totoday.value;
		arr.workcontent = workcontent.value;
	}
	if(document.getElementById('resume_edu').value == '1') {
		var eduname = document.getElementById('eduname'),
			edusdate = document.getElementById('edusdate'),
			eduedate = document.getElementById('eduedate'),
			education = document.getElementById('education'),
			eduspec = document.getElementById('eduspec');
		if(eduname.value == '') {
			return mui.toast('请填写学校名称！');
		}
		if(edusdate.value == '') {
			return mui.toast('请填写入学时间！');
		}
		if(eduedate.value == '') {
			return mui.toast('请填写离校或预计离校时间！');
		}
		if(eduspec.value == '') {
			return mui.toast('请填写所学专业！');
		}
		if(education.value == '') {
			return mui.toast('请选择毕业学历！');
		}
		arr.eduname = eduname.value;
		arr.edusdate = edusdate.value;
		arr.eduedate = eduedate.value;
		arr.eduspec = eduspec.value;
		arr.education = education.value;
	}
	if(document.getElementById('resume_pro').value == '1') {
		var proname = document.getElementById('proname'),
			prosdate = document.getElementById('prosdate'),
			proedate = document.getElementById('proedate'),
			protitle = document.getElementById('protitle'),
			procontent = document.getElementById('procontent');
		if(proname.value == '') {
			return mui.toast('请填写项目名称！');
		}
		if(protitle.value == '') {
			return mui.toast('请填写项目担任职务！');
		}
		if(prosdate.value == '') {
			return mui.toast('请填写项目开始时间！');
		}
		if(proedate.value == '') {
			return mui.toast('请填写项目结束时间！');
		}
		arr.proname = proname.value;
		arr.prosdate = prosdate.value;
		arr.proedate = proedate.value;
		arr.protitle = protitle.value;
		arr.procontent = procontent.value;
	}
	arr.submit = 'submit';
    layer_load('执行中，请稍候...');
	mui.post('index.php?c=kresume', arr, function(data) {
		layer.closeAll();
		if(data.url) {
			layermsg(data.msg, 2, function() {
				window.location.href = data.url;
			});
		} else {
			return mui.toast(data.msg);
		}
	}, 'json');
}

function app_height_status(id) {
	$("#wname .sq_gjresume_bth").html("<a class=\"sq_gjresume_bth_a\" href=\"javascript:void(0);\" onclick=\"layer_del('','index.php?c=resumeset&height=" + id + "');\">申请高级简历</a>");
	wnamehtml = $("#wname").html();
	$("#wname").html('');
	layer.open({
		type: 1,
		title: '申请高级简历',
		closeBtn: [0, true],
		border: [10, 0.3, '#000', true],
		area: ['300px', 'auto'],
		content: wnamehtml,
		cannel: $("#wname").html(wnamehtml)
	});
}

function entr_resume(id) {
	layer.closeAll();

	$("#entr_resume .job_box_botton").html("<a class=\"job_box_yes job_box_botton2\" href=\"javascript:void(0);\" onclick=\"entrust('','" + id + "');\">委托</a>");
	layer.open({
		type: 1,
		title: '委托简历',
		closeBtn: [0, true],
		border: [10, 0.3, '#000', true],
		area: ['300px', '200px'],
		content: $("#entr_resume").html()
	});
}

function entr_resume_free(id) {
	$.post(wapurl + "/member/index.php?c=canceltrust", {
		id: id
	}, function(data) {
		var data = eval('(' + data + ')');
		if(data.url) {
			layermsg(data.msg, Number(data.tm), function() {
				location.reload();
			});
			return false;
		} else {
			layermsg(data.msg, Number(data.tm), function() {
				location.href = data.url;
			});
			return false;
		}
	});
}

function entrust(msg, id) {

	if(msg) {
		layer.open({
			content: msg,
			btn: ['确认', '取消'],
			shadeClose: false,
			yes: function() {
				layer.closeAll();
				layer_load('执行中，请稍候...');
				$.post(wapurl + "/member/index.php?c=canceltrust", {
					id: id
				}, function(data) {
					layer.closeAll();
					var data = eval('(' + data + ')');
					if(data.url == '1') {
						layermsg(data.msg, Number(data.tm), function() {
							location.reload();
						});
						return false;
					} else {
						layermsg(data.msg, Number(data.tm), function() {
							location.href = data.url;
						});
						return false;
					}
				});
			}
		});
	} else {
		$.post(wapurl + "/member/index.php?c=canceltrust", {
			id: id
		}, function(data) {
			var data = eval('(' + data + ')');
			if(data.url) {
				layermsg(data.msg, Number(data.tm), function() {
					location.reload();
				});
				return false;
			} else {
				layermsg(data.msg, Number(data.tm), function() {
					location.href = data.url;
				});
				return false;
			}
		});
	}
}