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
];

$dingtalk = new \Geek\DingTalk($conf);

$token = ''; //自定义机器人的access_token
$message = 'hello';
$res = $dingtalk->robotSend($token, $message);

if ($res['errcode'] != 0) {
    echo $res['errmsg'];
}