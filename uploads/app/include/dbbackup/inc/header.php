<?php
require(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/global.php');
global $db_config,$db,$config;
session_start();
if(!$_SESSION['auid']){
    include(PLUS_PATH."/admindir.php");
    if($admindir){
        $adminurl = $admindir;
    }else{
        $adminurl = 'admin';
    }
    header("Location: ".$config['sy_weburl'].'/'.$adminurl);
}else{
    if($config['sy_web_site']=='1'){
        $query = $db->query("SELECT * FROM `".$db_config['def']."admin_user` WHERE `uid`='".$_SESSION['auid']."' and (`did`='".$config['did']."' or `isdid`='1') limit 1");
    }else{
        $query = $db->query("SELECT * FROM `".$db_config['def']."admin_user` WHERE `uid`='".$_SESSION['auid']."'  limit 1");
    }
    
    $us = is_array($row = $db->fetch_array($query));
    $shell = $us ? md5($row['username'].$row['password']):FALSE;
    if($shell===false){
        include(PLUS_PATH."/admindir.php");
        if($admindir){
            $adminurl = $admindir;
        }else{
            $adminurl = 'admin';
        }
        header("Location: ".$config['sy_weburl'].'/'.$adminurl);
    }
}
@require('config.php');
include(APP_PATH.'/app/include/dbbackup/class/functions.php');
$editor=2;

$mydbname=$db_config['dbname'];
$mypath=$_GET['mypath'];
if(empty($mydbname)||empty($mypath)){
	//TODO:ErrorUrl","history.go(-1)");
}
//编码
SetCharset($db_config['charset']);
$usql=$db->query("use `$mydbname`");
?>