//mui插件select、日期，个人标签
(function($) {
	$.init();
	//邀请面试时间
	var intertimePicker = document.getElementById('intertimePicker');
	if(intertimePicker){
		var intertime = document.getElementById('intertime');
		intertimePicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				intertime.value =rs.text;
				intertimePicker.innerText =  rs.text;
				picker.dispose();
			});	
		}, false);
	}
	//出生日期
	var birthdayUserPicker = document.getElementById('birthdayUserPicker');
	if(birthdayUserPicker){
		var birthday = document.getElementById('birthday');
		birthdayUserPicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				birthday.value =rs.text;
				birthdayUserPicker.innerText =  rs.text;
				picker.dispose();
			});	
		}, false);
	}
	//开始日期
	var sdatePicker = document.getElementById('sdatePicker');
	if(sdatePicker){
		var sdate = document.getElementById('sdate');
		sdatePicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				sdate.value =rs.text;
				sdatePicker.innerText =  rs.text;
				picker.dispose();
			});				
		}, false);
	}
	//结束日期
	var edatePicker = document.getElementById('edatePicker');
	if(edatePicker){
		var edate = document.getElementById('edate');
		edatePicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				edate.value =rs.text;
				edatePicker.innerText =  rs.text;
				picker.dispose();
			});				
		}, false);
	}
	//创建简历最近工作开始时间
	var worksdateComPicker = document.getElementById('worksdateComPicker');
	if(worksdateComPicker){
		var worksdate = document.getElementById('worksdate');
		worksdateComPicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				worksdate.value =rs.text;
				worksdateComPicker.innerText =  rs.text;
				picker.dispose();
			});					
		}, false);
	}
	//创建简历最近工作结束时间
	var workedateComPicker = document.getElementById('workedateComPicker');
	if(workedateComPicker){
		var workedate = document.getElementById('workedate');
		workedateComPicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				workedate.value =rs.text;
				workedateComPicker.innerText =  rs.text;
				picker.dispose();
			});					
		}, false);
	}
	//创建简历最近教育入学时间
	var edusdateComPicker = document.getElementById('edusdateComPicker');
	if(edusdateComPicker){
		var edusdate = document.getElementById('edusdate');
		edusdateComPicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				edusdate.value =rs.text;
				edusdateComPicker.innerText =  rs.text;
				picker.dispose();
			});					
		}, false);
	}
	//创建简历最近教育离校时间
	var eduedateComPicker = document.getElementById('eduedateComPicker');
	if(eduedateComPicker){
		var eduedate = document.getElementById('eduedate');
		eduedateComPicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				eduedate.value =rs.text;
				eduedateComPicker.innerText =  rs.text;
				picker.dispose();
			});					
		}, false);
	}
	//创建简历最近项目开始时间
	var prosdateComPicker = document.getElementById('prosdateComPicker');
	if(prosdateComPicker){
		var prosdate = document.getElementById('prosdate');
		prosdateComPicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				prosdate.value =rs.text;
				prosdateComPicker.innerText =  rs.text;
				picker.dispose();
			});		
		}, false);
	}
	//创建简历最近项目结束时间	
	var proedateComPicker = document.getElementById('proedateComPicker');
	if(proedateComPicker){
		var proedate = document.getElementById('proedate');
		proedateComPicker.addEventListener('tap', function() {
			document.activeElement.blur();
			var optionsJson = this.getAttribute('data-options') || '{}';
			var options = JSON.parse(optionsJson);
			var picker = new $.DtPicker(options);
			picker.show(function(rs) {
				proedate.value =rs.text;
				proedateComPicker.innerText =  rs.text;
				picker.dispose();
			});					
		}, false);
	}
})(mui)
//邀请职位选择职位
var interjobPickerButton = document.getElementById('interjobPicker');
if(typeof interjobData != "undefined" && interjobPickerButton){
	var interjobPicker = new mui.PopPicker();
	interjobPicker.setData(interjobData);
	var jobname = document.getElementById('jobname');
	var linkman = document.getElementById('linkman');
	var linktel = document.getElementById('linktel');
	interjobPickerButton.addEventListener('tap', function(event) {
		document.activeElement.blur();
		interjobPicker.show(function(items) {
			jobname.value = items[0].value;
			linkman.value = items[0].link_man;
			linktel.value = items[0].link_moblie;
  			interjobPickerButton.innerText = items[0].text;
		});
	}, false);
}
//教育经历里的最高学历
var educationUserPickerBtn = document.getElementById('educationUserPicker');
if(typeof eduData != "undefined" && educationUserPickerBtn){
	var educationuserPicker = new mui.PopPicker();
	educationuserPicker.setData(eduData);
	var education = document.getElementById('education'),
		deducation = educationUserPickerBtn.getAttribute('data-education');
	if(deducation) {
		educationuserPicker.pickers[0].setSelectedValue(deducation);
	}
	educationUserPickerBtn.addEventListener('tap', function(event) {
		document.activeElement.blur();
		educationuserPicker.show(function(items) {
			education.value = items[0].value;
			educationUserPickerBtn.innerText = items[0].text;
		});
	}, false);
}
//自我评价标签
var addtagbox = $('.addtagbox')[0];
if(addtagbox){
	addtagbox.addEventListener('tap', function(event) {//添加
		var addfuli = document.getElementById('addfuli').value;
		var error=0;
		var num=0;
		if(addfuli.length>=2 && addfuli.length<=8){
			//判断信息是否已经存在 
			$('.tag').each(function(i,arr){
				var otag = arr.dataset.name;
				if(arr.checked == true) {
					num++;
				}
				if(addfuli == otag){
					error = 1;
					return mui.toast('相同福利已存在，请选择或重新填写！');
				}
			});
			if(num>4){
				document.getElementById('addfuli').value='';
				error = 1;
				return mui.toast('最多只能选择五项！');
			}
			if(error==0){
				var html="<div class='mui-input-row mui-checkbox'><label>"+addfuli+"</label><input   name='tag[]' value='"+addfuli+"' type='checkbox' class='tag' data-name='"+addfuli+"'  checked></div>";
				var oDiv = document.createElement('div');
				oDiv.className = 'yun_info_fl_list';
				oDiv.innerHTML = html;
				document.getElementById('addtaglist').appendChild(oDiv);
			}
			document.getElementById('addfuli').value='';
		}else{
			return mui.toast('请输入2-8个标签字符！');
		}
	}, false);
}
//更多设置
if(document.getElementById("moreset")){
	document.getElementById("moreset").addEventListener('tap', function(e) {
		if(document.getElementById("bg").style.display=='none'){
			setTimeout(function(){
				document.getElementById("bg").style.display='block';
			document.getElementById("bgset").style.display='block';
			},350);
		}
	});
	if(document.getElementById("bg")){
		$("#bg").on('click',function(){
			document.getElementById("bg").style.display='none';
			document.getElementById("bgset").style.display='none';
		})
	}
}

