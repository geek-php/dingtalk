# dingtalk-sdk
钉钉企业开发服务端SDK

![build=passing][ico-build]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

钉钉开放了丰富的服务端接口能力，开发者可以借助这些接口能力，实现企业系统与钉钉的集成打通。  
官方文档地址：https://open-doc.dingtalk.com/microapp/serverapi2

## 安装
推荐使用`composer`安装：
```
composer require geek/dingtalk
```

或者在composer.json里追加：
```
"require": {
	"geek/dingtalk": "1.0"
},
```

## 使用示例
首先需要到钉钉管理后台获取开发账号。详解：https://open-doc.dingtalk.com/microapp/serverapi2/hfoogs  

SDK需要配置：
``` 
corp_id
secret
ding_token
encoding_aes_key
```

使用示例：
``` php

$conf = [
    'corp_id' => '', //企业CorpId
    'secret' => '', //企业CorpSecret
    'encoding_aes_key' => '', //数据加密密钥。用于回调数据的加密，长度固定为43个字符，从a-z, A-Z, 0-9共62个字符中选取,可以随机生成
    'ding_token' => 'test', //加解密需要用到的token，自定义
    'callback' => 'http://test001.vaiwan.com/eventreceive', //回调URL，钉钉服务器会向URL发起回调事件
];

$dingtalk = new \Geek\DingTalk($conf);

//获取部门列表
$dingtalk->getDepartmentList();
```

## 接口

### 获取部门列表

``` php
//获取部门列表
$departs = $dingtalk->getDepartmentList();
```

### 发送群消息

钉钉没有提供直接向群里发送消息的功能。我们可以在管理后台添加一个虚拟员工，然后用该员工创建一个钉钉群，之后就可以直接发消息了。

1、创建群会话
``` php

$userIdList = [];// 群用户钉钉id列表，里面有哪些用户
$chat_name = '小钉'; //群会话名称
$chat_owner = ''; //该虚拟员工的钉钉id

//创建新会话
$res = $dingtalk->chatCreate($chat_name, $chat_owner, $userIdList);
if ($res['errcode'] != 0) {
    echo sprintf("chatCreate fail. msg:%s", json_encode($res));
    return '';
} else {

    $chatid = $res['chatid'];

    //可以把这个群会话存储起来，下次还是这几个成员，则直接取得chatid
}
```

2、根据群会话id发送消息

``` php

$state = State::getInstance();

//发送消息
$res = $dingtalk->chatSendText($chat_id, $message);
if ($res['errcode'] != 0) {
    return $state->setErrorNo($res['errcode'])->setErrorMsg($res['errmsg']);
}
```


### 发送机器人消息

需要先获取access_token：打开任意一个钉钉群设置，在机器人管理页面选择“自定义”机器人，输入机器人名字并选择要发送消息的群。如果需要的话，可以为机器人设置一个头像。点击“完成添加”，完成后会生成Hook地址。
hook地址里会有access_token。

``` php
$dingtalk->robotSend($access_token, $message);
```

### 业务事件回调

当钉钉内的企业发生一些业务变更时，会通过业务事件回调URL通知企业或者第三方应用，实现数据同步的功能。目前支持的业务事件包含：通讯录相关事件，群会话相关事件，签到相关事件，审批相关事件。

首先需要注册回调：

``` php

//注册的事件，详见 https://open-doc.dingtalk.com/microapp/serverapi2/skn8ld
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
$url = $conf['callback']; //回调地址

//注册回调地址
$dingtalk->registerCallBack($call_back_tag, $token, $aes_key, $url);

if ($res['errcode'] != 0) {
    echo $res['errmsg'];
}
```

然后实现回调url对应接口的功能：
``` php
//回调请求示例：https://127.0.0.1/corp_biz_callback?signature=111108bb8e6dbce3c9671d6fdb69d15066227608&timestamp=1783610513&nonce=380320111
//{ "encrypt":"1ojQf0N..." } 回调消息体使用Post请求body格式传递
$signature = $_GET['signature'];
$timestamp = $_GET['timestamp'];
$nonce = $_GET['nonce'];

$postdata = file_get_contents("php://input");
$postList = json_decode($postdata, true);
$encrypt_msg = $postList['encrypt'];

$state = receiveEvent($signature, $timestamp, $nonce, $encrypt_msg, $conf);

if ($state->getErrorNo() != 0) {
    echo sprintf("handle receiveEvent fail. code:%d, msg:%s", $state->getErrorNo(), $state->getErrorMsg());
}

//接收到推送数据之后，需要返回字符串success (代表了你收到了推送)，返回的数据也需要做加密处理，如果不返回，钉钉服务器将持续推送下去，达到一定阈值后将不再推送。
$sign_obj = new \Geek\Signature($conf);
$state = $sign_obj->encryptMsg("success", $timestamp, $nonce);
if ($state->getErrorNo() != 0) {
    echo sprintf("encrypt receiveEvent fail. code:%d, msg:%s", $state->getErrorNo(), $state->getErrorMsg());
}

/**
 * 钉钉回调处理
 *
 * @param $signature
 * @param $timestamp
 * @param $nonce
 * @param $encrypt_msg
 * @return \Geek\State
 */
function receiveEvent($signature, $timestamp, $nonce, $encrypt_msg, $conf)
{
    $state = \Geek\State::getInstance();

    $sign_obj = new \Geek\Signature($conf);
    $msg_state = $sign_obj->decryptMsg($signature, $timestamp, $nonce, $encrypt_msg);
    if ($msg_state->getErrorNo() != 0) {
        return $msg_state;
    }

    $eventMsg = json_decode($msg_state->getData());
    //示例数据
//        $eventMsg = json_decode('{"TimeStamp":"1536907636429","CorpId":"ding93794335028af70b","UserId":["2127383846805836532", "0565176120300975"],"EventType":"user_add_org"}');
    $eventType = $eventMsg->EventType;

    echo sprintf("decrypt succ. msg:%s", json_encode($eventMsg));

    switch ($eventType) {
        case "check_url": //测试回调URL。注册回调接口时，钉钉服务器会向URL发起【测试回调URL】事件，来验证填写的url的合法性，需要接收到回调之后返回加密字符串“success”的json数据，才能完成注册。
            break;
        case "check_create_suite_url":
            break;
        case "check_update_suite_url":
            break;
        case "user_add_org":
        case "user_modify_org":
            $user_ids = $eventMsg->UserId;

            break;

        case "user_leave_org": //离职
            $user_ids = $eventMsg->UserId;

            break;
        case "chat_add_member":
        case "chat_remove_member":
        case "chat_update_title":
            break;
        default:
            break;
    }

    return $state->setData([]);
}

```



[ico-build]: https://img.shields.io/badge/build-passing-brightgreen.svg?maxAge=2592000
[ico-version]: https://img.shields.io/packagist/v/geek/dingtalk.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/geek/dingtalk.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/geek/dingtalk
[link-downloads]: https://packagist.org/packages/geek/dingtalk
[link-author]: https://github.com/geek-php

