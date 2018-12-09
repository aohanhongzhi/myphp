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
class map_controller extends common{
    function index_action(){
        $this->get_moblie();
        $this->yunset("title","附近职位");
        $this->yuntpl(array('wap/map'));
    }
    
    function maplist_action(){
        $this->get_moblie();
        $this->yunset("title","附近职位");
        $this->yuntpl(array('wap/maplist'));
    }
    
    function joblist_action(){
        $this->get_moblie();
        $JobM=$this->MODEL('job');
        $select="`id`,`uid`,`name`,`minsalary`,`maxsalary`,`lastupdate`,6371 * acos(cos(radians(".$_POST['y'].")) * cos(radians(`y`)) * cos(radians(`x`) - radians(".$_POST['x'].")) + sin(radians(".$_POST['y'].")) * sin(radians(`y`))) AS `distance`";
        $page = $_POST['page']?$_POST['page']:1;
        if ($this->config['sy_indexpage']){
            $indexpagenum = ceil($this->config['sy_indexpage']/10);
            if($page>$indexpagenum){
                $page=$indexpagenum;
            }
        }
        $pagenav=($page-1)*10;
        $limit="$pagenav,10";
        $rows=$JobM->GetComjobList(array('r_status'=>'1','status'=>'0','state'=>'1','x>'=>0,'y>'=>0,'did'=>$this->config['did']),array('field'=>$select,'limit'=>$limit,'orderby'=>'distance','desc'=>'asc'));
        if ($rows){
            foreach ($rows as $v){
                $uids[]=$v['uid'];
            }
            $CompanyM=$this->MODEL('company');
            $com = $CompanyM->GetComList(array("`uid` in (".pylode(',', $uids).")",'did'=>$this->config['did']),array('field'=>'`uid`,`name`,`shortname`,`welfare`,`address`'));
            foreach ($rows as $k=>$v){
                $list[$k]['id']      =$v['id'];
                $list[$k]['name']    =mb_substr($v['name'], 0,16,'utf-8');
                if ($v['minsalary']){
                    if ($v['maxsalary']>0){
                        $list[$k]['salary_n']='￥'.$v['minsalary'].'-'.$v['maxsalary'];
                    }else{
                        $list[$k]['salary_n']='￥'.$v['minsalary'].'以上';
                    }
                }else{
                    $list[$k]['salary_n']='面议';
                }
                if($v['distance']<=1){
                    $list[$k]['dis']=ceil($v['distance']*1000).'m';
                }else{
                    $list[$k]['dis']=round($v['distance'], 2).'km';
                }
                $list[$k]['joburl']  =Url('wap',array('c'=>'job','a'=>'view','id'=>$v['id']));
                $list[$k]['comurl']  =Url('wap',array('c'=>'company','a'=>'show','id'=>$v['uid']));
                $list[$k]['addressurl']  =Url('wap',array('c'=>'map','a'=>'jobmap','id'=>$v['uid']));
                foreach ($com as $val){
                    if ($val['uid']==$v['uid']) {
                        if($v['shortname']){
                            $list[$k]['com_name']=mb_substr($val['shortname'], 0,16,'utf-8');
                        }else{
                            $list[$k]['com_name']=mb_substr($val['name'], 0,16,'utf-8');
                        }
                        if($v['welfare']){
                            $list[$k]['welfare']=@explode(",",$val['welfare']);
                        }
                        $list[$k]['address']=$val['address'];
                    }
                }
            }
        }
        $jobnum = $JobM->GetComjobNum(array('r_status'=>'1','status'=>'0','state'=>'1','x>'=>0,'y>'=>0));
        $pagecount = ceil($jobnum/10);
        if ($this->config['sy_indexpage']){
            $indexpagenum = ceil($this->config['sy_indexpage']/10);
            if($pagecount>$indexpagenum){
                $pagecount=$indexpagenum;
            }
        }
        
        $data['list']=count($list)>0?$list:array();
        $prev='';
        if($page>1){
            $prev=Url('wap',array('c'=>'map','page'=>$page-1,'x'=>$_POST['x'],'y'=>$_POST['y']));
        }
        $option="";
        for ($x=1; $x<=$pagecount; $x++) {
            $selected = '';
            if ($x==$page){
                $selected= "selected='selected'";
            }
            $option .= "<option value='".Url('wap',array('c'=>'map','page'=>$x,'x'=>$_POST['x'],'y'=>$_POST['y']))."' ".$selected.">".$x."</option>";
        }
        if($pagecount>$page){
            $next=Url('wap',array('c'=>'map','page'=>$page+1,'x'=>$_POST['x'],'y'=>$_POST['y']));
        }
        $data['prev']=$prev;
        $data['next']=$next;
        $data['option']=$option;
        $data['pagecount']=$pagecount;
        $data['error']=0;
        echo json_encode($data);die;
    }
    function comlist_action(){
        $select="`uid`,`name`,`shortname`,`x`,`y`,6371 * acos(cos(radians(".$_POST['y'].")) * cos(radians(`y`)) * cos(radians(`x`) - radians(".$_POST['x'].")) + sin(radians(".$_POST['y'].")) * sin(radians(`y`))) AS `distance`";
        $page = $_POST['page']?$_POST['page']:1;
        $pagenav=($page-1)*10;
        $limit="$pagenav,10";
        $CompanyM=$this->MODEL('company');
        $comrows=$CompanyM->GetComList(array('did'=>$this->config['did'],"`name`<>'' and `hy`<>'' and `r_status`<>2 HAVING `distance`<20 ORDER BY `distance` ASC"),array('field'=>$select,'limit'=>$limit));
        if($comrows){
            $comall=$CompanyM->GetComList(array('did'=>$this->config['did'],"`name`<>'' and `hy`<>'' and `r_status`<>2 HAVING `distance`<20"),array('field'=>$select));
            $pagecount = ceil(count($comall)/10);
            foreach($comrows as $v){
                $uids[]=$v['uid'];
            }
            $JobM=$this->MODEL('job');
            $joball=$JobM->GetComjobList(array('r_status'=>'1','status'=>'0','state'=>'1',"`uid` in(".pylode(',',$uids).")",'did'=>$this->config['did']),array('field'=>'id,uid,name'));
            if($joball){
                foreach ($comrows as $k=>$v){
                    if($v['shortname']){
                        $list[$k]['com_name']=mb_substr($v['shortname'], 0,16,'utf-8');
                    }else{
                        $list[$k]['com_name']=mb_substr($v['name'], 0,16,'utf-8');
                    }
                    $list[$k]['comurl']=Url('wap',array('c'=>'company','a'=>'show','id'=>$v['uid']));
                    $list[$k]['x']=$v['x'];
                    $list[$k]['y']=$v['y'];
                    $list[$k]['joblist']=array();
                    foreach ($joball as $val){
                        if ($val['uid']==$v['uid']) {
                            $val['joburl']=Url('wap',array('c'=>'job','a'=>'view','id'=>$val['id']));
                            $list[$k]['joblist'][]=$val;
                        }
                    }
                }
                foreach ($list as $k=>$v){
                    if (count($list[$k]['joblist'])<0){
                        unset($list[$k]);
                    }
                }
            }
        }
        $data['list']=count($list)>0?$list:array();
        $data['pagecount']=$pagecount?$pagecount:0;
        $data['error']=0;
        echo json_encode($data);die;
    }
    function jobmap_action(){
        $this->get_moblie();
        $this->yunset("title","附近职位");
        $comid = intval($_GET['id']);
        $companyM = $this->MODEL('company');
        $com = $companyM->GetCompanyInfo(array('uid'=>$comid),array('field'=>'`uid`,`name`,`cityid`,`address`,`x`,`y`'));
        $CacheM=$this->MODEL('cache');
        $CacheArr=$CacheM->GetCache(array('city'));
        $cityname = $CacheArr['city_name'][$com['cityid']];
        $this->yunset('cityname',$cityname);
        $this->yunset('com',$com);
        $user_agent = ( !isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];
        if ($_COOKIE['mapx']>0 && $_COOKIE['mapy']>0 && strpos($user_agent, 'Android')){
            $this->yunset(array('mapx'=>$_COOKIE['mapx'],'mapy'=>$_COOKIE['mapy']));
        }else{
            $this->yunset(array('mapx'=>0,'mapy'=>0));
        }
        $this->yuntpl(array('wap/com_map'));
    }
}
?>