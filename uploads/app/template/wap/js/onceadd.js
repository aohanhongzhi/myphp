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
	    bounce: true //是否启用回弹
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
	view.addEventListener('pageBeforeBack', function(e) {
		document.getElementById('contentshow').innerText = document.getElementById('content').value;
		//console.log(e);
	});
})(mui);

(function() {
	var oncesubmit = document.getElementById('oncesubmit');
	oncesubmit.addEventListener('tap', function(event) {
		var title = document.getElementById('title').value,
			salary = document.getElementById('salary').value,
			address = document.getElementById('address').value,
			content = document.getElementById('content').value,
			companyname = document.getElementById('companyname').value,
			linkman = document.getElementById('linkman').value,
			phone = document.getElementById('phone').value,
			edate = document.getElementById('edate').value,
			password = document.getElementById('password').value,
			preview = document.getElementById('preview').value,
			id = document.getElementById('id').value,
			checkcode,
			geetest_challenge,
			geetest_validate,
			geetest_seccode;
		if(!id || id == '') {
			id = 0;
		} else {
			id = id;
		}

		if(!pic || pic == '') {
			pic = '';
		} else {
			pic = pic;
		}
		if(title == '') {
			return mui.toast('请填写招聘名称！');
		}
		if(salary == '') {
			return mui.toast('请填写工作薪资！');
		}
		if(address == '') {
			return mui.toast('请填写工作地点！');
		}
		if(content == '') {
			return mui.toast('请填写招聘要求！');
		}
		if(companyname == '') {
			return mui.toast('请填写店面名称！');
		}
		if(linkman == '') {
			return mui.toast('请填写联系人！');
		}
		if(phone == '') {
			return mui.toast('请填写联系电话！');
		}
		 

		if(edate == '') {
			return mui.toast('请填写招聘有效期！');
		}
		if(password == '') {
			return mui.toast('请填写密码！');
		}
		if(code_web.indexOf("店铺招聘")>=0) {
			if(code_kind == 1) {
				var code = $('#checkcode').val();
				if(code == '') {
					return mui.toast('请填写验证码！');
				}
			} else if(code_kind == 3) {
				geetest_challenge = $('input[name="geetest_challenge"]').val();
				geetest_validate = $('input[name="geetest_validate"]').val();
				geetest_seccode = $('input[name="geetest_seccode"]').val();

				if(geetest_challenge == '' || geetest_validate == '' || geetest_seccode == '') {
					$("#popup-submit").trigger("click");
					return mui.toast('请点击按钮进行验证！');
				}
			}
		}
		formData.append('title', title);
		formData.append('salary', salary);
		formData.append('address', address);
		formData.append('companyname', companyname);
		formData.append('linkman', linkman);
		formData.append('phone', phone);
		formData.append('require', content);
		formData.append('edate', edate);
		formData.append('password', password);
		formData.append('preview', preview);
		formData.append('id', id);
		if(code_web.indexOf("店铺招聘") >= 0){
			if(code_kind == 1){
				formData.append('checkcode', code);
			}else if(code_kind == 3){
				formData.append('geetest_challenge', geetest_challenge);
				formData.append('geetest_validate', geetest_validate);
				formData.append('geetest_seccode', geetest_seccode);
			}
		}
		formData.append('submit', 1);
		$.ajax({
			url: "index.php?c=once&a=add",
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
					checkCode('vcode_img');
					layermsg(data.msg, 2);
					return false;
				}
			}
		})
	}, false)
})();