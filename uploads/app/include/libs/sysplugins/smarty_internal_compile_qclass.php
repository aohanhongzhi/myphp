<?php
class Smarty_Internal_Compile_Qclass extends Smarty_Internal_CompileBase{
	
    public $required_attributes = array('item');
    public $optional_attributes = array('name', 'key', 'classid', 't_len', 'order', 'limit', 'cid', 'recom');
    public $shorttag_order = array('from', 'item', 'key', 'name');
    public function compile($args, $compiler, $parameter){

        $_attr = $this->getAttributes($compiler, $args);

        $from = $_attr['from'];
        $item = $_attr['item'];
        $name = $_attr['name'];
		
        $name=str_replace('\'','',$name);		
        $name=$name?$name:'list';$name='$'.$name;
        if (!strncmp("\$_smarty_tpl->tpl_vars[$item]", $from, strlen($item) + 24)) {
            $compiler->trigger_template_error("item variable {$item} may not be the same variable as at 'from'", $compiler->lex->taglineno);
        }
        
        $OutputStr='global $db,$db_config,$config;eval(\'$paramer='.str_replace('\'','\\\'',ArrayToString($_attr,true)).';\');'.$name.'=array();
		$ParamerArr = GetSmarty($paramer,$_GET);
		$paramer = $ParamerArr[arr];
		$Purl =  $ParamerArr[purl];
        global $ModuleName;
        if(!$Purl["m"]){
            $Purl["m"]=$ModuleName;
        }

		$where=1;
		include(PLUS_PATH.\'/ask.cache.php\');

		
		
		
		if(is_array($ask_type[$paramer[classid]]) && !empty($ask_type[$paramer[classid]]))
		{ 
			$Count = $ask_type[$paramer[classid]]; 
		}else{
			$aList = $db->select_all("q_class","`pid` IN (".@implode(\',\',$ask_index).")","id");
			foreach($aList as $v){
				$aid[] =$v[id]; 
			} 
			$Count = $aid; 
		}
		if(is_array($Count)){ 
			if($_COOKIE[\'uid\']){$atn=$db->DB_select_once("attention","`uid`=\'".$_COOKIE[\'uid\']."\' and `type`=\'2\'","`ids`");} 
			$ids=@explode(\',\',$atn[\'ids\']);
			foreach($Count as $value){
				$QclassInfo=array();
				if(in_array($value,$ids)){
					$QclassInfo[\'isatn\']=\'1\';
				}
				$QclassInfo[\'id\'] = $value;
				$QclassInfo[\'name\'] = $ask_name[$value];
				$QclassInfo[\'pic\'] = $ask_pic[$value];
				$QclassInfo[\'atnnum\'] = $ask_atnnum[$value];
				$QclassInfo[\'qusnum\'] = $ask_qusnum[$value];
				$QclassInfo[\'intro\'] = strip_tags($ask_intro[$value]);
				$QclassInfo[\'list\'] = $QusList[$value];				
				'.$name.'[] = $QclassInfo;
			}
			
		}
		';
        
        global $DiyTagOutputStr;
        $DiyTagOutputStr[]=$OutputStr;
        return SmartyOutputStr($this,$compiler,$_attr,'qclass',$name,'',$name);
    }
}
class Smarty_Internal_Compile_Qclasselse extends Smarty_Internal_CompileBase{
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);

        list($openTag, $nocache, $item, $key) = $this->closeTag($compiler, array('qclass'));
        $this->openTag($compiler, 'qclasselse', array('qclasselse', $nocache, $item, $key));

        return "<?php }\nif (!\$_smarty_tpl->tpl_vars[$item]->_loop) {\n?>";
    }
}
class Smarty_Internal_Compile_Qclassclose extends Smarty_Internal_CompileBase{
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }

        list($openTag, $compiler->nocache, $item, $key) = $this->closeTag($compiler, array('qclass', 'qclassclose'));

        return "<?php } ?>";
    }
}
