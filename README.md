# dingtalk-sdk
钉钉开发文档之服务端SDK

![build=passing][ico-build]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![composer][ico-composer]][link-packagist]
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
$dingtalk = new \Geek\DingTalk([
    'corp_id' => '',
    'secret' => '',
]);

//获取部门列表
$dingtalk->getDepartmentList();

//发送机器人消息
$dingtalk->robotSend($token, $message);
```

[ico-build]: https://img.shields.io/badge/build-passing-brightgreen.svg?maxAge=2592000
[ico-version]: https://img.shields.io/packagist/v/geek/dingtalk.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/geek/dingtalk.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/geek/dingtalk
[link-downloads]: https://packagist.org/packages/geek/dingtalk
[link-author]: https://github.com/geek-php

