//加减乘除
function accAdd(arg1, arg2) {
	var r1, r2, m;
	try {
		r1 = arg1.toString().split(".")[1].length
	} catch(e) {
		r1 = 0
	}
	try {
		r2 = arg2.toString().split(".")[1].length
	} catch(e) {
		r2 = 0
	}
	m = Math.pow(10, Math.max(r1, r2))
	return(arg1 * m + arg2 * m) / m
}

function accSub(arg1, arg2) {
	return accAdd(arg1, -arg2);
}

function accMul(arg1, arg2) {
	var m = 0,
		s1 = arg1.toString(),
		s2 = arg2.toString();
	try {
		m += s1.split(".")[1].length
	} catch(e) {}
	try {
		m += s2.split(".")[1].length
	} catch(e) {}
	return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m)
}

function accDiv(arg1, arg2) {
	var t1 = 0,
		t2 = 0,
		r1, r2;
	try {
		t1 = arg1.toString().split(".")[1].length
	} catch(e) {}
	try {
		t2 = arg2.toString().split(".")[1].length
	} catch(e) {}
	with(Math) {
		r1 = Number(arg1.toString().replace(".", ""));
		r2 = Number(arg2.toString().replace(".", ""));
		return(r1 / r2) * pow(10, t2 - t1);
	}
}

function myFunction(_this) {
	_this.value = _this.value.replace(/[^0-9]/g, '');
}

/*选择不同会员等级操作*/
$("ul#level_rating").on("tap", "li", function() {
	$(this).addClass("member_set_meal_cur"); //点击li添加class
	$(this).siblings('li').removeClass("member_set_meal_cur"); //删除兄弟li的class属性

	var price = $(this).find("#server_price").val();
	$("#price").html(price);
	$("#r_price").val(price);
	$("#order_price").html(price);
	$("#vip_price").val(price);
	$("#bank_price").val(price);

	var rating = $(this).find("#ratingid").text();
	$("#rating_id").val(rating);
	$("#id").val(rating);
	//切换套餐时，支付方式又积分模式恢复成付款模式
	$("#paymentform").show();
	$("#integral_buy").hide();
	if(document.getElementById('integral_switch_rating')) {
		document.getElementById('integral_switch_rating').classList.remove('mui-active');
		document.querySelector('.mui-switch-handle').setAttribute('style', '');
		
	}
	$("#integral_pay").val('');
	$("#dkjf").val('');
	$("#spread_integral_box").hide();
})

/*选择不同增值服务操作*/
$("ul#pack_type").on("tap", "li", function() {
	$(this).addClass("value_added_cur"); //点击li添加class
	$(this).siblings('li').removeClass("value_added_cur"); //删除兄弟li的class属性

	var price = $(this).find("#server_price").val();
	$("#price").html(price);
	$("#r_price").val(price);
	$("#order_price").html(price);
	$("#vip_price").val(price);

	var service = $(this).find("#serviceid").text();
	$("#service_id").val(service);
	$("#id").val(service);
	//切换套餐时，支付方式又积分模式恢复成付款模式
	$("#paymentform").show();
	$("#integral_buy").hide();
	if(document.getElementById('integral_switch_rating')) {
		document.getElementById('integral_switch_rating').classList.remove('mui-active');
		document.querySelector('.mui-switch-handle').setAttribute('style', '');
	}
	$("#integral_pay").val('');
	$("#dkjf").val('');
	$("#spread_integral_box").hide();

})

/*选择不同充值积分操作*/
$("ul#integral_num").on("click", "li", function() {
	$(this).addClass("pay_choice_cur"); //点击li添加class
	$(this).siblings('li').removeClass("pay_choice_cur"); //删除兄弟li的class属性

	var integral = $(this).attr('data-integral'); //充值积分
	if(integral == '0') {
		$('.defined').show();
		$('#price').html(0);
		$('#bank_price').val(0);
		$('#order_price').val(0);
		$('#integral_int').val(integral);
	} else {
		var integralid = $(this).attr('data-id'); //积分类别id
		var discount = $(this).attr('data-discount'); //积分折扣

		if(parseInt(discount) > 0) {
			var price = (integral / pro) * (discount / 100);
		} else {
			var price = integral / pro;
		}
		price = Math.floor(price * 100) / 100;
		$("#integralid").val(integralid);
		$('.defined').hide();
		$('#user_defined').val('');

		$('#price').html(price);
		$('#bank_price').val(price);
		$('#order_price').val(price);
	 
		$('#integral_int').val(integral);
	}
})

