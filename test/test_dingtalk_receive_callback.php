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
        case "check_url": //测试回调URL
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