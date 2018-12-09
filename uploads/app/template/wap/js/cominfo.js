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
})(mui);
view.addEventListener('pageBeforeBack', function(e) {
	if(e.detail.page.id == 'welfarehtml') {
		var allwel = '';
		$('.welfare').each(function(w, wel) {
			if(wel.checked == true) {
				if(allwel == '') {
					allwel = wel.dataset.name;
				} else {
					allwel = allwel + ',' + wel.dataset.name;
				}
			}
		});
		document.getElementById('welfareshow').innerText = allwel;
	}
	if(e.detail.page.id == 'moneyhtml') {
		var contenttext = document.getElementById('contenttext').value;
		if(contenttext != "") {
			umeditor.setContent(contenttext);
		}
	}
	if(e.detail.page.id == 'contenthtml') {
		var content = umeditor.getContent();
		if(content != "") {
			document.getElementById('contentshow').innerText = content.replace(/<\/?.+?>/g, "").replace(/ /g, "");
			document.getElementById('contenttext').innerText = content;
		}
	}
});
var moneytypeData = [{
	value: 1,
	text: '人民币'
}, {
	value: 2,
	text: '美元'
}];
(function($, doc) {
	$.init();
	$.ready(function() {
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
	});
	//添加福利
	//var result = $('#result')[0];
	var addwelfarebox = $('.addwelfarebox')[0];
	if(addwelfarebox) {
		addwelfarebox.addEventListener('tap', function(event) { //添加福利
			var welfare = doc.getElementById('addwelfare').value;
			var error = 0;
			if(welfare.length >= 2 && welfare.length <= 8) {
				//判断信息是否已经存在 
				$('.welfare').each(function(i, arr) {
					var otag = arr.dataset.name;
					if(welfare == otag) {
						error = 1;
						return mui.toast('相同福利已存在，请选择或重新填写！');
					}
				});
				if(error == 0) {
					var html = "<div class='mui-input-row mui-checkbox'><label>" + welfare + "</label><input name='welfare[]' value='" + welfare + "' type='checkbox' class='welfare' data-name='" + welfare + "' checked></div>";
					var oDiv = doc.createElement('div');
					oDiv.className = 'yun_info_fl_list';
					oDiv.innerHTML = html;
					doc.getElementById('addwelfarelist').appendChild(oDiv);
				}
				doc.getElementById('addwelfare').value = '';
			} else {
				return mui.toast('请输入2-8个福利字符！');
			}
		}, false);
	}

})(mui, document);

(function() {
	mui('.yunset_bth_box').on('tap', '.addnext', function() {
		var name = document.getElementById('name'),
			shortname = document.getElementById('shortname'),
			hy = document.getElementById('hy'),
			pr = document.getElementById('pr'),
			mun = document.getElementById('mun'),
			provinceid = document.getElementById('provinceid'),
			cityid = document.getElementById('cityid'),
			three_cityid = document.getElementById('three_cityid'),
			address = document.getElementById('address'),
			linkman = document.getElementById('linkman'),
			linktel = document.getElementById('linktel'),
			linkphone = document.getElementById('linkphone'),
			content = document.getElementById('contenttext');

		if(name.value == '') {
			mui.toast('请填写企业全称！');
			return false;
		}
		if(hy.value == '') {
			mui.toast('请选择从事行业！');
			return false;
		}
		if(pr.value == '') {
			mui.toast('请选择企业性质！');
			return false;
		}
		if(mun.value == '') {
			mui.toast('请选择企业规模！');
			return false;
		}
		if(cityid.value == '') {
			mui.toast('请选择所在地！');
			return false;
		}
		if(address.value == '') {
			mui.toast('请填写公司地址！');
			return false;
		}
		if(linkman.value == '') {
			mui.toast('请填写联系人！');
			return false;
		}
		if(linktel.value == '' && linkphone.value == '') {
			mui.toast('固定电话与手机号码必须填写一项！');
			return false;
		}
		if(linktel.value != '' && !isjsMobile(linktel.value)) {
			mui.toast('请填写正确手机格式！');
			return false;
		}
		if(linkphone.value != '' && !isjsTell(linkphone.value)) {
			mui.toast('请填写正确电话格式！');
			return false;
		}
		if(content.innerText == '') {
			mui.toast('请填写企业简介！');
			return false;
		}
	})

	var infosubmitBtn = document.getElementById('infosubmit')
	if(infosubmitBtn) {
		infosubmitBtn.addEventListener('tap', checkinfo, false)
	}
})();