/*支付方式选择*/
function paycheck(type) {
	var type;
	if(type == "alipay") {
		$(".alipay").addClass("member_set_meal_fk_xz_cur");
 		$(".bank").removeClass("member_set_meal_fk_xz_cur");
		$("#paytype").val('alipay');
		$(".paybank").hide();
		$("#paymentform").attr("action", "index.php?c=dingdan");
	} else if(type == "bank") {
		$(".bank").addClass("member_set_meal_fk_xz_cur");
		$(".alipay").removeClass("member_set_meal_fk_xz_cur");
 		$("#paytype").val('bank');
		$(".paybank").show();
		$("#paymentform").attr("action", "index.php?c=paybank");
 	}
}

/*积分填写*/

function checkIntegralNum() {
	var integral = $("#user_defined").val();
	if(parseInt(integral) > 0) {
		var integralval = integral;
		var discountarr = [];
		for(var i in integralclass) {
			if(integralval >= parseInt(integralclass[i]['integral'])) {
				discountarr.push({
					'discount': integralclass[i]['discount'],
					'id': integralclass[i]['id']
				});
			}
		}
		if(discountarr.length > 0) {
			var discount = discountarr[discountarr.length - 1].discount;
			$("#integralid").val(discountarr[discountarr.length - 1].id);
		}
		if(parseInt(discount) > 0) {
			var price = (integral / pro) * (discount / 100);
		} else {
			var price = integral / pro;
		}
		price = Math.floor(price * 100) / 100;
		$('#price').html(price);
		$('#bank_price').val(price);
		$('#order_price').val(price);
		$('#integral_int').val(integral);
	} else {
		$('#price').html(0);
		$('#bank_price').val(0);
		$('#order_price').val(0);
		$('#integral_int').val(0);

	}
}

function autointegral() {
	var integral = $("#user_defined").val();
	if(integral) {
		if(min_integral > 0 && integral < min_integral) {
			integral = min_integral;
			$("#user_defined").val(integral);
			$('#integral_int').val(integral);
		} else {
			$("#user_defined").val(integral);
			$('#integral_int').val(integral);
		}
		var integralval = integral;
		var discountarr = [];
		for(var i in integralclass) {
			if(integralval >= parseInt(integralclass[i]['integral'])) {
				discountarr.push({
					'discount': integralclass[i]['discount'],
					'id': integralclass[i]['id']
				});
			}
		}
		if(discountarr.length > 0) {
			var discount = discountarr[discountarr.length - 1].discount;
			$("#integralid").val(discountarr[discountarr.length - 1].id);
		}else{
			var discount = 0;
			$("#integralid").val('');
		}
		if(parseInt(discount) > 0) {
			var price = (integral / pro) * (discount / 100);
		} else {
			var price = integral / pro;
		}
		price = Math.floor(price * 100) / 100;
		$('#price').html(price);
		$('#bank_price').val(price);
		$('#order_price').val(price);

	}
}

function checkNum(integral, integral_pro) {

	var integral_pay = $("#integral_pay").val();

	var price = $("#r_price").val();

	var need_integral = accMul(price, integral_pro);

	if(parseInt(integral) >= parseInt(need_integral)) {

		if(parseInt(integral_pay) > parseInt(need_integral)) {

			$("#integral_pay").val(parseInt(need_integral));
			var price_n = accSub(price, accDiv(need_integral, integral_pro));
		} else {
			var price_n = accSub(price, accDiv(integral_pay, integral_pro));
		}

	} else {

		if(parseInt(integral_pay) > parseInt(integral)) {
			$("#integral_pay").val(parseInt(integral));
			var price_n = accSub(price, accDiv(integral, integral_pro));
		} else {
			var price_n = accSub(price, accDiv(integral_pay, integral_pro));
		}

	}

	if(price_n <= 0) {

		$("#order_price").html(0);

		$("#paymentform").hide();

		$("#integral_buy").show();

	} else {
		$("#dkjf").val(integral_pay);
		$("#order_price").html(price_n);
		$("#bank_price").val(price_n);
		$("#paymentform").show();
		$("#integral_buy").hide();

	}
}

