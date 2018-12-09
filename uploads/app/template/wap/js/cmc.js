//职位多选---------------------------------------------------------------------------------------------------------------开始----------------------
var jobchoose = document.getElementById("jobchoose");
var jobone = document.getElementById("jobone");
var jobtwo = document.getElementById("jobtwo");
var jobthree = document.getElementById("jobthree");
var jobhtml = '';
var jobhtmltwo = '';
var jobhtmlthree = '';
if(typeof jobclass == "undefined") {
	var jobclass = '';
}
if(typeof jobclassname == "undefined") {
	var jobclassname = '';
}

//点击一级类别
mui("#jobone").on('tap', 'li', function() {
	$(".yun_category_on").removeClass('yun_category_on');
	this.classList.add('yun_category_on');
	var jobid = this.getAttribute('data-id');
	$(".jobtwo").addClass('none');
	$(".job" + jobid).removeClass('none');
	$(".jobthree").addClass('none');
	var twostyle = $("#jobtwo").attr("style");
	if(!twostyle){
		$("#jobtwo").css("left", "30.48%");
	}
	$("#jobthree").removeAttr("style");
});
//点击二级类别
mui("#jobtwo").on('tap', 'li', function() {
	$(".yun_category_ons").removeClass('yun_category_ons');
	this.classList.add('yun_category_ons');
	var jobid = this.getAttribute('data-id');
	$(".jobthree").addClass('none');
	$(".job" + jobid).removeClass('none');
	$("#jobthree").css("left", "56.96%");
});
//删除已选类别
mui("#jobchoosed").on('tap', 'a', function() {
	var id = this.getAttribute('data-id');
	var choosetwo = document.getElementById('jobcheckAll' + id);
	if(choosetwo) {
		choosetwo.checked = false;
		var listBox = mui('.jobcheck' + id);
		listBox.each(function() {
			var ele = this;
			ele.checked = false;
			ele.disabled = false;
		});
	} else {
		document.getElementById('jobthree' + id).checked = false;
	}
	document.getElementById("jobchoosed").removeChild(this);
	//处理jobclass和jobclassname，减少内容
	var list = arrsplice(jobclass, id);
	var jobnamelist = [];
	for(var i = 0; i < list.length; i++) {
		jobnamelist.push(jn[list[i]]);
	}
	if(list.length > 0) {
		document.getElementById('jobpencent').classList.remove('none');
		document.getElementById('jobpencent').innerHTML = list.length + '/5';
	} else {
		document.getElementById('jobpencent').classList.add('none');
		document.getElementById('jobpencent').innerHTML = '';
	}
	jobclass = list.join(',');
	jobclassname = jobnamelist.join('+');
	document.getElementById("job_classid").value = jobclass;
	document.getElementById("jobnameshow").innerHTML = jobclassname;
});
(function(m) {
	$('#jobthree .checkAll').each(function(i, jobtwo) {
		//根据获取到的已选数据，处理类别选中
		if(typeof jobclassidData != "undefined") {
			$.each(jobclassidData, function(index, vaule, arr) {
				if(jobtwo.value == vaule.value) {
					jobtwo.checked = true;
					m('.jobcheck' + jobtwo.value).each(function() {
						var le = this;
						le.checked = true;
						le.disabled = true;
					})
				}
			})
		}
		//选中三级全部处理
		document.getElementById(jobtwo.id).addEventListener('change', function() {
			var jobtwolist = jobclass.split(',');
			var list = [];
			for(var job in jobtwolist) {
				if(jobtwolist[job])
					list.push(jobtwolist[job]);
			}
			if(list.length > 4 && this.checked == true) {
				this.checked = false;
				return mui.toast("最多只能选择5个类别哦");
			}
			var listBox = m('.jobcheck' + this.value);
			if(this.checked) {
				//选中处理下方已选显示
				var checked = [],
					newchoosed = '<a class="grade_chlose_box_a" data-id="' + this.value + '">' + jn[this.value] + '</a>';
				$("#jobchoosed").prepend(newchoosed);
				//选中全部则该类下所有三级都设为已选中和不可选状态
				listBox.each(function() {
					var ele = this;
					if(ele.checked == true) {
						checked.push(ele.value);
					}
					ele.checked = true;
					ele.disabled = true;
				})
				if(checked.length > 0) {
					var jobarr = jobclass.split(','),
						newjobarr = [];
					for(var i = 0; i < jobarr.length; i++) {
						var flag = true;
						for(var j = 0; j < checked.length; j++) {
							if(jobarr[i] == checked[j]) {
								flag = false;
								m("#jobchoosed a").each(function() {
									var id = this.getAttribute('data-id');
									if(id == checked[j]) {
										document.getElementById("jobchoosed").removeChild(this);
									}
								})
							}
						}
						if(flag) {
							newjobarr.push(jobarr[i]);
						}
					}
					var jobnamelist = [];
					for(var i = 0; i < newjobarr.length; i++) {
						jobnamelist.push(jn[newjobarr[i]]);
					}
					jobclass = newjobarr.join(',');
					jobclassname = jobnamelist.join('+');
				}
				//处理jobclass和jobclassname，增加内容
				if(jobclass != '' || jobclassname != '') {
					jobclass += ',' + this.value;
					jobclassname += '+' + jn[this.value];
				} else {
					jobclass += this.value;
					jobclassname += jn[this.value];
				}
				var listlength = jobclass.split(',').length;
			} else {
				//取消选中处理下方已选显示
				var choosed = this.value;
				$("#jobchoosed a").each(function() {
					var elechoose = this;
					var id = elechoose.getAttribute('data-id');
					if(id == choosed) {
						document.getElementById("jobchoosed").removeChild(elechoose);
					}
				});
				//取消该类下所有三级的已选中和不可选状态
				listBox.each(function() {
					var ele = this;
					ele.checked = false;
					ele.disabled = false;
				})
				//处理jobclass和jobclassname，减少内容
				var list = arrsplice(jobclass, this.value);
				var jobnamelist = [];
				for(var i = 0; i < list.length; i++) {
					jobnamelist.push(jn[list[i]]);
				}
				jobclass = list.join(',');
				jobclassname = jobnamelist.join('+');
				var listlength = list.length;
			}
			if(listlength > 0) {
				document.getElementById('jobpencent').classList.remove('none');
				document.getElementById('jobpencent').innerHTML = listlength + '/5';
			}else{
				document.getElementById('jobpencent').classList.add('none');
				document.getElementById('jobpencent').innerHTML ='';
			}
			document.getElementById("job_classid").value = jobclass;
			document.getElementById("jobnameshow").innerHTML = jobclassname;
		})
	})
	//选中单个三级处理
	$('#jobthree .jobthree div .jobthreebox').each(function(j, jobthree) {
		//根据获取到的已选数据，处理类别选中
		if(typeof jobclassidData != "undefined") {
			$.each(jobclassidData, function(index, vaule, arr) {
				if(jobthree.value == vaule.value) {
					jobthree.checked = true;
					m('.jobcheck' + jobthree.value).each(function() {
						var le = this;
						le.checked = true;
						le.disabled = true;
					})
				}
			})
		}
		document.getElementById(jobthree.id).addEventListener('change', function() {
			var jobtwolist = jobclass.split(',');
			var list = [];
			for(var job in jobtwolist) {
				if(jobtwolist[job])
					list.push(jobtwolist[job]);
			}
			if(list.length > 4 && this.checked == true) {
				this.checked = false;
				return mui.toast("最多只能选择5个类别哦");
			}
			if(this.checked == true) {
				//选中处理下方已选显示
				var newchoosed = '<a class="grade_chlose_box_a" data-id="' + this.value + '">' + jn[this.value] + '</a>';
				$("#jobchoosed").prepend(newchoosed);
				//处理jobclass和jobclassname，增加内容
				if(jobclass != '' || jobclassname != '') {
					jobclass += ',' + this.value;
					jobclassname += '+' + jn[this.value];
				} else {
					jobclass += this.value;
					jobclassname += jn[this.value];
				}
				var listlength = jobclass.split(',').length;
			} else {
				//取消选中处理下方已选显示
				var choosed = this.value;
				$("#jobchoosed a").each(function() {
					var elechoose = this;
					var id = elechoose.getAttribute('data-id');
					if(id == choosed) {
						document.getElementById("jobchoosed").removeChild(elechoose);
					}
				});
				//处理jobclass和jobclassname，减少内容
				var list = arrsplice(jobclass, this.value);
				var jobnamelist = [];
				for(var i = 0; i < list.length; i++) {
					jobnamelist.push(jn[list[i]]);
				}
				jobclass = list.join(',');
				jobclassname = jobnamelist.join('+');
				var listlength = list.length;
			}
			if(listlength > 0) {
				document.getElementById('jobpencent').classList.remove('none');
				document.getElementById('jobpencent').innerHTML = listlength + '/5';
			}else{
				document.getElementById('jobpencent').classList.add('none');
				document.getElementById('jobpencent').innerHTML ='';
			}
			document.getElementById("job_classid").value = jobclass;
			document.getElementById("jobnameshow").innerHTML = jobclassname;
		})
	})

})(mui);
//职位多选---------------------------------------------------------------------------------------------------------------结束----------------------

