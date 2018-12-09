
layui.use(['element', 'layer'], function(){
		  var element = layui.element;
		  var layer = layui.layer;
		  
		});

layui.use(['layer', 'form'], function(){
    var layer = layui.layer
    ,form = layui.form
    ,$ = layui.$;
    form.on('switch(wapdiy)', function(data){
    	if(data.elem.checked==true){
    		data.value=1;
    	}else{
    		data.value=2
    	}
    	var pytoken=$("#pytoken").val();
    	$.post('index.php?m=admin_tpl_moblies&c=wapdiy',{wapdiy:data.value,pytoken:pytoken},function(msg){
			if(msg){
				
				if(data.value==1){
					layer.msg('开启成功！',2,9);return false;
				}else{
					layer.msg('关闭成功！',2,9);return false;
				}
			}else{
				layer.msg('操作失败！',2,8);
			}
		})
      });
    form.on('radio(site)', function(data){
    	if(data.value==1){
    		$("#site").show();
    	}else{
    		$("#site").hide();
    	}
    });
    form.on('radio(logo)', function(data){
    	if(data.value==1){
    		$("#logo1").show();
    		$("#logo2").hide();
    	}else{
    		$("#logo1").hide();
    		$("#logo2").show();
    	}
    });
    form.on('radio(heardercss)', function(data){
    	$(".heardercssnone").hide();
    	$(".heardercss"+data.value).show();
    });
	form.on('radio(hdshow)', function(data){
    	if(data.value==1){
    		$(".wap_mb_show_hdp").show();
    	}else{
    		$(".wap_mb_show_hdp").hide();
    	}
    });
    form.on('radio(searchshow)', function(data){
    	if(data.value==1){
    		$(".wap_mb_show_search").show();
    	}else{
    		$(".wap_mb_show_search").hide();
    	}
    });
    form.on('radio(notice)', function(data){
    	if(data.value==1){
    		$(".yun_wap_notice").show();
    	}else{
    		$(".yun_wap_notice").hide();
    	}
    });
    form.on('radio(indexnav)', function(data){
    	if(data.value==1){
    		$(".index_nav_yd").show();
    	}else{
    		$(".index_nav_yd").hide();
    	}
    });
    form.on('radio(reglogin)', function(data){
    	if(data.value==1){
    		$("#reglogin").show();
    	}else{
    		$("#reglogin").hide();
    	}
    });
    form.on('radio(ad)', function(data){
    	if(data.value==1){
    		$("#ad").show();
    	}else{
    		$("#ad").hide();
    	}
    });
    form.on('radio(rewardjob)', function(data){
    	if(data.value==1){
    		$("#rewardjob").show();
    	}else{
    		$("#rewardjob").hide();
    	}
    });
    form.on('radio(rewardjobcss)', function(data){
    	if(data.value==1){
    		$("#rewardjobcss1").show();
    		$("#rewardjobcss2").hide();
    	}else{
    		$("#rewardjobcss2").show();
    		$("#rewardjobcss1").hide();
    	}
    });
    form.on('checkbox(rewardjobcom)', function(data){
    	
    	if(data.elem.checked==true){
    		$(".rewardjobcom").show();
    	}else{
    		$(".rewardjobcom").hide();
    	}
    });
    form.on('checkbox(rewardjobsalary)', function(data){
    	if(data.elem.checked==true){
    		$(".rewardjobsalary").show();
    	}else{
    		$(".rewardjobsalary").hide();
    	}
    });
    form.on('checkbox(rewardjobreward)', function(data){
    	if(data.elem.checked==true){
    		$(".rewardjobreward").show();
    	}else{
    		$(".rewardjobreward").hide();
    	}
    });
    form.on('checkbox(rewardjobdate)', function(data){
    	if(data.elem.checked==true){
    		$(".rewardjobdate").show();
    	}else{
    		$(".rewardjobdate").hide();
    	}
    });
    form.on('radio(rewardjobmore)', function(data){
    	if(data.value==1){
    		$(".rewardjobmore").show();
    	}else{
    		$(".rewardjobmore").hide();
    	}
    });
    form.on('radio(hotjob)', function(data){
    	if(data.value==1){
    		$("#hotjob").show();
    	}else{
    		$("#hotjob").hide();
    	}
    });
    form.on('radio(hotjobmore)', function(data){
    	if(data.value==1){
    		$(".hotjobmore").show();
    	}else{
    		$(".hotjobmore").hide();
    	}
    });
    form.on('radio(newjob)', function(data){
    	if(data.value==1){
    		$("#newjob").show();
    	}else{
    		$("#newjob").hide();
    	}
    });
    form.on('checkbox(newjobcom)', function(data){
    	if(data.elem.checked==true){
    		$("#newjobcom").show();
    	}else{
    		$("#newjobcom").hide();
    	}
    });
    form.on('checkbox(newjobsalary)', function(data){
    	if(data.elem.checked==true){
    		$("#newjobsalary").show();
    	}else{
    		$("#newjobsalary").hide();
    	}
    });
    form.on('checkbox(newjobcity)', function(data){
    	if(data.elem.checked==true){
    		$("#newjobcity").show();
    	}else{
    		$("#newjobcity").hide();
    	}
    });
    form.on('checkbox(newjobdate)', function(data){
    	if(data.elem.checked==true){
    		$("#newjobdate").show();
    	}else{
    		$("#newjobdate").hide();
    	}
    });
    form.on('checkbox(newjobwelfare)', function(data){
    	if(data.elem.checked==true){
    		$("#newjobwelfare").show();
    	}else{
    		$("#newjobwelfare").hide();
    	}
    });
    form.on('radio(newjobmore)', function(data){
    	if(data.value==1){
    		$(".newjobmore").show();
    	}else{
    		$(".newjobmore").hide();
    	}
    });
    form.on('radio(hotcom)', function(data){
    	if(data.value==1){
    		$("#hotcom").show();
    	}else{
    		$("#hotcom").hide();
    	}
    });
    form.on('checkbox(hotcomlogo)', function(data){
    	if(data.elem.checked==true){
    		$("#hotcomlogo").show();
    	}else{
    		$("#hotcomlogo").hide();
    	}
    });
    form.on('checkbox(hotcomhy)', function(data){
    	if(data.elem.checked==true){
    		$("#hotcomhy").show();
    	}else{
    		$("#hotcomhy").hide();
    	}
    });
    form.on('checkbox(hotcomcity)', function(data){
    	if(data.elem.checked==true){
    		$("#hotcomcity").show();
    	}else{
    		$("#hotcomcity").hide();
    	}
    });
    form.on('radio(hotcommore)', function(data){
    	if(data.value==1){
    		$(".hotcommore").show();
    	}else{
    		$(".hotcommore").hide();
    	}
    });
    form.on('radio(recjob)', function(data){
    	if(data.value==1){
    		$("#recjob").show();
    	}else{
    		$("#recjob").hide();
    	}
    });
    form.on('checkbox(recjobcom)', function(data){
    	if(data.elem.checked==true){
    		$("#recjobcom").show();
    	}else{
    		$("#recjobcom").hide();
    	}
    });
    form.on('checkbox(recjobsalary)', function(data){
    	if(data.elem.checked==true){
    		$("#recjobsalary").show();
    	}else{
    		$("#recjobsalary").hide();
    	}
    });
    form.on('checkbox(recjobcity)', function(data){
    	if(data.elem.checked==true){
    		$("#recjobcity").show();
    	}else{
    		$("#recjobcity").hide();
    	}
    });
    form.on('checkbox(recjobdate)', function(data){
    	if(data.elem.checked==true){
    		$("#recjobdate").show();
    	}else{
    		$("#recjobdate").hide();
    	}
    });
    form.on('checkbox(recjobwelfare)', function(data){
    	if(data.elem.checked==true){
    		$("#recjobwelfare").show();
    	}else{
    		$("#recjobwelfare").hide();
    	}
    });
    form.on('radio(recjobmore)', function(data){
    	if(data.value==1){
    		$(".recjobmore").show();
    	}else{
    		$(".recjobmore").hide();
    	}
    });
    form.on('radio(urgentjob)', function(data){
    	if(data.value==1){
    		$("#urgentjob").show();
    	}else{
    		$("#urgentjob").hide();
    	}
    });
    form.on('checkbox(urgentjobcom)', function(data){
    	if(data.elem.checked==true){
    		$("#urgentjobcom").show();
    	}else{
    		$("#urgentjobcom").hide();
    	}
    });
    form.on('checkbox(urgentjobsalary)', function(data){
    	if(data.elem.checked==true){
    		$("#urgentjobsalary").show();
    	}else{
    		$("#urgentjobsalary").hide();
    	}
    });
    form.on('checkbox(urgentjobcity)', function(data){
    	if(data.elem.checked==true){
    		$("#urgentjobcity").show();
    	}else{
    		$("#urgentjobcity").hide();
    	}
    });
    form.on('checkbox(urgentjobdate)', function(data){
    	if(data.elem.checked==true){
    		$("#urgentjobdate").show();
    	}else{
    		$("#urgentjobdate").hide();
    	}
    });
    form.on('checkbox(urgentjobwelfare)', function(data){
    	if(data.elem.checked==true){
    		$("#urgentjobwelfare").show();
    	}else{
    		$("#urgentjobwelfare").hide();
    	}
    });
    form.on('radio(urgentjobmore)', function(data){
    	if(data.value==1){
    		$(".urgentjobmore").show();
    	}else{
    		$(".urgentjobmore").hide();
    	}
    });
    
    form.on('radio(resume)', function(data){
    	if(data.value==1){
    		$("#resume").show();
    	}else{
    		$("#resume").hide();
    	}
    });
    form.on('radio(resumepic)', function(data){
    	if(data.value==1){
    		$("#resumepic1").show();
    		$("#resumepic2").hide();
    		$("#resumelabel").show();
    	}else{
    		$("#resumepic2").show();
    		$("#resumepic1").hide();
    		$("#resumelabel").hide();
    	}
    });
    form.on('checkbox(resumeexp)', function(data){
    	if(data.elem.checked==true){
    		$("#resumeexp").show();
    	}else{
    		$("#resumeexp").hide();
    	}
    });
    form.on('checkbox(resumecity)', function(data){
    	if(data.elem.checked==true){
    		$("#resumecity").show();
    	}else{
    		$("#resumecity").hide();
    	}
    });
    form.on('checkbox(resumeedu)', function(data){
    	if(data.elem.checked==true){
    		$("#resumeedu").show();
    	}else{
    		$("#resumeedu").hide();
    	}
    });
    form.on('checkbox(resumeexpect)', function(data){
    	if(data.elem.checked==true){
    		$("#resumeexpect").show();
    	}else{
    		$("#resumeexpect").hide();
    	}
    });
    form.on('radio(resumemore)', function(data){
    	if(data.value==1){
    		$(".resumemore").show();
    	}else{
    		$(".resumemore").hide();
    	}
    });
    
    form.on('radio(article)', function(data){
    	if(data.value==1){
    		$("#article").show();
    	}else{
    		$("#article").hide();
    	}
    });
    form.on('radio(articlecss)', function(data){
    	if(data.value==2){
    		$("#articlecss2").show();
    		$("#articlecss1").hide();
    		$("#articlecss3").hide();
    	}else if(data.value==3){
    		$("#articlecss3").show();
    		$("#articlecss1").hide();
    		$("#articlecss2").hide();
    	}else{
    		$("#articlecss1").show();
    		$("#articlecss2").hide();
    		$("#articlecss3").hide();
    	}
    });
    form.on('radio(articlemore)', function(data){
    	if(data.value==1){
    		$(".articlemore").show();
    	}else{
    		$(".articlemore").hide();
    	}
    });
    
    form.on('radio(zph)', function(data){
    	if(data.value==1){
    		$("#zph").show();
    	}else{
    		$("#zph").hide();
    	}
    });
    form.on('radio(zphmore)', function(data){
    	if(data.value==1){
    		$(".zphmore").show();
    	}else{
    		$(".zphmore").hide();
    	}
    });
    
    form.on('radio(jobclassone)', function(data){
    	if(data.value==1){
    		$("#twojobshow").show();
    		$("#twojobnum").show();
    		$(".jobclassone").show();
    	}else{
    		$("#twojobshow").hide();
    		$("#twojobnum").hide();
    		$(".jobclassone").hide();
    	}
    });
    form.on('checkbox(jobclassonenumall)', function(data){
    	if(data.elem.checked==true){
    		$("#jobclassonenum").val('');
    		$("#jobclassonenum").attr('disabled','disabled');
    	}else{
    		$("#jobclassonenum").removeAttr('disabled');
    	}
    });
    form.on('radio(jobclasstwo)', function(data){
    	if(data.value==1){
    		$(".jobclasstwo").show();
    	}else{
    		$(".jobclasstwo").hide();
    	}
    });
    form.on('checkbox(jobclasstwonumall)', function(data){
    	if(data.elem.checked==true){
    		$("#jobclasstwonum").val('');
    		$("#jobclasstwonum").attr('disabled','disabled');
    	}else{
    		$("#jobclasstwonum").removeAttr('disabled');
    	}
    });
    
  });

