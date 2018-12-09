var umeditor = UM.getEditor('content', {
	toolbar: false,
	elementPathEnabled: false,
	wordCount: false,
	autoHeightEnabled: false
});
mui.init();
var viewApi = mui('#app').view({
	defaultPage: '#main'
});

var view = viewApi.view;
(function($) {
	//初始化单页的区域滚动
	mui('.mui-scroll-wrapper').scroll({
		scrollY: true, //是否竖向滚动
		scrollX: false, //是否横向滚动
		startX: 0, //初始化时滚动至x
		startY: 0, //初始化时滚动至y
		indicators: true, //是否显示滚动条
		deceleration: 0.0006, //阻尼系数,系数越小滑动越灵敏
		// bounce: true //是否启用回弹
	});
	//处理view的后退与webview后退
	var oldBack = $.back;
	$.back = function() {
		if(viewApi.canBack()) { //如果view可以后退，则执行view的后退
			viewApi.back();
		} else { //执行webview后退
			oldBack();
		}
	};
	view.addEventListener('pageShow', function(e) { //动画开始前触发
		if(e.detail.page.id == 'contenthtml') {
			var contenttext = document.getElementById('contenttext').value;
			if(contenttext != "") {
				umeditor.setContent(contenttext);
			}
		}
		if(e.detail.page.id == 'maphtml') {
			var mapx = document.getElementById('map_x').value,
				mapy = document.getElementById('map_y').value,
				address = document.getElementById('address').value;
			if((mapx==""||mapy=="")&&address!=""){
				localsearch(searchcity); 
			}else if(mapx!="" && mapy!=""){
				setLocation('map_container', mapx, mapy, "map_x", "map_y"); 
			}else{
				var geolocation = new BMap.Geolocation();
				geolocation.getCurrentPosition(function(r) {
					if(this.getStatus() == BMAP_STATUS_SUCCESS) {
						location_r = r;
						setLocation('map_container', r.point.lng, r.point.lat, "map_x", "map_y");
					}
				}, {
					enableHighAccuracy: true
				}); 
			}
		}
	});
	view.addEventListener('pageBeforeBack', function(e) {
		if(e.detail.page.id == 'salaryhtml') {
			if(document.getElementById('salary').value) {
				document.getElementById('salary_typeshow').innerText = document.getElementById('salary').value + $('.salary_typename')[0].innerHTML;
			}
		}
		if(e.detail.page.id == 'contenthtml') {
			var content = umeditor.getContent();
			if(content != "") {
				document.getElementById('contentshow').innerText = content.replace(/<\/?.+?>/g, "").replace(/ /g, "");
				document.getElementById('contenttext').innerText = content;
			}
		}
		if(e.detail.page.id == 'timehtml') {
			//兼职时间
			var jz = "",
				worktimeshow = "";
			$(".lang").each(function(w, lang) {
				if(lang.checked == true) {
					if(jz == "") {
						jz = lang.dataset.id;
					} else {
						jz = jz + "," + lang.dataset.id;
					}

				}
			});
			document.getElementById("worktime").value = jz;
			document.getElementById("worktimeshow").innerText = jz != "" ? "上、中、晚时段" : "请选择";
		}
		if(e.detail.page.id == 'datehtml') {
			//有效期
			var timetype = document.getElementById('timetype').value;
			if(timetype == 1) {
				document.getElementById('descriptionshow').innerText = '短期招聘';
			} else {
				document.getElementById('descriptionshow').innerText = '长期招聘';
			}
		}
	});
})(mui);
(function($, doc) {
	$.init();
	$.ready(function() {
		if(typeof timetypeData != "undefined") {
			var timetypePicker = new $.PopPicker();
			timetypePicker.setData(timetypeData);
			var timetypePickerBtn = doc.getElementById('timetypePicker');
			var timetype = doc.getElementById('timetype');
			var dtimetype = timetypePickerBtn.getAttribute('data-timetype');

			if(dtimetype == 0) {
				timetypePicker.pickers[0].setSelectedValue(0);
			} else {
				timetypePicker.pickers[0].setSelectedValue(1);
			}
			timetypePickerBtn.addEventListener('tap', function(event) {
				document.activeElement.blur();
				timetypePicker.show(function(items) {
					timetype.value = items[0].value;
					timetypePickerBtn.innerText = items[0].text;
					change();
				});
			}, false);
		}
		//开始日期
		var sdatePicker = document.getElementById('sdatePicker');
		if(sdatePicker) {
			var sdate = document.getElementById('sdate');
			sdatePicker.addEventListener('tap', function() {
				document.activeElement.blur();
				var optionsJson = this.getAttribute('data-options') || '{}';
				var options = JSON.parse(optionsJson);
				var picker = new $.DtPicker(options);
				picker.show(function(rs) {
					sdate.value = rs.text;
					sdatePicker.innerText = rs.text;
					picker.dispose();
				});
			}, false);
		}
		//结束日期
		var edatePicker = document.getElementById('edatePicker');
		if(edatePicker) {
			var edate = document.getElementById('edate');
			edatePicker.addEventListener('tap', function() {
				document.activeElement.blur();
				var optionsJson = this.getAttribute('data-options') || '{}';
				var options = JSON.parse(optionsJson);
				var picker = new $.DtPicker(options);
				picker.show(function(rs) {
					edate.value = rs.text;
					edatePicker.innerText = rs.text;
					picker.dispose();
				});
			}, false);
		}
		var deadline = document.getElementById('deadline');
		var deadComPicker = document.getElementById('deadComPicker');
		deadComPicker.addEventListener('tap', function() {
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				deadline.value = rs.text;
				deadComPicker.innerText = rs.text;
				picker.dispose();
			});
		}, false);
		var salary_typeComPicker = new $.PopPicker();
		salary_typeComPicker.setData(salary_typeData);
		var salary_typeComPickerButton = doc.getElementById('salary_typeComPicker');
		var salary_type = doc.getElementById('salary_type');
		var dsalary_type = salary_typeComPickerButton.getAttribute('data-salary_type');
		if(dsalary_type) {
			salary_typeComPicker.pickers[0].setSelectedValue(dsalary_type);
		}
		salary_typeComPickerButton.addEventListener('tap', function(event) {
			document.activeElement.blur();
			salary_typeComPicker.show(function(items) {
				salary_type.value = items[0].value;
				salary_typeComPickerButton.innerText = items[0].text;
			});
		}, false);
		var billing_cycleComPicker = new $.PopPicker();
		billing_cycleComPicker.setData(billing_cycleData);
		var billing_cycleComPickerButton = doc.getElementById('billing_cycleComPicker');
		var billing_cycle = doc.getElementById('billing_cycle');
		var dbilling_cycle = billing_cycleComPickerButton.getAttribute('data-billing_cycle');
		if(dbilling_cycle) {
			billing_cycleComPicker.pickers[0].setSelectedValue(dbilling_cycle);
		}
		billing_cycleComPickerButton.addEventListener('tap', function(event) {
			document.activeElement.blur();
			billing_cycleComPicker.show(function(items) {
				billing_cycle.value = items[0].value;
				billing_cycleComPickerButton.innerText = items[0].text;
			});
		}, false);

	});
})(mui, document);

