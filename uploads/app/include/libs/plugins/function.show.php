<?php
function smarty_function_show($paramer,$template){
	global $views,$phpyun,$config;;
	$rows=$views->obj->DB_select_all("company_show","`uid`='".$_GET['id']."'");
	
	if($rows){
		foreach ($rows as $k=>$v){
			if(!$v['picurl'] || !file_exists(str_replace("./",APP_PATH,$v['picurl']))){
				$rows[$k]['picurl']='';
				unset($rows[$k]);
			}
		}
	}
    $template->tpl_vars['show'] = new Smarty_Variable;
    $template->tpl_vars['show']->value=$rows;  
	return;
}
?>