layui.use(['carousel', 'form'], function(){
	  var carousel = layui.carousel;
	 
	  carousel.render({
	    elem: '#hdpicshow'
	    ,width: '100%'
	    ,height: '130px'
	    ,interval: 5000
	  });
});

$(function(){
	
    $("#search").on('input propertychange',function(){
        var result = $(this).val();
        $("#pkeyword").attr('placeholder',result);
    });
    
    $("#indexnavname1").on('input propertychange',function(){
        var result = $(this).val();
        $("#pindexnavname1").html(result);
    });
    $("#indexnavdesc1").on('input propertychange',function(){
        var result = $(this).val();
        $("#pindexnavdesc1").html(result);
    });
    $("#indexnavname2").on('input propertychange',function(){
        var result = $(this).val();
        $("#pindexnavname2").html(result);
    });
    $("#indexnavdesc2").on('input propertychange',function(){
        var result = $(this).val();
        $("#pindexnavdesc2").html(result);
    });
    $("#indexnavname3").on('input propertychange',function(){
        var result = $(this).val();
        $("#pindexnavname3").html(result);
    });
    $("#indexnavdesc3").on('input propertychange',function(){
        var result = $(this).val();
        $("#pindexnavdesc3").html(result);
    });
    $("#reglogindesc").on('input propertychange',function(){
        var result = $(this).val();
        $("#preglogindesc").html(result);
    });
    $("#login").on('input propertychange',function(){
        var result = $(this).val();
        $("#plogin").html(result);
    });
    $("#reg").on('input propertychange',function(){
        var result = $(this).val();
        $("#preg").html(result);
    });
    
})

