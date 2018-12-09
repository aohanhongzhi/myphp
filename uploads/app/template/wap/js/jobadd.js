function check_email(strEmail) {
	var emailReg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/;
	if(emailReg.test(strEmail))
		return true;
	else
		return false;
}

function checkjob(id, type) {
	if(id > 0) {
		$.post(wapurl + "?c=ajax&a=wap_job", {
			id: id,
			type: type
		}, function(data) {
			if(type == 1) {
				$("#job1_son").html(data);
			} else {
				$("#job_post").html(data);
			}
		})
	} else {
		if(type == 1) {
			$("#job1_son").html('<option value="">请选择</option>');
		}
	}
	$("#job_post").html('<option value="">请选择</option>');
}

function checkcity(id, type) {
	if(id > 0) {
		$.post(wapurl + "?c=ajax&a=wap_city", {
			id: id,
			type: type
		}, function(data) {
			if(type == 1) {
				$("#cityid").html(data);
				$("#cityshowth").hide();
			} else {
				if(data) {
					$("#cityshowth").attr('style', 'width:31%;');
					$("#three_cityid").html(data);
				}
			}
		})
	} else {
		if(type == 1) {
			$("#cityid").html('<option value="">请选择</option>');
			$("#cityshowth").hide();
		}
	}
	$("#three_cityid").html('<option value="">请选择</option>');
}
$(function() {
	mui('.tblink').each(function() {
		this.addEventListener('toggle', function(event) {
			var active = event.detail.isActive ? 1 : 2;
			$("#tblink").val(active);
		});
	});
	mui('.is_graduate').each(function() {
		this.addEventListener('toggle', function(event) {
			var active = event.detail.isActive ? 1 : 0;
			$("#is_graduate").val(active);
		});
	});
	mui('.salary_type').each(function() {
		this.addEventListener('toggle', function(event) {
			var active = event.detail.isActive ? 1 : 2;
			var salarytype = document.getElementById('salary_type'),
				minsalary = document.getElementById('minsalary'),
				maxsalary = document.getElementById('maxsalary');
			if(active == 1) {
				minsalary.value = "";
				minsalary.disabled = true;
				maxsalary.value = "";
				maxsalary.disabled = true;
			} else if(active == 2) {
				minsalary.disabled = false;
				maxsalary.disabled = false;
			}
			salarytype.value = active;
		});
	});
	//是否面议
	if(typeof salaryData != "undefined") {
		var salaryPicker = new $.PopPicker();
		salaryPicker.setData(salaryData);
		var salaryPickerBtn = doc.getElementById('salaryPicker');
		var salary = doc.getElementById('xztype');
		var minsalary = document.getElementById('minxz');
		var maxsalary = document.getElementById('maxxz');
		salaryPickerBtn.addEventListener('tap', function(event) {
			salaryPicker.show(function(items) {
				salary.value = items[0].value;
				salaryPickerBtn.innerText = items[0].text;
				if(salary.value == 1) {
					minsalary.value = "";
					minsalary.disabled = true;
					maxsalary.value = "";
					maxsalary.disabled = true;
				} else if(salary.value == 0) {
					minsalary.disabled = false;
					maxsalary.disabled = false;
				}
			});
		}, false);
	}
	mui('.yunset_bth_box').on('tap', '.addnext',  function() {
		var name = $.trim($("#name").val()),
			job1_son = $.trim($("#job1_son").val()),
			cityid = $.trim($("#cityid").val()),
			minsalary = $.trim($("#minsalary").val()),
			maxsalary = $.trim($("#maxsalary").val()),
			salary_type = $.trim($("#salary_type").val()),
			description = $.trim($("#contenttext").val()),
			islink = $.trim($("#islink").val()),
			link_moblie = $.trim($("#link_moblie").val()),
			link_man = $.trim($("#link_man").val()),
			isemail = $.trim($("#isemail").val()),
			email = $.trim($("#email").val());
		if(name == "") {
			 mui.toast('请填写职位名称');return false;
		} else if(job1_son == "") {
			 mui.toast('请选择职位类别');return false;
		} else if(cityid == "") {
			 mui.toast('请选择工作地点');return false;
		}
		if(salary_type != 1) {
			if(minsalary == "" || minsalary == "0") {
				 mui.toast('请填写薪资待遇');return false;
			}
			if(maxsalary > 0) {
				if(parseInt(maxsalary) <= parseInt(minsalary)) {
					 mui.toast('最高工资必须大于最低工资');return false;
				}
			}
		}
		if(description == "") {
			 mui.toast('请填写职位描述');return false;
		}
		if(islink == 2) {
			if(link_man == '' || link_moblie == '') {
				 mui.toast('联系人及联系电话均不能为空');return false;
			}
			if(link_moblie && (isjsMobile(link_moblie) == false && !isjsTell(link_moblie))) {
				 mui.toast('联系电话格式错误');return false;
			}
		}
		if(isemail == '2') {
			if(email == '') {
				 mui.toast('请输入邮箱');return false;
			} else if(check_email(email) == false) {
				 mui.toast('新邮箱格式错误');return false;
			}
		}
	})
	var jobsubmit = document.getElementById('jobsubmit');
	if(jobsubmit) {
		jobsubmit.addEventListener('tap', function(event) {
			var name = $.trim($("#name").val()),
				job1 = $.trim($("#job1").val()),
				job1_son = $.trim($("#job1_son").val()),
				job_post = $.trim($("#job_post").val()),
				provinceid = $.trim($("#provinceid").val()),
				cityid = $.trim($("#cityid").val()),
				three_cityid = $.trim($("#three_cityid").val()),
				minsalary = $.trim($("#minsalary").val()),
				maxsalary = $.trim($("#maxsalary").val()),
				salary_type = $.trim($("#salary_type").val()),
				description = $.trim($("#contenttext").val()),
				islink = $.trim($("#islink").val()),
				link_moblie = $.trim($("#link_moblie").val()),
				link_man = $.trim($("#link_man").val()),
				tblink = $.trim($("#tblink").val()),
				hy = $.trim($("#hy").val()),
				number = $.trim($("#number").val()),
				exp = $.trim($("#exp").val()),
				report = $.trim($("#report").val()),
				age = $.trim($("#age").val()),
				sex = $.trim($("#sex").val()),
				edu = $.trim($("#edu").val()),
				is_graduate = $.trim($("#is_graduate").val()),
				marriage = $.trim($("#marriage").val()),
				lang = $.trim($("#lang").val()),
				isemail = $.trim($("#isemail").val()),
				email = $.trim($("#email").val()),
				id = $.trim($("#id").val()),
				state = $.trim($("#state").val());

			if(name == "") {
				return mui.toast('请填写职位名称');
			} else if(job1_son == "") {
				return mui.toast('请选择职位类别');
			} else if(cityid == "") {
				return mui.toast('请选择工作地点');
			}
			if(salary_type != 1) {
				if(minsalary == "" || minsalary == "0") {
					return mui.toast('请填写薪资待遇');
				}
				if(maxsalary > 0) {
					if(parseInt(maxsalary) <= parseInt(minsalary)) {
						return mui.toast('最高工资必须大于最低工资');
					}
				}
			}
			if(description == "") {
				return mui.toast('请填写职位描述');
			}
			if(islink == 2) {
				if(link_man == '' || link_moblie == '') {
					return mui.toast('联系人及联系电话均不能为空');
				}
				if(link_moblie && (isjsMobile(link_moblie) == false && !isjsTell(link_moblie))) {
					return mui.toast('联系电话格式错误');
				}
			}
			if(isemail == '2') {
				if(email == '') {
					return mui.toast('请输入邮箱');
				} else if(check_email(email) == false) {
					return mui.toast('新邮箱格式错误');
				}
			}
			layer_load('执行中，请稍候...');
			mui.post('index.php?c=jobadd', {
				name: name,
				job1: job1,
				job1_son: job1_son,
				job_post: job_post,
				provinceid: provinceid,
				cityid: cityid,
				three_cityid: three_cityid,
				minsalary: minsalary,
				maxsalary: maxsalary,
				salary_type: salary_type,
				description: description,
				islink: islink,
				link_moblie: link_moblie,
				link_man: link_man,
				tblink: tblink,
				hy: hy,
				number: number,
				exp: exp,
				report: report,
				age: age,
				sex: sex,
				edu: edu,
				is_graduate: is_graduate,
				marriage: marriage,
				lang: lang,
				isemail: isemail,
				email: email,
				id: id,
				state: state,
				submit: 'submit'
			}, function(data) {
				layer.closeAll();
				if(data.url_tg) {
					location.href = wapurl + 'member/' + data.url_tg;
				} else {
					layermsg(data.msg, 2, function() {
						location.href = data.url;
					});
				}
			}, 'json');
		})
	}

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
			if($.trim($("#edu").val()) < 1) {
				return mui.toast("请选择学历要求");

			}

			if($.trim($("#exp").val()) < 1) {
				return mui.toast("请选择工作经验");
			}
			var language = [];
			$('input[name="yyyq"]:checked').each(function() {
				language.push($(this).val());
			});
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
				id: id,
				submit: 'submit'
			}
			mui.post('index.php?c=lt_jobadd', sql, function(data) {
				layermsg(data.msg, 2, function() {
					location.href = data.url;
				});
			}, 'json');
		})
	}
})