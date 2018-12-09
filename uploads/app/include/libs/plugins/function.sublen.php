<?php
function smarty_function_sublen($paramer,$template){
	extract($params);
	if($html==""){
        $str=strip_tags(html_entity_decode(str_replace(array("&amp;","&nbsp;"," "),array("&","",""),$str),ENT_QUOTES));
	}
	$length=$length?$length:20;
	$charset=$charset?$charset:"utf-8";
	return mb_substr($str,0,$length,$charset);
}
?>