function getcolor(id){
	$(".js_change_color").removeClass("selected");
	$(".bg"+id).addClass("selected");
	$(".wap_header").attr('class',"wap_header bg"+id+" selected");
	$("#color").val(id);
	$(".heardercss").attr('class',"heardercss bg"+id);
}

function rgb2hex(rgb) {
	rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
	function hex(x) {
		return ("0" + parseInt(x).toString(16)).slice(-2);
	}
	return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}

function showpic(fileDom,imgid,pimgid){
	
    if (window.FileReader) {
        var reader = new FileReader();
    } else {
    	layer.msg("您的设备不支持图片预览功能，如需该功能请升级您的设备！",2,8);
    }
    
    var file = fileDom.files[0];
    var imageType = /^image\//;
    
    if (!imageType.test(file.type)) {
        layer.msg("请选择图片！",2,8);return;
    }
    
    reader.onload = function(e) {
        
        var img = document.getElementById(imgid);
        var pimg = document.getElementById(pimgid);
     
        
        img.src = e.target.result;
        pimg.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

function addHd(){
	var randnum='h'+parseInt(Math.random()*1000); 
	$("#addhd").append(function(){
		var html="<li class='wap_mb_list_hd_list' id='"+randnum+"'>" +
				"<div class='wap_mb_list_hd_c'> <span class='wap_mb_list_hd_s'>图标：</span>" +
				"<input type='hidden' name='hdid[]' value=''/>" +
				"<div class='layui-upload'>" +
				"<div class='wap_mb_list_hd_file_box'>" +
				"<input type='file' name='hdpic[]'  id='hd"+randnum+"'  class='wap_mb_list_hd_file_text' onchange=\"showpic(this,'img"+randnum+"','pimg"+randnum+"')\" /> + 添加图标" +
				" </div>" +
				"</div>  " +
				"</div>" +
				"<div class='wap_mb_list_hd_c mt5'> <span class='wap_mb_list_hd_s'>标题：</span>" +
				"<input name='hdname[]'  type='text' class='wap_mb_list_text' autocomplete='off'>" +
				"</div>" +
				"<div class='wap_mb_list_hd_c mt5'> <span class='wap_mb_list_hd_s'>链接：</span>" +
				"<input name='hdurl[]' type='text' value='' class='wap_mb_list_text'  autocomplete='off'>" +
				
				"</div>" +
				"<div class='wap_mb_list_hd_tbimg layui-upload-list'> " +
				"<img src='images/wap_show_img1.png' width='40' height='40' class='layui-upload-img' id='img"+randnum+"'>" +
				"</div>" +
				"<div class='wap_mb_list_tip'> 建议图标尺寸：不小于64*64像素</div>" +
				"<input type='button' value='删除'  onclick=\"deleteupbox('"+randnum+"','hd')\" class='wap_mb_list_hd_sc'>" +
				"</li>";
		
        return html;
    });
	$("#paddhd").append(function(){
		var html="<div id='pr"+randnum+"' class><img id='pimg"+randnum+"' src='images/wap_show_img1.png' width='100%'  height='100'></div>";
        return html;
    });
	layui.use(['carousel', 'form'], function(){
		  var carousel = layui.carousel;
		  
		  carousel.render({
		    elem: '#test3'
		    ,width: '100%'
		    ,height: '100px'
		    ,interval: 5000
		  });
	});
}
function addAdlist(){
	var randnum='ad'+parseInt(Math.random()*1000); 
	$("#addadlist").append(function(){
		var html="<li class='wap_mb_list_hd_list' id='"+randnum+"'>" +
				"<div class='wap_mb_list_hd_c'> <span class='wap_mb_list_hd_s'>图标：</span>" +
				"<input type='hidden' name='adlistid[]' value=''/>" +
				"<div class='layui-upload'>" +
				"<div class='wap_mb_list_hd_file_box'>" +
				"<input type='file' name='adlistpic[]'  id='hd"+randnum+"'  class='wap_mb_list_hd_file_text' onchange=\"showpic(this,'img"+randnum+"','pimg"+randnum+"')\" /> + 添加图标" +
				" </div>" +
				"</div>  " +
				"</div>" +
				"<div class='wap_mb_list_hd_c mt5'> <span class='wap_mb_list_hd_s'>标题：</span>" +
				"<input name='adlistname[]'  type='text' class='wap_mb_list_text' autocomplete='off'>" +
				"</div>" +
				"<div class='wap_mb_list_hd_c mt5'> <span class='wap_mb_list_hd_s'>链接：</span>" +
				"<input name='adlisturl[]' type='text' value='' class='wap_mb_list_text' autocomplete='off'>" +
				
				"</div>" +
				"<div class='wap_mb_list_hd_tbimg layui-upload-list'> " +
				"<img src='images/wap_show_img1.png' width='40' height='40' class='layui-upload-img' id='img"+randnum+"'>" +
				"</div>" +
				"<div class='wap_mb_list_tip'> 建议图标尺寸：不小于64*64像素</div>" +
				"<input type='button' value='删除'  onclick=\"deleteupbox('"+randnum+"','adlist')\"  class='wap_mb_list_hd_sc'>" +
				"</li>";
		
        return html;
    });
	$("#paddadlist").append(function(){
		var html="<li id='pr"+randnum+"'><img src='images/wap_show_img3.png' alt='华为' id='pimg"+randnum+"'></li>";
        return html;
    });
}
function deleteupbox(boxid,type){
	$("#"+boxid).remove();
	$("#pr"+boxid).remove();
	if(type=='hd'){
		layui.use(['carousel', 'form'], function(){
			  var carousel = layui.carousel;
			  
			  carousel.render({
			    elem: '#test3'
			    ,width: '100%'
			    ,height: '100px'
			    ,interval: 5000
			  });
		});
	}
	
}

function upsort(type){
	var $tr = $("#toggle"+type);
	var $ptr = $("#ptoggle"+type);
	var pytoken=$("#pytoken").val()
	if ($tr.index() != 1) {
		$tr.prev().before($tr);
		$ptr.prev().before($ptr);
		var sort =[];
		$("input[name='sort[]']").each(function(index,item){
			sort.push($(this).val());
		});
		$.post('index.php?m=admin_tpl_moblies&c=sort',{sort:sort,pytoken:pytoken},function(data){
			
		})
    }else{
    	layer.msg('已经是第一个了，不能上移',2,8);
    }
}

function downsort(type){
	var len=$(".down").length;
	var $tr = $("#toggle"+type);
	var $ptr = $("#ptoggle"+type);
	var pytoken=$("#pytoken").val()
    if ($tr.index() != len) {
    	$tr.next().after($tr);
        $ptr.next().after($ptr);
    	var sort =[];
		$("input[name='sort[]']").each(function(index,item){
			sort.push($(this).val());
		});
    	$.post('index.php?m=admin_tpl_moblies&c=sort',{sort:sort,pytoken:pytoken},function(data){
    		
		})
    }else{
    	layer.msg('已经是最后一个了，不能下移',2,8);
    }
}
function wapdiypreview(){
	$.layer({
		type:1,
		title:'手机首页预览',
		closeBtn:[0,true],
		offset:['20%','30%'],
		border:[10 , 0.3 , '#000', true],
		area:['290px','380px'],
		page:{dom : '#getwapurl'}
	});
}