function integral_add_buy() {
	var service_id = $("#service_id").val();
	var r_integral = $("#r_price").val();
	var integral_need = accMul(r_integral, pro);

	if(parseInt(integral) < parseInt(integral_need)) {
		layermsg("积分不足，请先充值", 2, function() {
			location.href = wapurl + "member/index.php?c=pay";
		});
		return false;
	} else {

		layer_load('执行中，请稍候...');

		$.post("index.php?c=dkzf", {
			tcid: service_id,
			integral: integral_need
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == '0') {
				layermsg(data.msg, 2, function() {
					location.href = wapurl + "member/index.php?c=com";
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = wapurl + "member/index.php?c=rating";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload();
					});
					return false;
				}
			}
		});
	}
}

function integral_rating_buy() {
	var rating_id = $("#rating_id").val();
	var r_integral = $("#r_price").val();
	var integral_need = accMul(r_integral, pro);

	if(parseInt(integral) < parseInt(integral_need)) {
		layermsg("积分不足，请先充值", 2, function() {
			location.href = "index.php?c=pay";
		});
		return false;
	} else {

		layer_load('执行中，请稍候...');
		$.post("index.php?c=dkzf", {
			id: rating_id,
			integral: integral_need
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == '0') {
				layermsg(data.msg, 2, function() {
					location.href = wapurl + "member/index.php?c=com";
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = wapurl + "member/index.php?c=pay";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload();
					});
					return false;
				}
			}
		});
	}
}

function pay_form() {
	 

	var paytype = $('#paytype').val();
	if(paytype == "") {
		layermsg('请选择一种支付方式！', 2);
		return false;
	}

	if(paytype == 'bank') {
		if($('#bank_name').val() == '') {
			layermsg('请填写汇款银行！', 2);
			return false;
		} else if($('#bank_number').val() == '') {
			layermsg('请填写汇入账号！', 2);
			return false;
		} else if($('#bank_price').val() == '') {
			layermsg('请填写汇款金额！', 2);
			return false;
		} else if($('#bank_time').val() == '') {
			layermsg('请填写汇款时间！', 2);
			return false;
		}
	}
}

function integral_form() {

	var integral = $('#integral_int').val();
	if(parseInt(pro) > parseInt(integral)) {
		layermsg('最低充值：' + pro + jifen, 2);
		return false;
	} else if(parseInt(min_integral) > parseInt(integral)) {
		layermsg('最低充值：' + min_integral + jifen, 2);
		return false;
	}

 	var price = $("#order_price").val();

	 

	var paytype = $('#paytype').val();
	if(paytype == "") {
		layermsg('请选择一种支付方式！', 2);
		return false;
	}

	if(paytype == 'bank') {
		if($('#bank_name').val() == '') {
			layermsg('请填写汇款银行！', 2);
			return false;
		} else if($('#bank_number').val() == '') {
			layermsg('请填写汇入账号！', 2);
			return false;
		} else if($('#bank_price').val() == '') {
			layermsg('请填写汇款金额！', 2);
			return false;
		} else if($('#bank_time').val() == '') {
			layermsg('请填写汇款时间！', 2);
			return false;
		}
	}
}

/*职位推广页面 JScript*/

/*选择职位自动刷新天数*/
$("ul#job_auto").on("tap", "li", function() {
	$(this).addClass("spread_box_cur"); //点击li添加class
	$(this).siblings('li').removeClass("spread_box_cur"); //删除兄弟li的class属性
	$('#integral_pay').val('');
	var day = $(this).attr('data-day'); //时间
	if(day) {
		if(integral_online == 3) {
			var price = accMul(parseInt(day), job_auto);
			var price = accMul(parseInt(price), pro);
		} else {
			var price = accMul(parseInt(day), job_auto);
		}
		$('.pricehtml').html(price);
		$('#price').html(price);
		$('#days').val(day);

		if(day == 0) {
			$("#custom").show();
		} else {
			$("#day").val('');
			$("#custom").hide();
		}
		if(document.getElementById('integral_switch')) {
			document.getElementById('integral_switch').classList.remove('mui-active');
			document.querySelector('.mui-switch-handle').setAttribute('style', '');
		}
		$('#spread_integral_box').hide();

	}
})

