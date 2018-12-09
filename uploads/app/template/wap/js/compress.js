function previewImage(obj,type){
	
	setFilesReader(obj.files[0], 0,type);
	
}
function setFilesReader(file, ismul,type) {
	var reader = new window.FileReader();
	reader.onload = function(e) {
		compress(this.result, fileSize, ismul,type);
	}
	reader.readAsDataURL(file);
	//console.log(this.files[0]);
	var fileSize = Math.round(file.size/1024/1024) 
}

//onchange="readMultiFiles(this.files)"
function readMultiFiles(files) {
	for (var i = 0; i < files.length; i++) {
		setFilesReader(files[i]);
	}
}

//res代表上传的图片，fileSize大小图片的大小
function compress(res, fileSize, ismul,type) {
    var img = new Image(), maxW = 1000; //设置最大宽度

    img.onload = function () {
        var cvs = document.createElement('canvas'), ctx = cvs.getContext('2d');

        if(img.width > maxW) {
            img.height *= maxW / img.width;
            img.width = maxW;
        }

        cvs.width = img.width;
        cvs.height = img.height;

        ctx.clearRect(0, 0, cvs.width, cvs.height);
        ctx.drawImage(img, 0, 0, img.width, img.height);

        var compressRate = getCompressRate(1,fileSize);

        var dataUrl = cvs.toDataURL('image/jpeg', compressRate);

		$('#'+type+'img').attr('src',dataUrl);
		$('#'+type+'show').show();
		
		$('#'+type).val(dataUrl);
    }
    img.src = res;
}

//计算压缩比率，size单位为MB
function getCompressRate(allowMaxSize,fileSize){
	var compressRate = 1;
		
	if(fileSize/allowMaxSize > 4){
	   compressRate = 0.5;
	} else if(fileSize/allowMaxSize >3){
	   compressRate = 0.6;
	} else if(fileSize/allowMaxSize >2){
	   compressRate = 0.7;
	} else if(fileSize > allowMaxSize){
	   compressRate = 0.8;
	} else{
	   compressRate = 0.9;
	}
	return compressRate;
}