(function() {
	var submit = document.getElementById('submit')
	submit.addEventListener('tap', function(event) {
		var id = document.getElementById('id'),
			name = document.getElementById('name'),
			type = document.getElementById('type'),
			number = document.getElementById('number'),
			worktime = document.getElementById('worktime'),
			sdate = document.getElementById('sdate'),
			edate = document.getElementById('edate'),
			deadline = document.getElementById('deadline'),
			timetype = document.getElementById('timetype'),
			sex = document.getElementById('sex'),
			salary = document.getElementById('salary'),
			salary_type = document.getElementById('salary_type'),
			billing_cycle = document.getElementById('billing_cycle'),
			provinceid = document.getElementById('provinceid'),
			cityid = document.getElementById('cityid'),
			three_cityid = document.getElementById('three_cityid'),
			address = document.getElementById('address'),
			x = document.getElementById('map_x'),
			y = document.getElementById('map_y'),
			content = document.getElementById('contenttext'),
			linkman = document.getElementById('linkman'),
			linktel = document.getElementById('linktel');

		if(name.value == '') {
			return mui.toast('请填写职位名称！');
		}
		if(type.value == '') {
			return mui.toast('请选择工作类型！');
		}
		if(number.value == '') {
			return mui.toast('请确定招聘人数！');
		}
		if(worktime.value == '') {
			return mui.toast('请选择兼职时间！');
		}
		if(sdate.value == '') {
			return mui.toast('请选择兼职开始时间！');
		}
		if(timetype.value == '1') {
			if(edate.value == '') {
				return mui.toast('请选择兼职结束时间！');
			}
			if(deadline.value == '') {
				return mui.toast('请选择报名截止时间！');
			}
			if(edate.value < sdate.value) {
				return mui.toast('兼职结束日期不能小于兼职开始日期！');
			}
			if(deadline.value > edate.value) {
				return mui.toast('兼职报名截止日期不能大于兼职结束日期！');
			}
		}

		if(salary.value == '') {
			return mui.toast('请填写薪水！');
		}
		if(salary_type.value == '') {
			return mui.toast('请选择薪水类别！');
		}
		if(billing_cycle.value == '') {
			return mui.toast('请选择结算周期！');
		}
		if(provinceid.value == '' || cityid.value == '' || three_cityid.value == '') {
			return mui.toast('请选择工作区域！');
		}
		if(address.value == '') {
			return mui.toast('请填写详细地址！');
		}

		if(x.value == '' || y.value == '') {
			return mui.toast('请设置地图！');
		}

		if(content.value == '') {
			return mui.toast('请填写兼职内容！');
		}
		if(linkman.value == '') {
			return mui.toast('请填写联系人！');
		}
		if(linktel.value == '') {
			return mui.toast('请填写联系手机！');
		} else if(!isjsMobile(linktel.value)) {
			return mui.toast('请填正确手机格式！');
		}
		mui.post("index.php?c=partadd", {
			id: id.value,
			name: name.value,
			type: type.value,
			number: number.value,
			worktime: worktime.value,
			sdate: sdate.value,
			timetype: timetype.value,
			edate: edate.value,
			deadline: deadline.value,
			sex: sex.value,
			salary: salary.value,
			salary_type: salary_type.value,
			billing_cycle: billing_cycle.value,
			provinceid: provinceid.value,
			cityid: cityid.value,
			three_cityid: three_cityid.value,
			y: y.value,
			x: x.value,
			address: address.value,
			content: content.value,
			linkman: linkman.value,
			linktel: linktel.value,
			submit: 'submit'
		}, function(data) {
			layermsg(data.msg, 2, function() {
				location.href = data.url;
			});
		}, 'json');
	}, false)
	//选择快捷输入
	mui('.mui-popover').on('tap', 'li', function(e) {
		document.getElementById("name").value = document.getElementById("name").value + this.children[0].innerHTML;
		mui('.mui-popover').popover('toggle')
	})
})();