/*选择职位置顶天数*/
$("ul#job_top").on("tap", "li", function() {
	$(this).addClass("spread_box_cur"); //点击li添加class
	$(this).siblings('li').removeClass("spread_box_cur"); //删除兄弟li的class属性
	$('#integral_pay').val('');
	var day = $(this).attr('data-day'); //时间

	if(day) {
		if(integral_online == 3) {
			var price = accMul(parseInt(day), job_top);
			var price = accMul(parseInt(price), pro);
		} else {
			var price = accMul(parseInt(day), job_top);
		}

		$('.pricehtml').html(price);
		$('#price').html(price);
		$('#days').val(day);

		if(day == 0) {
			$("#custom").show();
		} else {
			$("#custom").hide();
			$("#day").val('');
		}

		if(document.getElementById('integral_switch')) {
			document.getElementById('integral_switch').classList.remove('mui-active');
			document.querySelector('.mui-switch-handle').setAttribute('style', '');
		}
		$('#spread_integral_box').hide();

	}
})

/*选择职位推荐天数*/
$("ul#job_rec").on("tap", "li", function() {
	$(this).addClass("spread_box_cur"); //点击li添加class
	$(this).siblings('li').removeClass("spread_box_cur"); //删除兄弟li的class属性
	$('#integral_pay').val('');
	var day = $(this).attr('data-day'); //时间

	if(day) {
		if(integral_online == 3) {
			var price = accMul(parseInt(day), job_rec);
			var price = accMul(parseInt(price), pro);
		} else {
			var price = accMul(parseInt(day), job_rec);
		}
		$('.pricehtml').html(price);
		$('#price').html(price);
		$('#days').val(day);

		if(day == 0) {
			$("#custom").show();
		} else {
			$("#custom").hide();
			$("#day").val('');
		}

		if(document.getElementById('integral_switch')) {
			document.getElementById('integral_switch').classList.remove('mui-active');
			document.querySelector('.mui-switch-handle').setAttribute('style', '');
		}
		$('#spread_integral_box').hide();

	}
})

/*选择紧急招聘天数*/
$("ul#job_urgent").on("tap", "li", function() {
	$(this).addClass("spread_box_cur"); //点击li添加class
	$(this).siblings('li').removeClass("spread_box_cur"); //删除兄弟li的class属性
	$('#integral_pay').val('');
	var day = $(this).attr('data-day'); //时间

	if(day) {
		if(integral_online == 3) {
			var price = accMul(parseInt(day), job_urgent);
			var price = accMul(parseInt(price), pro);
		} else {
			var price = accMul(parseInt(day), job_urgent);
		}
		$('.pricehtml').html(price);
		$('#price').html(price);
		$('#days').val(day);

		if(day == 0) {
			$("#custom").show();
		} else {
			$("#custom").hide();
			$("#day").val('');
		}

		if(document.getElementById('integral_switch')) {
			document.getElementById('integral_switch').classList.remove('mui-active');
			document.querySelector('.mui-switch-handle').setAttribute('style', '');
		}
		$('#spread_integral_box').hide();

	}
})

function checkDayNum(type) {
	var day = $("#day").val(); //自定义日期
	$('#integral_pay').val('');
	if(type == 'job_auto') {
		var job_price = job_auto;
	} else if(type == 'job_top') {
		var job_price = job_top;
	} else if(type == 'job_rec') {
		var job_price = job_rec;
	} else if(type == 'job_urgent') {
		var job_price = job_urgent;
	}
	if(day) {
		if(integral_online == 3) {
			var price = accMul(day, job_price);
			var price = accMul(price, pro);
		} else {
			var price = accMul(day, job_price);
		}
		$('.pricehtml').html(price);
		$('#price').html(price);
		$('#days').val(day);
	} else {
		$('.pricehtml').html(0);
		$('#price').html(0);
		$('#days').val(0);
	}
}

