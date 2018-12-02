<?php
/**
 * Created by PhpStorm.
 * User: YJC
 * Date: 2018/12/2 002
 * Time: 10:24
 */

$conf = [
    'corp_id' => '',
    'secret' => '',
    'encoding_aes_key' => '',
];

$dingtalk = new \Geek\DingTalk($conf);

$call_back_tag = [
    "user_add_org",
    "user_modify_org",
    "user_leave_org",
    "chat_add_member",
    "chat_remove_member",
    "chat_update_title"
];

$token = $conf['ding_token'];
$aes_key = $conf['encoding_aes_key'];
$url = 'http://dingtalk.52fhy.com/callback'; //回调地址

//注册回调地址
$dingtalk->registerCallBack($call_back_tag, $token, $aes_key, $url);

if ($res['errcode'] != 0) {
    echo $res['errmsg'];
}