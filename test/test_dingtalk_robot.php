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

$res = $dingtalk->getDepartmentList();

$departments = $res['department'] ?: [];

//获取部门用户列表
foreach ($departments as $department) {
    $res = $dingtalk->getUserList($department['id']);

    if ($res['errcode'] == 0) {

        if (!$res['userlist']) {
            $res['userlist'] = [];
        }
        
        foreach ($res['userlist'] as $user) {
            var_dump($user);
        }
    }
}