function checkNumT(integral, integral_pro) {

	var integral_pay = $("#integral_pay").val();
	var price = $(".pricehtml").text();
	//var price = parseInt(price);
	var integral_n = accMul(price, integral_pro);

	if(parseInt(integral) >= parseInt(integral_n)) {

		if(parseInt(integral_pay) > parseInt(integral_n)) {
			$("#integral_pay").val(parseInt(integral_n));
			var price_o = accSub(price, accDiv(integral_n, integral_pro));
		} else {
			var price_o = accSub(price, accDiv(integral_pay, integral_pro));
		}

	} else {
		if(parseInt(integral_pay) > parseInt(integral)) {
			$("#integral_pay").val(parseInt(integral));
			var price_o = accSub(price, accDiv(integral, integral_pro));
		} else {
			var price_o = accSub(price, accDiv(integral_pay, integral_pro));
		}
	}

	if(price_o <= 0) {
		$("#price").html(0);
		$("#zffs").hide();
		$("#qrzf_btn").show();
	} else {
		$("#price").html(price_o);
		$("#zffs").show();
		$("#qrzf_btn").hide();
	}
}

function checkNumJ(integral, integral_pro) {

	var integral_pay = $("#integral_pay").val();
	var price = $(".pricehtml").text();
	//var price = parseInt(price);

	$("#dkhdiv").show();

	var integral_n = accMul(price, integral_pro);

	if(parseInt(integral) >= parseInt(integral_n)) {

		if(parseInt(integral_pay) > parseInt(integral_n)) {
			$("#integral_pay").val(parseInt(integral_n));
			var price_o = accSub(price, accDiv(integral_n, integral_pro));
		} else {
			var price_o = accSub(price, accDiv(integral_pay, integral_pro));
		}

	} else {
		if(parseInt(integral_pay) > parseInt(integral)) {
			$("#integral_pay").val(parseInt(integral));
			var price_o = accSub(price, accDiv(integral, integral_pro));
		} else {
			var price_o = accSub(price, accDiv(integral_pay, integral_pro));
		}
	}

	if(price_o <= 0) {

		$("#dkjfh_price").html(0);
		$("#zffs").hide();
		$("#qrzf_btn").show();
	} else {
		$("#dkjfh_price").html(price_o);
		$("#zffs").show();
		$("#qrzf_btn").hide();
	}
}