//简历状态切换
if(document.getElementById("privacy")){
	document.getElementById("privacy").addEventListener('toggle', function(e) {
		evalue=e.detail.isActive?1:2;
		$.get(wapurl+"member/index.php?c=up&status="+evalue,function(data){});
		document.getElementById("showprivacy").innerText=e.detail.isActive?'简历公开':'简历保密';
	});
}
//简历刷新
if(document.getElementById("refresh")){
	document.getElementById("refresh").addEventListener('tap', function(e) {
		$.get(wapurl+"member/index.php?c=resumeset&update="+e.target.dataset.id,function(data){
			if(data){
				mui.openWindow({
					url:wapurl+"member/index.php?c=resume&eid="+e.target.dataset.id,
				});
			}else{
				return mui.toast('刷新失败！');
			}
		});
	});
}
//简历默认
if(document.getElementById("resumedefaults")){
	document.getElementById("resumedefaults").addEventListener('tap', function(e) {
		$.get(wapurl+"member/index.php?c=resumeset&def="+e.target.dataset.id,function(data){
			if(data){
				mui.openWindow({
					url:wapurl+"member/index.php?c=resume&eid="+e.target.dataset.id,
				});
			}else{
				return mui.toast('设置失败！');
			}
		});
	});
}

//切换简历
var resumeUserPickerButton = document.getElementById('resumeUserPicker');
if(typeof resumeData != "undefined" && resumeUserPickerButton){
	var resumeuserPicker = new mui.PopPicker();
	resumeuserPicker.setData(resumeData);
	var resume = document.getElementById('resume'),
		dresume = resumeUserPickerButton.getAttribute('data-resume');
	if(dresume) {
		resumeuserPicker.pickers[0].setSelectedValue(dresume);
	}
	resumeUserPickerButton.addEventListener('tap', function(event) {
		resumeuserPicker.show(function(items) {
			mui.openWindow({
				url:wapurl+"member/index.php?c=resume&eid="+items[0].value,
			});
		});
	}, false);
}
