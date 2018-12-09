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
/************
* 计划任务：每日自动更新部分职位
* 仅作参考
*/
global $db_config,$db,$config;

$time = time();
$endtime = strtotime('+'.$config['sy_maturityday'].' day',$time);
$num = $db->select_num("company_statis","`vip_etime`<'".$endtime."' and `vip_etime`>'".$time."'");
if ($num>0){
    //给管理员提醒
    $emailtitle=$config['sy_webname']."-会员到期提醒";
    $emailcoment=$config['sy_webname']."-有".$num."位企业会员".$config['sy_maturityday']."天内将要到期，请登录网站后台查看！";
    $emailData['email'] = $config['sy_webemail'];
    $emailData['subject'] = $emailtitle;
    $emailData['content'] = $emailcoment;
    include(dirname(dirname(dirname(__FILE__)))."/model/notice.model.php");
    $notice = new notice_model($db, $db_config['def']);
    $notice->sendEmail($emailData);
    
    //给会员提醒
    $comstatis=$db->select_all("company_statis","`vip_etime`<'".$endtime."' and `vip_etime`>'".$time."'","`uid`,`vip_etime`,`rating_name`");
    if(is_array($comstatis)){
        foreach($comstatis as $key=>$value){
            $uid[] = $value['uid'];
        }
        $companys=$db->select_all("company","`uid` IN (".@implode(',',$uid).") AND `linkmail`<>'' and `name`<>''","`uid`,`name`,`linkmail` as email,`linktel` as moblie");
        foreach($companys as $key=>$value){
            foreach ($comstatis as $k=>$v){
                if($value['uid']==$v['uid']){
                    $companys[$key]['vip_etime']=$v['vip_etime'];
                    $companys[$key]['rating_name']=$v['rating_name'];
                }
            }
        }
    }
    if($companys&&is_array($companys)){
        foreach($companys as $k=>$v){
            $userinfo[$v['uid']]=$v;
        }
    }
    if(count($userinfo)<500){
        foreach($userinfo as $key=>$value){
            $msgdata[] = vipedmsg($value);
        }
        foreach ($msgdata as $v){
            $notice->sendEmailType($v);
            $notice->sendSMSType($v);
        }
    }
}
function vipedmsg($info){
    $data['type']='viped';
    $data['uid']=$info['uid'];
    $data['name']=$info['name'];
    $data['email']=$info['email'];
    $data['moblie']=$info['moblie'];
    $data['ratingname']=$info['rating_name'];
    $data['date']=date("Y-m-d",$info['vip_etime']);
    $data['day'] = floor(($info['vip_etime'] - time()) / (60 * 60 * 24));
    return $data;
}
?>