function integral_server_buy(type, jobid) {
	var jobid = jobid;
	if(type == 'autojob') {
		var rdays = $('#days').val();
		layer_load('执行中，请稍候...');
		$.post("index.php?c=dkzf", {
			jobautoids: jobid,
			rdays: rdays
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == '0') { //成功
				layermsg(data.msg, 2, function() {
					location.href = "index.php?c=job";
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = "index.php?c=pay";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload();
					});
					return false;
				}

			}
		});
	} else if(type == 'zdjob') {
		var xsdays = $('#days').val();
		layer_load('执行中，请稍候...');
		$.post("index.php?c=dkzf", {
			zdjobid: jobid,
			xsdays: xsdays
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == '0') { //成功
				layermsg(data.msg, 2, function() {
					location.href = "index.php?c=job";
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = "index.php?c=pay";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload();
					});
					return false;
				}
			}
		});
	} else if(type == 'recjob') {
		var recdays = $('#days').val();
		layer_load('执行中，请稍候...');
		$.post("index.php?c=dkzf", {
			recjobid: jobid,
			recdays: recdays
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == '0') { //成功
				layermsg(data.msg, 2, function() {
					location.href = "index.php?c=job";
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = "index.php?c=pay";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload();
					});
					return false;
				}
			}
		});
	} else if(type == 'ujob') {
		var udays = $('#days').val();
		layer_load('执行中，请稍候...');
		$.post("index.php?c=dkzf", {
			ujobid: jobid,
			udays: udays
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == '0') { //成功
				layermsg(data.msg, 2, function() {
					location.href = "index.php?c=job";
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = "index.php?c=pay";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload();
					});
					return false;
				}
			}
		});
	} else if(type == 'sxjob') {
		layer_load('执行中，请稍候...');
		$.post("index.php?c=dkzf", {
			sxjobid: jobid
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == '0') { //成功
				layermsg(data.msg, 2, function() {
					location.href = "index.php?c=job";
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = "index.php?c=pay";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload();
					});
					return false;
				}
			}
		});
	} else if(type == 'sxpart') {
		layer_load('执行中，请稍候...');
		$.post("index.php?c=dkzf", {
			sxpartid: jobid
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == '0') { //成功
				layermsg(data.msg, 2, function() {
					location.href = "index.php?c=part";
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = "index.php?c=pay";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload();
					});
					return false;
				}
			}
		});
	} else if(type == 'sxltjob') {
		layer_load('执行中，请稍候...');
		$.post("index.php?c=dkzf", {
			sxltjobid: jobid
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == '0') { //成功
				layermsg(data.msg, 2, function() {
					location.href = "index.php?c=lt_job";
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = "index.php?c=pay";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload();
					});
					return false;
				}
			}
		});
	} else if(type == 'downresume') {
		layer_load('执行中，请稍候...');
		$.post("index.php?c=dkzf", {
			eid: jobid
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == '0') { //成功
				layermsg(data.msg, 2, function() {
					location.href = wapurl + '/index.php?c=resume&a=show&id=' + jobid;
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = "index.php?c=pay";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload();
					});
					return false;
				}
			}
		});
	} else if(type == 'issue') {
		layer_load('执行中，请稍候...');
		$.post("index.php?c=dkzf", {
			issuejob: 1
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == '0') { //成功
				layermsg(data.msg, 2, function() {
					location.href = "index.php?c=jobadd";
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = "index.php?c=pay";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload();
					});
					return false;
				}
			}
		});
	} else if(type == 'issuepart') {
		layer_load('执行中，请稍候...');
		$.post("index.php?c=dkzf", {
			issuepart: 1
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == '0') { //成功
				layermsg(data.msg, 2, function() {
					location.href = "index.php?c=partadd";
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = "index.php?c=pay";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload();
					});
					return false;
				}
			}
		});
	} else if(type == 'issueltjob') {
		layer_load('执行中，请稍候...');
		$.post("index.php?c=dkzf", {
			issueltjob: 1
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == '0') { //成功
				layermsg(data.msg, 2, function() {
					location.href = "index.php?c=lt_jobadd";
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = "index.php?c=pay";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload();
					});
					return false;
				}
			}
		});
	} else if(type == 'invite') {
		layer_load('执行中，请稍候...');
		$.post("index.php?c=dkzf", {
			invite: 1
		}, function(data) {
			layer.closeAll();
			var data = eval('(' + data + ')');
			if(data.error == '0') { //成功
				layermsg(data.msg, 2, function() {
					location.href = wapurl + '/index.php?c=resume&a=invite&uid=' + jobid;
				});
				return false;
			} else {
				if(data.url) {
					layermsg(data.msg, 2, function() {
						location.href = "index.php?c=pay";
					});
					return false;
				} else {
					layermsg(data.msg, 2, function() {
						location.reload();
					});
					return false;
				}
			}
		});
	}
}

function pay_form_t(type) {
	var days = $("#days").val();
	if(days == '' || days < 1) {
		layermsg("请选择或填写天数！", 2);
		return false;
	}
	var paytype = $('#paytype').val();
	if(paytype == "") {
		layermsg('请选择一种支付方式！', 2);
		return false;
	}
	return true;
}

//其他支付方式
if(document.getElementById('spreadpaymore')) {
	document.getElementById('spreadpaymore').addEventListener('tap', function(event) {
		if(document.getElementById('spreadotherpay').style.display == 'none') {
			document.getElementById('spreadotherpay').style.display = '';
			document.getElementById('switchbank').classList.remove('member_set_meal_fk_xz_cur');
		} else {
			document.getElementById('spreadpaybank').style.display = 'none';
			document.getElementById('spreadotherpay').style.display = 'none';
			document.getElementById('switchbank').classList.remove('member_set_meal_fk_xz_cur');
		}

	});
}