function checkinfo() {

	var name = document.getElementById('name'),
		shortname = document.getElementById('shortname'),
		hy = document.getElementById('hy'),
		pr = document.getElementById('pr'),
		mun = document.getElementById('mun'),
		provinceid = document.getElementById('provinceid'),
		cityid = document.getElementById('cityid'),
		three_cityid = document.getElementById('three_cityid'),
		address = document.getElementById('address'),
		linkman = document.getElementById('linkman'),
		linktel = document.getElementById('linktel'),
		linkphone = document.getElementById('linkphone'),
		linkmail = document.getElementById('linkmail'),
		website = document.getElementById('website'),
		linkjob = document.getElementById('linkjob'),
		linkqq = document.getElementById('linkqq'),
		busstops = document.getElementById('busstops'),
		sdate = document.getElementById('sdate'),
		money = document.getElementById('money'),
		preview = document.getElementById('preview'),
		moneytype = document.getElementById('moneytype'),
		content = document.getElementById('contenttext'),
		welfare = document.getElementById('welfareshow');

	if(name.value == '') {
		return mui.toast('请填写企业全称！');
	}
	if(hy.value == '') {
		return mui.toast('请选择从事行业！');
	}
	if(pr.value == '') {
		return mui.toast('请选择企业性质！');
	}
	if(mun.value == '') {
		return mui.toast('请选择企业规模！');
	}
	if(cityid.value == '') {
		return mui.toast('请选择所在地！');
	}
	if(address.value == '') {
		return mui.toast('请填写公司地址！');
	}
	if(linkman.value == '') {
		return mui.toast('请填写联 系 人！');
	}
	if(linktel.value == '' && linkphone.value == '') {
		return mui.toast('固定电话与手机号码必须填写一项！');
	}
	if(linktel.value != '' && !isjsMobile(linktel.value)) {
		mui.toast('请填写正确手机格式！');
		return false;
	}
	if(linkphone.value != '' && !isjsTell(linkphone.value)) {
		mui.toast('请填写正确电话格式！');
		return false;
	}
	if(content.innerText == '') {
		return mui.toast('请填写企业简介！');
	}

	formData.append('name', name.value);
	formData.append('shortname', shortname.value);
	formData.append('hy', hy.value);
	formData.append('pr', pr.value);
	formData.append('mun', mun.value);
	formData.append('provinceid', provinceid.value);
	formData.append('cityid', cityid.value);
	formData.append('three_cityid', three_cityid.value);
	formData.append('address', address.value);

	formData.append('linkman', linkman.value);
	formData.append('linktel', linktel.value);
	formData.append('linkphone', linkphone.value);
	formData.append('linkmail', linkmail.value);

	formData.append('website', website.value);
	formData.append('linkjob', linkjob.value);
	formData.append('linkqq', linkqq.value);
	formData.append('busstops', busstops.value);
	formData.append('sdate', sdate.value);
	formData.append('preview', preview.value);
	formData.append('money', money.value);
	formData.append('moneytype', moneytype.value);

	formData.append('content', content.innerText);
	formData.append('welfare', welfare.innerText);
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

$(function() {
	
	function toFixed2(num) {
		return parseFloat(+num.toFixed(2));
	}

	$('#cancleBtn').on('click', function() {
		$("#showEdit").fadeOut();
		$('#showResult').fadeIn();
	});
	$('#confirmBtn').on('click', function() {
		$("#showEdit").fadeOut();

		var $image = $('#report > img');
		var dataURL = $image.cropper("getCroppedCanvas");
		var imgurl = dataURL.toDataURL("image/jpeg", 0.5);
		$("#changeAvatar > img").attr("src", imgurl);
		$("#uimage").val(imgurl);
		$('#showResult').fadeIn();

	});

	function cutImg() {
		$('#showResult').fadeOut();
		$("#showEdit").fadeIn();
	}
	$('#image').on('change', function() {
		cutImg();
	});

	var $image = $('#report > img'),
		options = {
			modal: true,
			autoCropArea: 0.5,
			dragCrop: false,
			movable: true,
			resizable: false,
			minContainerWidth: 400,
			minContainerHeight: 400,
			aspectRatio: 1 / 1,
			crop: function(data) {

			}
		};

	$image.on().cropper(options);

	var $inputImage = $('#image'),
		URL = window.URL || window.webkitURL,
		blobURL;
	if(URL) {
		$inputImage.change(function() {
			var files = this.files,
				file;

			if(files && files.length) {
				file = files[0];

				if(/^image\/\w+$/.test(file.type)) {
					blobURL = URL.createObjectURL(file);

					$image.one('built.cropper', function() {
						URL.revokeObjectURL(blobURL); // Revoke when load complete
					}).cropper('reset', true).cropper('replace', blobURL);

					$inputImage.val('');
				} else {
					showMessage('请上传图片');
				}
			}
		});
	} else {
		$inputImage.parent().remove();
	}
});

function photo() {
	var uimage = $("#uimage").val();
	if(uimage == '') {
		layermsg('头像未改变，无需修改');
		return false;
	}
	var regS = new RegExp("\\+", "gi");
	uimage = uimage.replace(regS, "#");
	$.ajax({
		type: 'POST',
		url: "index.php?c=photo",
		cache: false,
		dataType: 'json',
		data: {
			uimage: uimage,
			submit: 1
		},
		success: function(msg) {
			if(msg == '1') {
				var date = '操作成功！';
			} else {
				var date = '操作失败！';
			}
			layermsg(date, 2, function() {
				window.location.href = wapurl + "member/index.php?c=info";
			});
			return false;
		}
	});
}