//城市多选---------------------------------------------------------------------------------------------------------------开始----------------------
var citychoose = document.getElementById("citychoose");
var cityone = document.getElementById("cityone");
var citytwo = document.getElementById("citytwo");
var citythree = document.getElementById("citythree");
var cityhtml = '';
var cityhtmltwo = '';
var cityhtmlthree = '';
if(typeof cityclass == "undefined") {
	var cityclass = '';
}
if(typeof cityclassname == "undefined") {
	var cityclassname = '';
}
//点击一级类别
mui("#cityone").on('tap', 'li', function() {
	$(".yun_category_on").removeClass('yun_category_on');
	this.classList.add('yun_category_on');
	var cityid = this.getAttribute('data-id');
	$(".citytwo").addClass('none');
	$(".city" + cityid).removeClass('none');
	$(".citythree").addClass('none');
	var twostyle = $("#citytwo").attr("style");
	if(!twostyle){
		$("#citytwo").css("left", "30.48%");
	}
	$("#citythree").removeAttr("style");
});
//点击二级类别
mui("#citytwo").on('tap', 'li', function() {
	$(".yun_category_ons").removeClass('yun_category_ons');
	this.classList.add('yun_category_ons');
	var cityid = this.getAttribute('data-id');
	$(".citythree").addClass('none');
	$(".city" + cityid).removeClass('none');
	$("#citythree").css("left", "56.96%");
});
//删除已选类别
mui("#citychoosed").on('tap', 'a', function() {
	var id = this.getAttribute('data-id');
	var choosetwo = document.getElementById('citycheckAll' + id);
	if(choosetwo) {
		choosetwo.checked = false;
		var listBox = mui('.citycheck' + id);
		listBox.each(function() {
			var ele = this;
			ele.checked = false;
			ele.disabled = false;
		});
	} else {
		document.getElementById('citythree' + id).checked = false;
	}
	document.getElementById("citychoosed").removeChild(this);
	//处理cityclass和cityclassname，减少内容
	var list = arrsplice(cityclass, id);
	var citynamelist = [];
	for(var i = 0; i < list.length; i++) {
		citynamelist.push(cn[list[i]]);
	}
	if(list.length > 0) {
		document.getElementById('citypencent').classList.remove('none');
		document.getElementById('citypencent').innerHTML = list.length + '/5';
	} else {
		document.getElementById('citypencent').classList.add('none');
		document.getElementById('citypencent').innerHTML = '';
	}
	cityclass = list.join(',');
	cityclassname = citynamelist.join('+');
	document.getElementById("city_classid").value = cityclass;
	document.getElementById("citynameshow").innerHTML = cityclassname;
});
(function(m) {
	$('#citythree .checkAll').each(function(i, citytwo) {
		//根据获取到的已选数据，处理类别选中
		if(typeof cityclassidData != "undefined") {
			$.each(cityclassidData, function(index, vaule, arr) {
				if(citytwo.value == vaule.value) {
					citytwo.checked = true;
					m('.citycheck' + citytwo.value).each(function() {
						var le = this;
						le.checked = true;
						le.disabled = true;
					})
				}
			})
		}
		//选中三级全部处理
		document.getElementById(citytwo.id).addEventListener('change', function() {
			var citytwolist = cityclass.split(',');
			var list = [];
			for(var city in citytwolist) {
				if(citytwolist[city])
					list.push(citytwolist[city]);
			}
			if(list.length > 4 && this.checked == true) {
				this.checked = false;
				return mui.toast("最多只能选择5个类别哦");
			}
			var listBox = m('.citycheck' + this.value);
			if(this.checked) {
				//选中处理下方已选显示
				var checked = [],
					newchoosed = '<a class="grade_chlose_box_a" data-id="' + this.value + '">' + cn[this.value] + '</a>';
				$("#citychoosed").prepend(newchoosed);
				//选中全部则该类下所有三级都设为已选中和不可选状态
				listBox.each(function() {
					var ele = this;
					if(ele.checked == true) {
						checked.push(ele.value);
					}
					ele.checked = true;
					ele.disabled = true;
				})
				if(checked.length > 0) {
					var cityarr = cityclass.split(','),
						newcityarr = [];
					for(var i = 0; i < cityarr.length; i++) {
						var flag = true;
						for(var j = 0; j < checked.length; j++) {
							if(cityarr[i] == checked[j]) {
								flag = false;
								m("#citychoosed a").each(function() {
									var id = this.getAttribute('data-id');
									if(id == checked[j]) {
										document.getElementById("citychoosed").removeChild(this);
									}
								})
							}
						}
						if(flag) {
							newcityarr.push(cityarr[i]);
						}
					}
					var citynamelist = [];
					for(var i = 0; i < newcityarr.length; i++) {
						citynamelist.push(cn[newcityarr[i]]);
					}
					cityclass = newcityarr.join(',');
					cityclassname = citynamelist.join('+');
				}
				//处理cityclass和cityclassname，增加内容
				if(cityclass != '' || cityclassname != '') {
					cityclass += ',' + this.value;
					cityclassname += '+' + cn[this.value];
				} else {
					cityclass += this.value;
					cityclassname += cn[this.value];
				}
				var listlength = cityclass.split(',').length;
			} else {
				//取消选中处理下方已选显示
				var choosed = this.value;
				$("#citychoosed a").each(function() {
					var elechoose = this;
					var id = elechoose.getAttribute('data-id');
					if(id == choosed) {
						document.getElementById("citychoosed").removeChild(elechoose);
					}
				});
				//取消该类下所有三级的已选中和不可选状态
				listBox.each(function() {
					var ele = this;
					ele.checked = false;
					ele.disabled = false;
				})
				//处理cityclass和cityclassname，减少内容
				var list = arrsplice(cityclass, this.value);
				var citynamelist = [];
				for(var i = 0; i < list.length; i++) {
					citynamelist.push(cn[list[i]]);
				}
				cityclass = list.join(',');
				cityclassname = citynamelist.join('+');
				var listlength = list.length;
			}
			if(listlength > 0) {
				document.getElementById('citypencent').classList.remove('none');
				document.getElementById('citypencent').innerHTML = listlength + '/5';
			} else {
				document.getElementById('citypencent').classList.add('none');
				document.getElementById('citypencent').innerHTML = '';
			}
			document.getElementById("city_classid").value = cityclass;
			document.getElementById("citynameshow").innerHTML = cityclassname;
		})
	})
	//选中单个三级处理
	$('#citythree .citythree div .citythreebox').each(function(j, citythree) {
		//根据获取到的已选数据，处理类别选中
		if(typeof cityclassidData != "undefined") {
			$.each(cityclassidData, function(index, vaule, arr) {
				if(citythree.value == vaule.value) {
					citythree.checked = true;
					m('.citycheck' + citythree.value).each(function() {
						var le = this;
						le.checked = true;
						le.disabled = true;
					})
				}
			})
		}
		document.getElementById(citythree.id).addEventListener('change', function() {
			var citytwolist = cityclass.split(',');
			var list = [];
			for(var city in citytwolist) {
				if(citytwolist[city])
					list.push(citytwolist[city]);
			}
			if(list.length > 4 && this.checked == true) {
				this.checked = false;
				return mui.toast("最多只能选择5个类别哦");
			}
			if(this.checked == true) {
				//选中处理下方已选显示
				var newchoosed = '<a class="grade_chlose_box_a" data-id="' + this.value + '">' + cn[this.value] + '</a>';
				$("#citychoosed").prepend(newchoosed);
				//处理cityclass和cityclassname，增加内容
				if(cityclass != '' || cityclassname != '') {
					cityclass += ',' + this.value;
					cityclassname += '+' + cn[this.value];
				} else {
					cityclass += this.value;
					cityclassname += cn[this.value];
				}
				var listlength = cityclass.split(',').length;
			} else {
				//取消选中处理下方已选显示
				var choosed = this.value;
				$("#citychoosed a").each(function() {
					var elechoose = this;
					var id = elechoose.getAttribute('data-id');
					if(id == choosed) {
						document.getElementById("citychoosed").removeChild(elechoose);
					}
				});
				//处理cityclass和cityclassname，减少内容
				var list = arrsplice(cityclass, this.value);
				var citynamelist = [];
				for(var i = 0; i < list.length; i++) {
					citynamelist.push(cn[list[i]]);
				}
				cityclass = list.join(',');
				cityclassname = citynamelist.join('+');
				var listlength = list.length;
			}
			if(listlength > 0) {
				document.getElementById('citypencent').classList.remove('none');
				document.getElementById('citypencent').innerHTML = listlength + '/5';
			} else {
				document.getElementById('citypencent').classList.add('none');
				document.getElementById('citypencent').innerHTML = '';
			}
			document.getElementById("city_classid").value = cityclass;
			document.getElementById("citynameshow").innerHTML = cityclassname;
		})
	})

})(mui);
//城市多选---------------------------------------------------------------------------------------------------------------结束----------------------
function arrsplice(classlist, id) {
	var list = classlist.split(',');
	for(var i = 0; i < list.length; i++) {
		if(id == list[i]) {
			list.splice(i, 1);
		}
	}
	return list;
}