//企业猎头会员套餐和增值套餐积分兑换开关
if(document.getElementById('integral_switch_rating')) {
	document.getElementById('integral_switch_rating').addEventListener('toggle', function(event) {
		if(event.detail.isActive) {
			document.getElementById('spread_integral_box').style.display = 'block';
		} else {
			if(document.getElementById('integral_buy')) { //积分付款和订单付款切换
				if(document.getElementById('integral_buy').style.display == '') {
					document.getElementById('integral_buy').style.display = 'none';
					document.getElementById('paymentform').style.display = '';
				}
			}
			if(document.getElementById('spread_integral_box')) {
				document.getElementById('spread_integral_box').style.display = 'none';
			}
			if(document.getElementById('integral_pay')) {
				document.getElementById('integral_pay').value = '';
			}
			if(document.getElementById('dkjf')) {
				document.getElementById('dkjf').value = '';
			}
			if(document.getElementById('order_price') && document.getElementById('r_price')) {
				document.getElementById('order_price').innerText = document.getElementById('r_price').value
			}
			if(document.getElementById('bank_price') && document.getElementById('r_price')) {
				document.getElementById('bank_price').innerText = document.getElementById('r_price').value
			}
		}
	});

}

//企业getserver.htm内积分兑换开关
if(document.getElementById('integral_switch')) {
	document.getElementById('integral_switch').addEventListener('toggle', function(event) {
		if(event.detail.isActive) {
			document.getElementById('spread_integral_box').style.display = 'block';
		} else {
			if(document.getElementById('spread_integral_box')) { //兑换积分部分隐藏
				document.getElementById('spread_integral_box').style.display = 'none';
			}
			if(document.getElementById('integral_pay')) { //积分输入框
				document.getElementById('integral_pay').value = '';
			}
			if(document.getElementById('qrzf_btn')) {
				if(document.getElementById('qrzf_btn').style.display == '') {
					document.getElementById('qrzf_btn').style.display = 'none';
					document.getElementById('zffs').style.display = '';
				}
			}
			if(document.getElementById('price') && document.getElementById('r_price')) { //发布职位抵扣积分，关闭时恢复价格
				document.getElementById('price').innerText = document.getElementById('r_price').innerHTML
			}
			if(document.getElementById('dkjfh_price') && document.getElementById('r_price')) { //刷新职位抵扣后金额隐藏
				document.getElementById('dkjfh_price').innerText = '';
				document.getElementById('dkhdiv').style.display = 'none';
			}
		}
	});

}

//汇款时间
var banktimePicker = document.getElementById('banktimePicker');
if(banktimePicker) {
	var bank_time = document.getElementById('bank_time');
	banktimePicker.addEventListener('tap', function() {
		document.activeElement.blur();
		var optionsJson = this.getAttribute('data-options') || '{}';
		var options = JSON.parse(optionsJson);
		var picker = new mui.DtPicker(options);
		picker.show(function(rs) {
			bank_time.value = rs.text;
			banktimePicker.innerText = rs.text;
			picker.dispose();
		});
	}, false);
}

//汇款单
/*function previewImage(file) {
	var span = document.getElementById('preview');

	if(file.files && file.files[0]) {
		span.innerHTML = '<img id=photoshow>';
		var img = document.getElementById('photoshow');
		img.onload = function() {
			img.width = 35;
			img.height = 35;

		}
		var reader = new FileReader();
		reader.onload = function(evt) {
			img.src = evt.target.result;
			$("#orderpicchangeAvatar").find('img').attr('src',evt.target.result);
		}
		reader.readAsDataURL(file.files[0]);
	} else { //兼容IE
		var sFilter = 'filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src="';
		file.select();
		var src = document.selection.createRange().text;
		span.innerHTML = '<img id=photoshow>';
		var img = document.getElementById('photoshow');
		img.filters.item('DXImageTransform.Microsoft.AlphaImageLoader').src = src;
		span.innerHTML = "<div id=divhead style='width:280px;height:150px;" + sFilter + src + "\"'></div>";
	}
	$('#preview').show();
}*/