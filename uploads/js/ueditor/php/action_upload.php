<?php
/**
 * 上传附件和上传视频
 * User: Jinqn
 * Date: 14-04-09
 * Time: 上午10:17
 */
include "Uploader.class.php";

include(dirname(dirname(dirname(dirname(__FILE__))))."/data/plus/config.php");
//图片限定大小
if($config['pic_maxsize']){
	$picmaxsize=(int)$config['pic_maxsize']*1024*1024;
}else{
	$maxsize=5*1024*1024;
}
//文件限定大小
if($config['file_maxsize']){
	$filemaxsize=(int)$config['file_maxsize']*1024*1024;
}else{
	$filemaxsize=5*1024*1024;
}
//传入设定 图片类型 
if($config['pic_type']){
	$pic_type = explode(',',str_replace(' ','',$config['pic_type']));
	foreach($pic_type as $key=>$value){
		$pic_type[$key] = '.'.$value;
	}
}else{
	$pic_type = array('.jpg','.png','.jpeg','.bmp','.gif');
}

//传入设定 文件类型 
if($config['file_type']){
	$file_type = explode(',',str_replace(' ','',$config['file_type']));
	foreach($file_type as $key=>$value){
		$file_type[$key] = '.'.$value;
	}
}else{
	$file_type = array('.rar','.zip','.doc','.docx','.xls');
}
/* 上传配置 */
$base64 = "upload";
switch (htmlspecialchars($_GET['action'])) {
    case 'uploadimage':
        $config = array(
            "pathFormat" => $CONFIG['imagePathFormat'],
            //"maxSize" => $CONFIG['imageMaxSize'],
           // "allowFiles" => $CONFIG['imageAllowFiles']
		   "maxSize" => $picmaxsize,
           "allowFiles" => $pic_type,
		   "uptype"		=> 'image',
		   "is_picself"    => $config['is_picself'],
		   "is_picthumb"    => $config['is_picthumb']

        );
        $fieldName = $CONFIG['imageFieldName'];
        break;
    case 'uploadscrawl':
        $config = array(
            "pathFormat" => $CONFIG['scrawlPathFormat'],
           // "maxSize" => $CONFIG['scrawlMaxSize'],
            //"allowFiles" => $CONFIG['scrawlAllowFiles'],
			"maxSize" => $picmaxsize,
           "allowFiles" => $pic_type,
            "oriName" => "scrawl.png",
		   "uptype"		=> 'image',
		   "is_picself"    => $config['is_picself'],
		   "is_picthumb"    => $config['is_picthumb']
        );
        $fieldName = $CONFIG['scrawlFieldName'];
        $base64 = "base64";
        break;
    case 'uploadvideo':
        $config = array(
            "pathFormat" => $CONFIG['videoPathFormat'],
           // "maxSize" => $CONFIG['videoMaxSize'],
           // "allowFiles" => $CONFIG['videoAllowFiles']
		   "maxSize" => $filemaxsize,
           "allowFiles" => $file_type
        );
        $fieldName = $CONFIG['videoFieldName'];
        break;
    case 'uploadfile':
    default:
        $config = array(
            "pathFormat" => $CONFIG['filePathFormat'],
           // "maxSize" => $CONFIG['fileMaxSize'],
           // "allowFiles" => $CONFIG['fileAllowFiles']
		    "maxSize" => $filemaxsize,
           "allowFiles" => $file_type
        );
        $fieldName = $CONFIG['fileFieldName'];
        break;
}

/* 生成上传实例对象并完成上传 */
$up = new Uploader($fieldName, $config, $base64);

/**
 * 得到上传文件所对应的各个参数,数组结构
 * array(
 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
 *     "url" => "",            //返回的地址
 *     "title" => "",          //新文件名
 *     "original" => "",       //原始文件名
 *     "type" => ""            //文件类型
 *     "size" => "",           //文件大小
 * )
 */

/* 返回数据 */
return json_encode($up->getFileInfo());
