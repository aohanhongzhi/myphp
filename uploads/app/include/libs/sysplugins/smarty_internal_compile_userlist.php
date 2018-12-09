<?php
class Smarty_Internal_Compile_Userlist extends Smarty_Internal_CompileBase{
	public $required_attributes = array('item');
	public $optional_attributes = array('name', 'key', 'post_len', 'limit', 'city_len', 'salary', 'minsalary', 'maxsalary', 'idcard', 'edu', 'order', 'work', 'exp', 'sex','birthday', 'keyword', 'hy', 'provinceid', 'report', 'cityid', 'three_cityid', 'adtime', 'pic', 'typeids', 'type', 'job1_son', 'job_post', 'uptime', 'ispage', 'rec_resume','where_uid', 'rec', 't_len' ,'top','job_classid','islt','job1','isshow','cityin','jobin','where','topdate','noid','tag');
	public $shorttag_order = array('from', 'item', 'key', 'name');
	public function compile($args, $compiler, $parameter){
		$_attr = $this->getAttributes($compiler, $args);

		$from = $_attr['from'];
		$item = $_attr['item'];
		$name = $_attr['item'];
		$name=str_replace('\'','',$name);
		$name=$name?$name:'list';$name='$'.$name;
		if (!strncmp("\$_smarty_tpl->tpl_vars[$item]", $from, strlen($item) + 24)) {
			$compiler->trigger_template_error("item variable {$item} may not be the same variable as at 'from'", $compiler->lex->taglineno);
		}

		
		$OutputStr=''.$name.'=array();global $db,$db_config,$config;
		if(is_array($_GET)){
			foreach($_GET as $key=>$value){
				if($value==\'0\'){
					unset($_GET[$key]);
				}
			}
		}
		eval(\'$paramer='.str_replace('\'','\\\'',ArrayToString($_attr,true)).';\');
		$ParamerArr = GetSmarty($paramer,$_GET,$_smarty_tpl);
		$paramer = $ParamerArr[arr];
		$Purl =  $ParamerArr[purl];
        global $ModuleName;
        if(!$Purl["m"]){
            $Purl["m"]=$ModuleName;
        }

	    
		$cache_array = $db->cacheget();
        $fscache_array = $db->fscacheget();
		$userclass_name = $cache_array["user_classname"];
		$city_name      = $cache_array["city_name"];
        $city_index     = $cache_array["city_index"];
		$job_name		= $cache_array["job_name"];
        $job_index		= $cache_array["job_index"];
		$job_type		= $cache_array["job_type"];
		$industry_name	= $cache_array["industry_name"];
        $city_two       = $fscache_array["city_two"];
        $city_three     = $fscache_array["city_three"];
        $job_two		= $fscache_array["job_two"];
        $job_three		= $fscache_array["job_three"];

		
		if($config[\'sy_web_site\']=="1"){
			if($config[province]>0 && $config[province]!=""){
				$paramer[provinceid] = $config[province];
			}
			if($config[\'cityid\']>0 && $config[\'cityid\']!=""){
				$paramer[\'cityid\']=$config[\'cityid\'];
			}
			if($config[\'three_cityid\']>0 && $config[\'three_cityid\']!=""){
				$paramer[\'three_cityid\']=$config[\'three_cityid\'];
			}
			if($config[\'hyclass\']>0 && $config[\'hyclass\']!=""){
				$paramer[\'hy\']=$config[\'hyclass\'];
			}
		}

		
			$where = "a.`defaults`=\'1\' and a.`status`=\'1\' and a.`r_status`=\'1\'";
            
			if($paramer[where_uid]){
				$where .=" AND a.`uid` in (".$paramer[\'where_uid\'].")";
			}
			
			if($_COOKIE[\'uid\']&&$_COOKIE[\'usertype\']=="2"){
				$blacklist=$db->select_all("blacklist","`p_uid`=\'".$_COOKIE[\'uid\']."\'","c_uid");
				if(is_array($blacklist)&&$blacklist){
					foreach($blacklist as $v){
						$buid[]=$v[\'c_uid\'];
					}
				    $where .=" AND a.`uid` NOT IN (".@implode(",",$buid).")";
				}
			}
            
			if($paramer[topdate]){
				$where .=" AND a.`topdate`>\'".time()."\'";
			}
            
			if($paramer[idcard]){
				$where .=" AND a.`idcard_status`=\'1\'";
			}
			if($paramer[rec]){
				$where .=" AND a.`rec`=1";
			}
			
			if($paramer[rec_resume]){
				$where .=" AND a.`rec_resume`=1";
			}
			
			if($paramer[work]){
				$show=$db->select_all("resume_show","1 group by eid","`eid`");
				if(is_array($show)){
					foreach($show as $v){
						$eid[]=$v[\'eid\'];
					}
				}
				$where .=" AND a.`id` in (".@implode(",",$eid).")";
			}
			
			if($paramer[tag]){
			    $tagname=$userclass_name[$paramer[tag]];
				$tag=$db->select_all("resume","`def_job`>0 and `r_status`<>2 and `status`=1 and FIND_IN_SET(\'".$tagname."\',`tag`)","`def_job`");
				if(is_array($tag)){
					foreach($tag as $v){
						$tagid[]=$v[\'def_job\'];
					}
				}
				$where .=" AND a.`id` in (".@implode(",",$tagid).")";
			}
			
			if($paramer[uptime]){
				if($paramer[uptime]==1){
					$beginToday=mktime(0,0,0,date(\'m\'),date(\'d\'),date(\'Y\'));
	    			$where.=" AND a.`lastupdate`>$beginToday";
	    		}else{
	    			$time=time();
					$uptime = $time-($paramer[uptime]*86400);
					$where.=" AND a.`lastupdate`>$uptime";
	    		}
			}
			
			if($paramer[adtime]){
				$time=time();
				$adtime = $time-($paramer[adtime]*86400);
				$where.=" AND a.`status_time`>$adtime";
			}
			
			if($paramer[pic]=="0" || $paramer[pic]){
				$where .=" AND a.`photo`<>\'\' AND `phototype`!=1";
			}
            
			if($paramer[\'hy\']!=""){
				$where .= " AND a.`hy` IN (".$paramer[\'hy\'].")";
			}
            $citywhere = "1";
			
			if($paramer[provinceid]){
                $citywhere .= " AND `provinceid` = $paramer[provinceid]";
			}
			
			if($paramer[cityid]){
                $citywhere .= " AND `cityid` = $paramer[cityid]";
			}
			
			if($paramer[three_cityid]){
                $citywhere .= " AND `three_cityid` = $paramer[three_cityid]";
			}
			
			if($paramer[cityin]){
                $cityin = @explode(",",$paramer[cityin]);
                if(count($cityin)>1){
                    $citywhere .= " AND(`provinceid` IN ($paramer[cityin]) OR `cityid` IN ($paramer[cityin]) OR `three_cityid` IN ($paramer[cityin]))";
                }elseif(count($cityin)==1){
                    if(in_array($paramer[cityin],$city_index)){
                        $citywhere .= " AND `provinceid` = $paramer[cityin]";
                    }elseif(in_array($paramer[cityin],$city_two)){
                        $citywhere .= " AND `cityid` = $paramer[cityin]";
                    }elseif(in_array($paramer[cityin],$city_three)){
                        $citywhere .= " AND `three_cityid` = $paramer[cityin]";
                    }
                }
			}
            
            $jobwhere = "1";
			if($paramer[job1]){
				$jobwhere.=" AND `job1`= $paramer[job1]";
			}
			if($paramer[job1_son]){
                $jobwhere.=" AND `job1_son`= $paramer[job1_son]";
			}
			if($paramer[job_post]){
                $jobwhere.=" AND `job_post`= $paramer[job_post]";
			}
            
			if($paramer[jobin]){
                $jobin = @explode(",",$paramer[jobin]);
                if(count($jobin)>1){
                    $jobwhere .= " AND(`job1` IN ($paramer[jobin]) OR `job1_son` IN ($paramer[jobin]) OR `job_post` IN ($paramer[jobin]))";
                }elseif(count($jobin)==1){
                    if(in_array($paramer[jobin],$job_index)){
                        $jobwhere .= " AND `job1` = $paramer[jobin]";
                    }elseif(in_array($paramer[jobin],$job_two)){
                        $jobwhere .= " AND `job1_son` = $paramer[jobin]";
                    }elseif(in_array($paramer[jobin],$job_three)){
                        $jobwhere .= " AND `job_post` = $paramer[jobin]";
                    }
                }
			}
			
			if($paramer[exp]){
				$where .=" AND a.`exp`=$paramer[exp]";
			}
			
			if($paramer[edu]){
				$where .=" AND a.`edu`=$paramer[edu]";
			}
			
			if($paramer[sex]){
				$where .=" AND a.`sex`=$paramer[sex]";
			}
			
			if($paramer[report]){
				$where .=" AND a.`report`=$paramer[report]";
			}
			
			if($paramer[type]){
				$where .= " AND a.`type`=$paramer[type]";
			}
			
			if($paramer[keyword]){
				$jobid = array();
				$where1[]="a.`name` LIKE \'%$paramer[keyword]%\'";
				$where1[]="a.`uname` LIKE \'%$paramer[keyword]%\'";
                
			    $cityid=array();
				foreach($city_name as $k=>$v){
					if(strpos($v,$paramer[keyword])!==false){
						$cityid[]=$k;
					}
				}
                
				if(!empty($cityid)){
                    $ckwhere = "1";
                    if(in_array($cityid[0],$city_index)){
                        $ppcityid = $cityid[0];
                        $ckwhere .= " AND `provinceid` = $cityid[0]";
                    }elseif(in_array($cityid[0],$city_two)){
                        $ppcityid = $cityid[0];
                        $ckwhere .= " AND `cityid` = $cityid[0]";
                    }elseif(in_array($cityid[0],$city_three)){
                        $ppcityid = $cityid[0];
                        $ckwhere .= " AND `three_cityid` = $cityid[0]";
                    }
                    $cityresume = $db->select_all("resume_city",$ckwhere);
                    if($cityresume){
                        foreach ($cityresume as $v){
                            $where1[]=" `a.id`=".$v[\'eid\'];
                        }
                    }
				}
                $where.=" AND (".@implode(" or ",$where1).")";
			}
	
			
			if($paramer[minsalary]&&$paramer[maxsalary]){
				$where.= " AND ((a.`minsalary`<=".intval($paramer[minsalary])." and a.`maxsalary`>=".intval($paramer[minsalary]).") 
							or (a.`minsalary`<=".intval($paramer[maxsalary])." and a.`maxsalary`>=".intval($paramer[maxsalary])."))";
			}elseif($paramer[minsalary]&&!$paramer[maxsalary]){
				$where.= " AND ((a.`minsalary`<=".intval($paramer[minsalary])." and a.`maxsalary`>=".intval($paramer[minsalary]).") 
							or (a.`minsalary`>=".intval($paramer[minsalary])." and a.`maxsalary`>=".intval($paramer[minsalary]).")
							or (a.`minsalary`!=0 and  a.`maxsalary`=0))";
			}elseif(!$paramer[minsalary]&&$paramer[maxsalary]){
				$where.= " AND ((a.`minsalary`<=".intval($paramer[maxsalary])." and a.`maxsalary`>=".intval($paramer[maxsalary]).")
							or (a.`minsalary`<=".intval($paramer[maxsalary])." and a.`maxsalary`<=".intval($paramer[maxsalary]).")  
							or (a.`minsalary`<=".intval($paramer[maxsalary])." and a.`maxsalary`=0) 
							or (a.`minsalary`=0 and a.`maxsalary`!=0)
							)";
			}
	        
			if($paramer[order] && $paramer[order]!="lastdate"){
				if($paramer[order]==\'topdate\'){
					$nowtime=time();
					$order = " ORDER BY if(a.`topdate`>$nowtime,a.`topdate`,a.`lastupdate`)";
				}else{
					$order = " ORDER BY a.`".$paramer[order]."`";
				}
			}else{
				$order = " ORDER BY a.`lastupdate` ";
			}
			
			$sort = $paramer[sort]?$paramer[sort]:\'DESC\';
			
			if($paramer[limit]){
				$limit=" LIMIT ".$paramer[limit];
			}
			
			if($paramer[where]){
				$where = $paramer[where];
 			}
            $pagewhere = "";$joinwhere = "";
            if($citywhere!="1"){
                $pagewhere.=" ,(select `eid` from `".$db_config[def]."resume_cityclass` where ".$citywhere." group by `eid`) b";
                $joinwhere .= " a.`id`=b.`eid` and ";
            }
            if($jobwhere!="1"){
                $pagewhere.=" ,(select `eid` from `".$db_config[def]."resume_jobclass` where ".$jobwhere." group by `eid`) c";
                $joinwhere .= " a.`id`=c.`eid` and ";
            }
			if($paramer[ispage]){
				$limit = PageNav($paramer,$_GET,"resume_expect",$where,$Purl,"",\'0\',$_smarty_tpl,$pagewhere,$joinwhere);
				
			}
		
		
		$select="a.`id`,a.`uid`,a.`name`,a.`hy`,a.`job_classid`,a.`city_classid`,a.`jobstatus`,a.`type`,a.`report`,a.`lastupdate`,a.`rec`,a.`top`,a.`topdate`,a.`rec_resume`,a.`ctime`,a.`uname`,a.`idcard_status`,a.`minsalary`,a.`maxsalary`";
		if($pagewhere!=""){
			$sql = "select ".$select." from `".$db_config[def]."resume_expect` a ".$pagewhere." where ".$joinwhere.$where.$order.$sort.$limit;
			'.$name.'=$db->DB_query_all($sql,"all");
		}else{
			$sql = "select ".$select." from `".$db_config[def]."resume_expect` a where ".$where.$order.$sort.$limit;
			'.$name.'=$db->DB_query_all($sql,"all");
		}
      
        include(CONFIG_PATH."db.data.php");		
		if('.$name.' && is_array('.$name.')){
			
			
			if($paramer[\'top\']){
				$uids=$m_name=array();
				foreach('.$name.' as $k=>$v){
					$uids[]=$v[uid];
				}

				$member=$db->select_all($db_config[def]."member","`uid` in(".@implode(\',\',$uids).")","uid,username");
				foreach($member as $val){
					$m_name[$val[uid]]=$val[\'username\'];
				}
			}
			foreach('.$name.' as $key=>$value){
				$uid[] = $value[\'uid\'];
				$eid[] = $value[\'id\'];
			}
			$eids = @implode(\',\',$eid);
			$uids = @implode(\',\',$uid);
            $resume=$db->select_all("resume","`uid` in(".$uids.")","uid,name,nametype,tag,sex,moblie_status,edu,exp,photo,phototype,birthday");
			if($paramer[topdate]){
				$noids=array();
			}	
			foreach('.$name.' as $k=>$v){
				if($paramer[topdate]){
					$noids[] = $v[id];
				}
				
				if($paramer[noid]==\'1\' && !empty($noids) && in_array($v[\'id\'],$noids)){
					unset('.$name.'[$k]);
					continue;
				}
			    foreach($resume as $val){
			        if($v[\'uid\']==$val[\'uid\']){
                        '.$name.'[$k][\'where\']=$citywhere;
			    		'.$name.'[$k][\'edu_n\']=$userclass_name[$val[\'edu\']];
				        '.$name.'[$k][\'exp_n\']=$userclass_name[$val[\'exp\']];
			            if($val[\'birthday\']){
							$year = date("Y",strtotime($val[\'birthday\']));
							'.$name.'[$k][\'age\'] =date("Y")-$year;
						}
						if($val[\'sex\']==152){
							$val[\'sex\']=\'1\';
						}elseif ($val[\'sex\']==153){
							$val[\'sex\']=\'2\';
						}
						'.$name.'[$k][\'sex\'] =$arr_data[sex][$val[\'sex\']];
		                '.$name.'[$k][\'phototype\']=$val[phototype];
						if($config[\'user_pic\']==1 || !$config[\'user_pic\']){
		                if($val[\'photo\'] && $val[\'phototype\']!=1&&(file_exists(str_replace($config[\'sy_weburl\'],APP_PATH,\'.\'.$val[\'photo\']))||file_exists(str_replace($config[\'sy_weburl\'],APP_PATH,$val[\'photo\'])))){
            				'.$name.'[$k][\'photo\']=str_replace("./",$config[\'sy_weburl\']."/",$val[\'photo\']);
            			}else{
            				if($val[\'sex\']==1){
            					'.$name.'[$k][\'photo\']=$config[\'sy_weburl\']."/".$config[\'sy_member_icon\'];
            				}else{
            					'.$name.'[$k][\'photo\']=$config[\'sy_weburl\']."/".$config[\'sy_member_iconv\'];
            				}
            			}
						}elseif($config[\'user_pic\']==2){
							if($val[\'photo\']&&(file_exists(str_replace($config[\'sy_weburl\'],APP_PATH,\'.\'.$val[\'photo\']))||file_exists(str_replace($config[\'sy_weburl\'],APP_PATH,$val[\'photo\'])))){
								'.$name.'[$k][\'photo\']=str_replace("./",$config[\'sy_weburl\']."/",$val[\'photo\']);
							}else{
								if($val[\'sex\']==1){
									'.$name.'[$k][\'photo\']=$config[\'sy_weburl\']."/".$config[\'sy_member_icon\'];
								}else{
									'.$name.'[$k][\'photo\']=$config[\'sy_weburl\']."/".$config[\'sy_member_iconv\'];
								}
							}
						}elseif($config[\'user_pic\']==3){
							if($val[\'sex\']==1){
								'.$name.'[$k][\'photo\']=$config[\'sy_weburl\']."/".$config[\'sy_member_icon\'];
							}else{
								'.$name.'[$k][\'photo\']=$config[\'sy_weburl\']."/".$config[\'sy_member_iconv\'];
							}
						}
						if($val[\'tag\']){
                            '.$name.'[$k][\'tag\']=explode(\',\',$val[\'tag\']);
	                    }
                        '.$name.'[$k][\'nametype\']=$val[nametype];
                        '.$name.'[$k][\'moblie_status\']=$val[moblie_status];
                        
						if($config[\'user_name\']==1 || !$config[\'user_name\']){
    						if($val[\'nametype\']==3){
    						    if($val[\'sex\']==1){
    						        '.$name.'[$k][\'username_n\'] = mb_substr($val[\'name\'],0,1,\'utf-8\')."先生";
    						    }else{
    						        '.$name.'[$k][\'username_n\'] = mb_substr($val[\'name\'],0,1,\'utf-8\')."女士";
    						    }
    						}elseif($val[\'nametype\']==2){
    						    '.$name.'[$k][\'username_n\'] = "NO.".$v[\'id\'];
    						}else{
    							'.$name.'[$k][\'username_n\'] = $val[\'name\'];
    						}
						}elseif($config[\'user_name\']==3){
							if($val[\'sex\']==1){
								'.$name.'[$k][\'username_n\'] = mb_substr($val[\'name\'],0,1,\'utf-8\')."先生";
							}else{
								'.$name.'[$k][\'username_n\'] = mb_substr($val[\'name\'],0,1,\'utf-8\')."女士";
							}
						}elseif($config[\'user_name\']==2){
							'.$name.'[$k][\'username_n\'] = "NO.".$v[\'id\'];
						}elseif($config[\'user_name\']==4){
							'.$name.'[$k][\'username_n\'] = $val[\'name\'];
						}
                    }
                }
				
				
				$time=$v[\'lastupdate\'];
				
				$beginToday=mktime(0,0,0,date(\'m\'),date(\'d\'),date(\'Y\'));
				
				$beginYesterday=mktime(0,0,0,date(\'m\'),date(\'d\')-1,date(\'Y\'));
				
				$week=strtotime(date("Y-m-d",strtotime("-1 week")));
				if($time>$week && $time<$beginYesterday){
					'.$name.'[$k][\'time\'] = "一周内";
				}elseif($time>$beginYesterday && $time<$beginToday){
					'.$name.'[$k][\'time\'] = "昨天";
				}elseif($time>$beginToday){
					'.$name.'[$k][\'time\'] = date("H:i",$v[\'lastupdate\']);
					'.$name.'[$k][\'redtime\'] =1;
				}else{
					'.$name.'[$k][\'time\'] = date("Y-m-d",$v[\'lastupdate\']);
				} 
				
				'.$name.'[$k][\'user_jobstatus_n\']=$userclass_name[$v[\'jobstatus\']];



				if($v[\'minsalary\']&&$v[\'maxsalary\']){
					'.$name.'[$k]["salary_n"] = "￥".$v[\'minsalary\']."-".$v[\'maxsalary\'];    
                }else if($v[\'minsalary\']){
                    '.$name.'[$k]["salary_n"] = "￥".$v[\'minsalary\']."以上";  
                }else{
    				'.$name.'[$k]["salary_n"] = "面议";
    			}
				'.$name.'[$k][\'report_n\']=$userclass_name[$v[\'report\']];
				'.$name.'[$k][\'type_n\']=$userclass_name[$v[\'type\']];
				'.$name.'[$k][\'lastupdate\']=date("Y-m-d",$v[\'lastupdate\']);
					
				'.$name.'[$k][\'user_url\']=Url("resume",array("c"=>"show","id"=>$v[\'id\']),"1");
				'.$name.'[$k]["hy_info"]=$industry_name[$v[\'hy\']];
				if($paramer[\'top\']){
					'.$name.'[$k][\'m_name\']=$m_name[$v[\'uid\']];
					'.$name.'[$k][\'user_url\']=Url("ask",array("c"=>"friend","a"=>"myquestion","uid"=>$v[\'uid\']));
				}
				$kjob_classid=@explode(",",$v[\'job_classid\']);
				$kjob_classid=array_unique($kjob_classid);	
				$jobname=array();
				if(is_array($kjob_classid)){
					foreach($kjob_classid as $val){
					    if($val!=\'\'){
					        if($paramer[\'keyword\']){
                               $jobname[]=str_replace($paramer[\'keyword\'],"<font color=#FF6600 >".$paramer[\'keyword\']."</font>",$job_name[$val]);
                            }else{
                               $jobname[]=$job_name[$val];
                            }
                        }
					}
				}
				
				'.$name.'[$k][\'expectjob\']=$jobname;
				$kcity_classid=@explode(",",$v[\'city_classid\']);
				$kcity_classid=array_unique($kcity_classid);	
				$cityname=array();
				if(is_array($kcity_classid)){
					foreach($kcity_classid as $val){
					    if($val!=\'\'){
					       
                              $cityname[]=$city_name[$val];
                            
                        }
					}
				}
                
				'.$name.'[$k][\'expectcity\']=$cityname;
				
				if($paramer[\'post_len\']){
					$postname[$k]=@implode(",",$jobname);
					'.$name.'[$k][\'job_post_n\']=mb_substr($postname[$k],0,$paramer[post_len],"utf-8");
				}
                if($paramer[\'city_len\']){
					$scityname[$k]=@implode("/",$cityname);
					'.$name.'[$k][\'city_name_n\']=mb_substr($scityname[$k],0,$paramer[city_len],"utf-8");
				}
			}
			foreach('.$name.' as $k=>$v){
               if($paramer[\'keyword\']){
					'.$name.'[$k][\'username_n\']=str_replace($paramer[\'keyword\'],"<font color=#FF6600 >".$paramer[\'keyword\']."</font>",$v[\'username_n\']);
					'.$name.'[$k][\'job_post\']=str_replace($paramer[\'keyword\'],"<font color=#FF6600 >".$paramer[\'keyword\']."</font>",'.$name.'[$k][\'job_post\']);
					'.$name.'[$k][\'job_post_n\']=str_replace($paramer[\'keyword\'],"<font color=#FF6600 >".$paramer[\'keyword\']."</font>",'.$name.'[$k][\'job_post_n\']);
					'.$name.'[$k][\'city_name_n\']=str_replace($paramer[\'keyword\'],"<font color=#FF6600 >".$paramer[\'keyword\']."</font>",'.$name.'[$k][\'city_name_n\']);
				}
            }
			if($paramer[\'keyword\']!=""&&!empty('.$name.')){
				addkeywords(\'5\',$paramer[\'keyword\']);
			}
		}';
		
		global $DiyTagOutputStr;
		$DiyTagOutputStr[]=$OutputStr;
		return SmartyOutputStr($this,$compiler,$_attr,'userlist',$name,'',$name);
	}
}
class Smarty_Internal_Compile_Userlistelse extends Smarty_Internal_CompileBase{
	public function compile($args, $compiler, $parameter){
		$_attr = $this->getAttributes($compiler, $args);

		list($openTag, $nocache, $item, $key) = $this->closeTag($compiler, array('userlist'));
		$this->openTag($compiler, 'userlistelse', array('userlistelse', $nocache, $item, $key));

		return "<?php }\nif (!\$_smarty_tpl->tpl_vars[$item]->_loop) {\n?>";
	}
}
class Smarty_Internal_Compile_Userlistclose extends Smarty_Internal_CompileBase{
	public function compile($args, $compiler, $parameter){
		$_attr = $this->getAttributes($compiler, $args);
		if ($compiler->nocache) {
			$compiler->tag_nocache = true;
		}

		list($openTag, $compiler->nocache, $item, $key) = $this->closeTag($compiler, array('userlist', 'userlistelse'));

		return "<?php } ?>";
	}
}
