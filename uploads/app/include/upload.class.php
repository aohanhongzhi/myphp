<?php
/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2018 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class Upload {
	var $imgname='';
	var $previewname='';			
	var $upfiledir='/upload';
	var $maxsize='10240';				
	var $addpreview=true;			
	var $addwatermark=false;		
	var $watertype='img';			
    var $waterimg='/images/logo.png';
	var $waterstring='www.phpyun.com';	
	var $ttf='';		
	var $alpha='50';				
    var $position=1;				
	var $destination_folder;        
	var $errorType; 
	var $pic_type;
	function Upload($paras=array()){
		if(count($paras)==false){$paras=array('maxsize'=>'10kb','addwatermark'=>true,'watertype'=>'txt/img','waterimg'=>'images/logo.png','waterstring'=>'','ttf'=>'','position'=>'1/2/3/4');}
		foreach($paras as $key => $value){
			$key=strtolower($key);
			$this->$key=$value;
		}
		$this->upfiledir=rtrim($this->upfiledir,'/');
		$this->maxsize=intval($this->maxsize);

		
		$this->errorType=0;
	}
	
	function picture($file,$index=false,$type=''){
		if($index===false){
			if($type){
				$imageInfo	= $this->getInfo($file);
				$fileName=$imageInfo["name"];
				$filetmpname=$file;
			}else{
				$fileName=$file["name"];
				$filetmpname=$file["tmp_name"];
			}
		}else{
			$fileName=$file["name"][$index];
			$filetmpname=$file["tmp_name"][$index];
		}
		$uptypes=$this->pic_type;
		$nameArr=@explode(".",$fileName);
		$num=count($nameArr)-1;
		$this->destination_folder=$this->upfiledir.'/'.date('Ymd').'/'; 
		if(!file_exists($this->upfiledir)){
			@mkdir($this->upfiledir,0777,true);
		}
		if(!file_exists($this->destination_folder)){
			@mkdir($this->destination_folder,0777,true);
		}
		$imgpreviewsize=1/2;    
		if(@filesize($filetmpname) > ($this->maxsize*1024)){
			return $this->errorType=1;
   		 }
		
		 if(!in_array(strtolower($nameArr[$num]),$uptypes)){
			return $this->errorType=2;
		}
		if(strrpos($nameArr['0'],"<?php")){
			return $this->errorType=6;
		}
		
		
		list($width,$height,$type,$attr) =getimagesize($filetmpname);
		switch ($type)
		{
			case 1:	
				$img = imagecreatefromgif($filetmpname);
				break;
			case 2:	
				$img = imagecreatefromjpeg($filetmpname);
				break;
			case 3:	
				$img = imagecreatefrompng($filetmpname);
				break;
			default:
				return 0;
				break;
		}
		if (!$img){
			
			return $this->errorType=6;
		}
		if(!$this->addpreview && $this->is_picself=='1'){
			
			$image_data = fread(fopen($filetmpname, 'r'), filesize($filetmpname));

			if(strpos($image_data,'<?php')!==false){

				$this->addpreview = true;
			}
		}

		$pinfo=pathinfo($fileName);
		$ftype=$pinfo['extension'];
		$destination =$this->destination_folder.$this->generateImgName($ftype);
		if($type){
			if(!copy($filetmpname,$destination)){
				return $this->errorType=5;
			}
		}else{
			if (file_exists($destination)){
				return $this->errorType=3;
			}
			if(!move_uploaded_file($filetmpname,$destination)){
				return $this->errorType=4;
			}
		}
		$this->imgname=$destination;
		if($this->addpreview){
			
			$this->imgname = $this->makeThumb($this->imgname,$width,$height,'',$this->addpreview);
		}
		if($this->addwatermark){
			$this->makewatermark($this->imgname);
		}
		return ($this->imgname);
	}

	function imageBase($data){

		preg_match('/^(data:\s*image\/(\w+);base64,)/', $data, $result);
		
		$uimage=str_replace($result[1], '', str_replace('#','+',$data));
		
		if(in_array(strtolower($result[2]),array('jpg','png','gif','jpeg'))){
			$new_file = time().rand(1000,9999).".".$result[2];
		}else{
 			$new_file = time().rand(1000,9999).".jpg";
		}
		
		$im = imagecreatefromstring(base64_decode($uimage));
		
		if ($im !== false) {
			
			$this->destination_folder=$this->upfiledir.'/'.date('Ymd').'/'; 
			if(!file_exists($this->upfiledir)){
				@mkdir($this->upfiledir,0777,true);
			}
			if(!file_exists($this->destination_folder)){
				@mkdir($this->destination_folder,0777,true);
			}

			$destination =$this->destination_folder.$new_file;

			$re=file_put_contents($destination, base64_decode($uimage));

			chmod($destination,0777);

			$this->imgname=$destination;

			if($re){

				if(!$this->addpreview && $this->is_picself=='1'){
					
					$image_data = fread(fopen($this->imgname, 'r'), filesize($this->imgname));

					if(strpos($image_data,'<?php')!==false){

						$this->addpreview = true;
					}
				}
				if($this->addpreview){
					list($width,$height,$type,$attr) =getimagesize($this->imgname);
					$this->imgname = $this->makeThumb($this->imgname,$width,$height,'',$this->addpreview);
				}
			}else{
				return $this->errorType=4;
			}
		}else{
			return $this->errorType=6;
		}
		
		
		return $this->imgname;
		 
	}
	
	function makewatermark($destination){
        switch($this->watertype){
            case 'txt':   
           
		 	return $this->waterMarktxt($destination);
            break;
            case 'img':   
            return $this->waterMarkimg($destination);
		break;
        }
	}
	
	
	
	
	
	
	
	
	function waterMarkimg($destination)
	{
		$image_size = getimagesize($destination);
	    $iinfo=getimagesize($destination,$iinfo);
        $iinfo2=getimagesize($this->waterimg,$iinfo2);
        $nimage=imagecreatetruecolor($image_size[0],$image_size[1]);
        $white=imagecolorallocate($nimage,255,255,255);
        $black=imagecolorallocate($nimage,0,0,0);
        $red=imagecolorallocate($nimage,255,0,0);
        imagefill($nimage,0,0,$white);
        switch ($iinfo[2])
        {
            case 1:
            $simage =imagecreatefromgif($destination);
            break;
            case 2:
            $simage =imagecreatefromjpeg($destination);
            break;
            case 3:
            $simage =imagecreatefrompng($destination);
            break;
            case 6:
            $simage =imagecreatefromwbmp($destination);
            break;
            default:$this->errorType=5;
            return;
        }
        imagecopy($nimage,$simage,0,0,0,0,$image_size[0],$image_size[1]);
       
           switch ($iinfo2[2]){
            case 1:
            $simage1 =imagecreatefromgif($this->waterimg);
            break;
            case 2:
            $simage1 =imagecreatefromjpeg($this->waterimg);
            break;
            case 3:
            $simage1 =imagecreatefrompng($this->waterimg);
            break;
            case 6:
            $simage1 =imagecreatefromwbmp($this->waterimg);
            break;
            default:$this->errorType=6;
            return;
        }
		$gifsize=getimagesize($this->waterimg);
       switch($this->position){
		  case 1:// 上左
          imagecopy($nimage,$simage1,0,0,0,0,$gifsize[0],$gifsize[1]); 
          break;
		  case 2:// 上中
          imagecopy($nimage,$simage1,($image_size[0]-$gifsize[0])/2,0,0,0,$gifsize[0],$gifsize[1]); 
          break;
		  case 3:// 右上
		  imagecopy($nimage,$simage1,$image_size[0]-$gifsize[0],0,0,0,$gifsize[0],$gifsize[1]);
		  break;
		  case 4:// 中左
          imagecopy($nimage,$simage1,0,($image_size[1]-$gifsize[1])/2,0,0,$gifsize[0],$gifsize[1]); 
          break;
		  case 5:// 居中
		  imagecopy($nimage,$simage1,($image_size[0]-$gifsize[0])/2, ($image_size[1]-$gifsize[1])/2,0,0,$gifsize[0],$gifsize[1]);
		  break;
		  case 6:// 中右
          imagecopy($nimage,$simage1,$image_size[0]-$gifsize[0],($image_size[1]-$gifsize[1])/2,0,0,$gifsize[0],$gifsize[1]); 
          break;
          case 9:// 右下
          imagecopy($nimage,$simage1,$image_size[0]-$gifsize[0], $image_size[1]-$gifsize[1],0,0,$gifsize[0],$gifsize[1]);
          break;
		  case 7:// 左下
          imagecopy($nimage,$simage1,0,$image_size[1]-$gifsize[1],0,0,$gifsize[0],$gifsize[1]); 
          break;
		  case 8:// 底中
          imagecopy($nimage,$simage1,($image_size[0]-$gifsize[0])/2,$image_size[1]-$gifsize[1],0,0,$gifsize[0],$gifsize[1]); 
          break;
        }
       imagedestroy($simage1);
       switch ($iinfo[2]){
            case 1:
            imagejpeg($nimage, $destination);
            break;
            case 2:
            imagejpeg($nimage, $destination);
            break;
            case 3:
            imagepng($nimage, $destination);
            break;
            case 6:
            imagewbmp($nimage, $destination);
            break;
        }
        
        imagedestroy($nimage);
        imagedestroy($simage);
        return $destination;
	}
	
	
	
	
	
	
	
	
	function waterMarktxt($sourFile){
		 $maxWidth  = 300;			
		 $maxHeight = 300;			
		 $toFile	= true;			
		$imageInfo	= $this->getInfo($sourFile);
		switch ($imageInfo["type"])
		{
			case 1:	
			$newName	= substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".gif";
				break;
			case 2:	
			$newName	= substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".jpg";
				break;
			case 3:	
			$newName	= substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".png";
				break;
			default:
				return 0;
				break;
		}
		switch ($imageInfo["type"])
		{
			case 1:	
				$img = imagecreatefromgif($sourFile);
				break;
			case 2:	
				$img = imagecreatefromjpeg($sourFile);
				break;
			case 3:	
				$img = imagecreatefrompng($sourFile);
				break;
			default:
				return 0;
				break;
		}
		if (!$img) {
			return 0;
		}
		$width  = ($maxWidth > $imageInfo["width"]) ? $imageInfo["width"] : $maxWidth;
		$height = ($maxHeight > $imageInfo["height"]) ? $imageInfo["height"] : $maxHeight;
		$srcW	= $imageInfo["width"];
		$srcH	= $imageInfo["height"];
		if ($srcW * $width > $srcH * $height)
			$height = round($srcH * $width / $srcW);
		else
			$width = round($srcW * $height / $srcH);
		
		if (function_exists("imagecreatetruecolor")) 
		{
			$new = imagecreatetruecolor($width, $height);
			if($imageInfo["type"]==1 || $imageInfo["type"]==3){

				$color = imagecolorallocate($new,255,255,255);
				imagecolortransparent($new,$color);
				imagefill($new,0,0,$color);
			}
			imagecopyresampled($new, $img, 0, 0, 0, 0, $width, $height, $imageInfo["width"], $imageInfo["height"]);
		}
		else
		{
			$new = imagecreate($width, $height);
			if($imageInfo["type"]==1 || $imageInfo["type"]==3){

				$color = imagecolorallocate($new,255,255,255);
				imagecolortransparent($new,$color);
				imagefill($new,0,0,$color);
			}
			imagecopyresized($new, $img, 0, 0, 0, 0, $width, $height, $imageInfo["width"], $imageInfo["height"]);
		}
		$black = imagecolorallocate($new, 0, 0, 0);
		
		$alpha = imagecolorallocatealpha($new, 230, 230, 230,$this->alpha);
		
		imagefilledrectangle($new, 0, $height-26, $width, $height, $alpha);
		$white = imagecolorallocate ($new, 0, 0, 0);
  		imagettftext ($new, 10, 0, 20, $height-8,$white, $this->ttf, $this->waterstring);
		
        if ($toFile)
		{
			if (file_exists($this->destination_folder.$newName))
			 @unlink($this->destination_folder.$newName);
			imagejpeg($new, $this->destination_folder.$newName);
			imagedestroy($new);
			imagedestroy($img);
			return $this->destination_folder.$newName;
		}
		else
		{
			imagejpeg($new);
			imagedestroy($new);
			imagedestroy($img);
		}
	}
    
	
	
	
	
	
	function getInfo($file)
	{
		
		$data=getimagesize($file);
		$imageInfo["width"]	= $data[0];
		$imageInfo["height"]= $data[1];
		$imageInfo["type"]	= $data[2];
		$imageInfo["name"]	= basename($file);
		$imageInfo["size"]  = filesize($file);
		return $imageInfo;
	}
	
	
	
	
	
	
	
	
	function makeThumb($sourFile,$width=100,$height=100,$newNamePre='',$addpreview=false)
	{
		
		$imageInfo	= $this->getInfo($sourFile);
		switch ($imageInfo["type"])
		{
			case 1:	
			$newName	='make'.$newNamePre.substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".gif";
				break;
			case 2:	
			$newName	='make'.$newNamePre.substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".jpg";
				break;
			case 3:	
			$newName	='make'.$newNamePre.substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".png";
				break;
			default:
				return 0;
				break;
		}
		switch ($imageInfo["type"])
		{
			case 1:	
				$img = imagecreatefromgif($sourFile);
				break;
			case 2:	
				$img = imagecreatefromjpeg($sourFile);
				break;
			case 3:	
				$img = imagecreatefrompng($sourFile);
				break;
			default:
				return 0;
				break;
		}
		if (!$img){

			return 0;
		}
		
		$srcW	= $imageInfo["width"];
		$srcH	= $imageInfo["height"];
		
		if(floor($srcW/$srcH) >= 1){
		    $width  = ($width > $imageInfo["width"]) ? $imageInfo["width"] : $width;
		    $height=round($srcH*$width/$srcW);
		    if ($height>800){
		        $height = 800;
		        $width=round($srcW*$height/$srcH);
		    }
		}else{
		    $height = ($height > $imageInfo["height"]) ? $imageInfo["height"] : $height;
		    if ($height>800){
		        $height = 800;
		    }
		    $width=round($srcW*$height/$srcH);
		}
		
		if (function_exists("imagecreatetruecolor")) 
		{
			
			$new = imagecreatetruecolor($width, $height);
			if($imageInfo["type"]==1 || $imageInfo["type"]==3){

				$color = imagecolorallocate($new,255,255,255);
				imagecolortransparent($new,$color);
				imagefill($new,0,0,$color);
			}
			ImageCopyResampled($new, $img, 0, 0, 0, 0, $width, $height, $imageInfo["width"], $imageInfo["height"]);
		}
		else
		{
			$new = imagecreate($width, $height);
			if($imageInfo["type"]==1 || $imageInfo["type"]==3){

				$color = imagecolorallocate($new,255,255,255);
				imagecolortransparent($new,$color);
				imagefill($new,0,0,$color);
			}
			ImageCopyResized($new, $img, 0, 0, 0, 0, $width, $height, $imageInfo["width"], $imageInfo["height"]);
		}
		
		if($addpreview){
			$this->previewname=$sourFile;
		}else{
			$this->previewname=$this->destination_folder . $newName;
		}
		

		if (file_exists($this->previewname)){
			unlink($this->previewname);
		}
		
		ImageJPEG($new, $this->previewname,100);
		ImageDestroy($new);
		ImageDestroy($img);

		
		return ($this->previewname);
	}
	function news_makeThumb($sourFile,$width=100,$height=100,$newNamePre=''){
		$imageInfo	= $this->getInfo($sourFile);
		$this->destination_folder=str_replace($imageInfo["name"],"",$sourFile);
		switch ($imageInfo["type"]){
			case 1:	
			$newName	='make'.$newNamePre.substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".gif";
				break;
			case 2:	
			$newName	='make'.$newNamePre.substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".jpg";
				break;
			case 3:	
			$newName	='make'.$newNamePre.substr($imageInfo["name"], 0, strrpos($imageInfo["name"], ".")) . ".png";
				break;
			default:
				return 0;
				break;
		}
		switch ($imageInfo["type"])
		{
			case 1:	
				$img = imagecreatefromgif($sourFile);
				break;
			case 2:	
				$img = imagecreatefromjpeg($sourFile);
				break;
			case 3:	
				$img = imagecreatefrompng($sourFile);
				break;
			default:
				return 0;
				break;
		}
		if (!$img){
			return 0;
		}
		
		$srcW	= $imageInfo["width"];
		$srcH	= $imageInfo["height"];
		
		if(floor($srcW/$srcH) >= 1){
		    $width  = ($width > $imageInfo["width"]) ? $imageInfo["width"] : $width;
		    $height=round($srcH*$width/$srcW);
		    if ($height>800){
		        $height = 800;
		        $width=round($srcW*$height/$srcH);
		    }
		}else{
		    $height = ($height > $imageInfo["height"]) ? $imageInfo["height"] : $height;
		    if ($height>800){
		        $height = 800;
		    }
		    $width=round($srcW*$height/$srcH);
		}
		
		if (function_exists("imagecreatetruecolor")) 
		{
			
			$new = imagecreatetruecolor($width, $height);
			if($imageInfo["type"]==1 || $imageInfo["type"]==3){

				$color = imagecolorallocate($new,255,255,255);
				imagecolortransparent($new,$color);
				imagefill($new,0,0,$color);
			}
			ImageCopyResampled($new, $img, 0, 0, 0, 0, $width, $height, $imageInfo["width"], $imageInfo["height"]);
		}
		else
		{
			$new = imagecreate($width, $height);
			if($imageInfo["type"]==1 || $imageInfo["type"]==3){

				$color = imagecolorallocate($new,255,255,255);
				imagecolortransparent($new,$color);
				imagefill($new,0,0,$color);
			}
			ImageCopyResized($new, $img, 0, 0, 0, 0, $width, $height, $imageInfo["width"], $imageInfo["height"]);
		}
		
		if (file_exists($this->destination_folder . $newName)){
			unlink($this->destination_folder . $newName);
		}
		ImageJPEG($new, $this->destination_folder . $newName,100);
		ImageDestroy($new);
		ImageDestroy($img);
		$this->previewname=$this->destination_folder . $newName;
		return ($this->previewname);
	}
    
	function getimage($name,$maxwidth=800,$maxheight=600){
		list($width,$height,$type,$attr) =getimagesize($name);
        $imgname=$name;
		if($width>$maxwidth){
           $imgname=$this->makeThumb($name,$maxwidth,$maxheight);
           unlink($name);
        }
        return $imgname;
	}
	function generateImgName($ftype){
		$imgname='';
		$microtime=@explode(" ",microtime());
		$imgname=ceil($microtime[0]*10000000)+$microtime[1];
		$imgname.=rand(1,9);
		$imgname.='.'.$ftype;
		return ($imgname);
	}
	
	function movepic($src){
	    $folder = $this->upfiledir.'/'.date('Ymd').'/'; 
	    if(!file_exists($this->upfiledir)){
	        @mkdir($this->upfiledir,0777,true);
	    }
	    if(!file_exists($folder)){
	        @mkdir($folder,0777,true);
	    }
	    $file = file_get_contents($src);
	    $name = trim(strrchr($src, '/'),'/');
	    file_put_contents($folder.$name, $file);
	    return $folder.$name;
	}
}
?>