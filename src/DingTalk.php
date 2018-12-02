<?php
/**
 * Created by PhpStorm.
 * User: YJC
 * Date: 2018/12/2 002
 * Time: 9:47
 */

namespace Geek;

class DingTalk
{
    private $host = 'https://oapi.dingtalk.com';

    private $appkey = '';
    private $appsecret = '';
    private $access_token = '';

    /**
     * DingTalk constructor.
     * @param $conf
     * @throws \Exception
     */
    public function __construct($conf)
    {
        if (!$conf || !isset($conf['corp_id']) || !isset($conf['secret'])) {
            throw new \Exception('dingding config is missed.');
        }

        $this->appkey = $conf['corp_id'];
        $this->appsecret = $conf['secret'];

        $this->gettoken();
    }

    /**
     * 获取access_token
     * 正常情况下access_token有效期为7200秒，有效期内重复获取返回相同结果，并自动续期。
     * @author yjc@52fhy.com
     * @date 2018/9/10
     */
    private function gettoken()
    {
        $uri = "/gettoken?appkey={$this->appkey}&appsecret={$this->appsecret}";
        $res = $this->request($uri);

        $this->access_token = $res['access_token'] ?? '';
    }

    /**
     * 获取通讯录权限范围
     * 该接口返回的auth_org_scopes的authed_dept字段是企业授权的部门id列表，通过这个列表可以获取用户列表
     * @author yjc@52fhy.com
     * @date 2018/9/10
     * @return array
     */
    public function getAuthScopes()
    {
        $uri = "/auth/scopes?access_token={$this->access_token}";
        $res = $this->request($uri);

        return $res;
    }

    /**
     * 获取所有部门列表（需要授权整个公司的权限，而不是按组授权）
     * 返回的department字段是数组
     * @example  {"errmsg":"ok","errcode":0,"department":[{"id":5678120,"createDeptGroup":true,"name":"行政人事部","parentid":1,"autoAddUser":true}]}
     *
     * @author yjc@52fhy.com
     * @date 2018/9/11
     * @param int $id
     * @return array
     */
    public function getDepartmentList($id = 1)
    {
        $uri = "/department/list?access_token={$this->access_token}&id={$id}";
        $res = $this->request($uri);

        return $res;
    }

    /**
     * 获取部门详情
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/dubakq#a-names1a%E8%8E%B7%E5%8F%96%E9%83%A8%E9%97%A8%E5%88%97%E8%A1%A8
     * @author yjc@52fhy.com
     * @date 2018/11/22
     * @param int $id
     * @return array
     */
    public function getDepartInfo($id = 1)
    {
        $uri = "/department/get?access_token={$this->access_token}&id={$id}";
        $res = $this->request($uri);

        return $res;
    }

    /**
     * 获取部门用户列表（详情）
     * @author yjc@52fhy.com
     * @date 2018/9/10
     * @param int $id
     * @return array
     */
    public function getUserList($department_id)
    {
        $uri = "/user/list?access_token={$this->access_token}&department_id={$department_id}";
        $res = $this->request($uri);

        return $res;
    }

    /**
     * 获取用户信息详情
     * @author yjc@52fhy.com
     * @date 2018/9/13
     * @param $userid
     * @return array
     */
    public function getUser($userid)
    {
        $uri = "/user/get?access_token={$this->access_token}&userid={$userid}";
        $res = $this->request($uri);

        return $res;
    }

    /**
     * @author yjc@52fhy.com
     * @date 2018/9/11
     * @param string $name 群名称，长度限制为1~20个字符
     * @param string $owner 群主userId，员工唯一标识ID；必须为该会话useridlist的成员之一
     * @param array $useridlist 群成员列表，每次最多支持40人，群人数上限为1000
     * @param int $showHistoryType 新成员是否可查看聊天历史消息（新成员入群是否可查看最近100条聊天记录），0代表否，1代表是，不传默认为否
     * @return array {"chatid":"chat304e7f19961a7fcad9bd336ad5285092","openConversationId":"cidy9pO96ou5kAfGPprQuhFMw==","conversationTag":2,"errmsg":"ok","errcode":0}
     */
    public function chatCreate(string $name, string $owner, array $useridlist, int $showHistoryType = 0)
    {
        $uri = "/chat/create?access_token={$this->access_token}";
        $res = $this->request($uri, [
            'name' => $name,
            'owner' => $owner,
            'useridlist' => $useridlist,
            'showHistoryType' => $showHistoryType,
        ], [], 'POST');

        return $res;
    }

    /**
     * @author yjc@52fhy.com
     * @date 2018/9/11
     * @param string $chatid 群会话的id
     * @return array {"chat_info":{"useridlist":["0565176120300975","05524434011110721"],"name":"测试群呀","owner":"0565176120300975","chatid":"chat10d3b21f02c3c94abd1d0ad91330e8d9","conversationTag":2},"errmsg":"ok","errcode":0}
     */
    public function chatGet(string $chatid)
    {
        $uri = "/chat/get?access_token={$this->access_token}&chatid={$chatid}";
        $res = $this->request($uri);

        return $res;
    }

    /**
     * 发送群消息
     * @author yjc@52fhy.com
     * @date 2018/9/11
     * @param string $chatid
     * @param string $msgtype
     * @param array $ext
     * @return array
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/isu6nk
     */
    public function chatSend(string $chatid, array $msg)
    {
        $uri = "/chat/send?access_token={$this->access_token}";

        $params = [
            'chatid' => $chatid,
            'msg' => $msg,
        ];

        $res = $this->request($uri, $params, [], 'POST');

        return $res;
    }

    /**
     * 发送文本消息
     * @author yjc@52fhy.com
     * @date 2018/9/11
     * @param string $chatid
     * @param string $content
     * @return array {"errcode":0,"errmsg":"ok","messageId":"abcd"}
     */
    public function chatSendText(string $chatid, string $content)
    {
        return $this->chatSend($chatid, self::makeTextMsg($content));
    }

    /**
     * 发送Markdown消息
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @param string $chatid
     * @param string $title
     * @param string $text
     * @return array
     */
    public function chatSendMarkdown(string $chatid, string $title, string $text)
    {
        return $this->chatSend($chatid, self::makeMarkdownMsg($title, $text));
    }

    /**
     * 发送机器人消息
     * @author yjc@52fhy.com
     * @date 2018/9/11
     * @param string $access_token 添加机器人得到的webhook的access_token
     * @param string $content
     * @return array {"errmsg":"ok","errcode":0}
     */
    public function robotSend(string $access_token, string $content)
    {
        $uri = "/robot/send?access_token={$access_token}";

        $params = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content
            ],
        ];

        $res = $this->request($uri, $params, [], 'POST');

        return $res;
    }

    /**
     * 发送应用内工作通知消息
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @param int $agent_id 企业开发者可在应用设置页面获取
     * @param string $userid_list 接收者的用户userid列表，多个英文逗号隔开。最大列表长度：20。
     * @param array $msg 通用消息内容
     * @return array {"errcode":0,"errmsg":"ok","task_id":123}
     */
    public function sendAppNotice(int $agent_id, string $userid_list, array $msg)
    {
        $uri = "/topapi/message/corpconversation/asyncsend_v2?access_token={$this->access_token}";

        $params = [
            'agent_id' => $agent_id,
            'userid_list' => $userid_list,
            'to_all_user' => false,
            'msg' => $msg
        ];

        $res = $this->request($uri, $params, [], 'POST');

        return $res;
    }

    /**
     * 发送文本通知消息
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @param int $agent_id
     * @param string $userid_list
     * @param string $content
     * @return array
     */
    public function sendAppNoticeText(int $agent_id, string $userid_list, string $content)
    {
        return $this->sendAppNotice($agent_id, $userid_list, self::makeTextMsg($content));
    }

    /**
     * text消息
     * @see https://open-doc.dingtalk.com/microapp/serverapi2/ye8tup
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @param $content
     * @return array
     */
    public static function makeTextMsg($content): array
    {
        return [
            'msgtype' => 'text',
            'text' => [
                'content' => $content
            ]];
    }

    /**
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @param string $media_id
     * @return array 图片媒体文件id，可以调用上传媒体文件接口获取。建议宽600像素 x 400像素，宽高比3 : 2
     */
    public static function makeImageMsg($media_id): array
    {
        return [
            'msgtype' => 'image',
            'image' => [
                'media_id' => $media_id
            ]];
    }

    /**
     * voice消息
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @param string $media_id 语音媒体文件id，可以调用上传媒体文件接口获取。2MB，播放长度不超过60s，AMR格式
     * @param int $duration 正整数，小于60，表示音频时长
     * @return array
     */
    public static function makeVoiceMsg($media_id, int $duration): array
    {
        return [
            'msgtype' => 'voice',
            'voice' => [
                'media_id' => $media_id,
                'duration' => $media_id,
            ]];
    }

    /**
     * link消息
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @param string $messageUrl 消息点击链接地址
     * @param string $picUrl 图片媒体文件id，可以调用上传媒体文件接口获取
     * @param string $title 消息标题
     * @param string $text 消息描述
     * @return array
     */
    public static function makeLinkMsg(string $messageUrl, string $picUrl, string $title, string $text): array
    {
        return [
            'msgtype' => 'link',
            'link' => [
                'messageUrl' => $messageUrl,
                'picUrl' => $picUrl,
                'title' => $title,
                'text' => $text,
            ]];
    }

    /**
     * markdown消息
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @param string $title 首屏会话透出的展示内容
     * @param string $text markdown格式的消息
     * @return array
     */
    public static function makeMarkdownMsg(string $title, string $text): array
    {
        return [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => $title,
                'text' => $text,
            ]];
    }

    /**
     * 注册业务事件回调接口
     *
     * 在您注册事件回调接口的时候，钉钉服务器会向您“注册回调接口”时候上传的url(接收回调的url)推送一条消息，用来测试url的合法性。
     * 收到消息后，需要返回经过加密后的字符串“success”的json数据，否则钉钉服务器将认为url不合法。
     *
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @param array $call_back_tag 需要监听的事件类型，例如["user_add_org", "user_modify_org", "user_leave_org"]
     * @param string $token 加解密需要用到的token，普通企业可以随机填写
     * @param string $aes_key 数据加密密钥。用于回调数据的加密，长度固定为43个字符，从a-z, A-Z, 0-9共62个字符中选取,您可以随机生成
     * @param string $url 接收事件回调的url
     * @return array {"errmsg":"ok","errcode":0}
     */
    public function registerCallBack(array $call_back_tag, string $token, string $aes_key, string $url)
    {
        $uri = "/call_back/register_call_back?access_token={$this->access_token}";

        $params = [
            'call_back_tag' => $call_back_tag,
            'token' => $token,
            'aes_key' => $aes_key,
            'url' => $url,
        ];

        $res = $this->request($uri, $params, [], 'POST');

        return $res;
    }

    /**
     * 查询事件回调接口
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @return array {"errcode":0,"errmsg":"ok","call_back_tag":["user_add_org","user_modify_org","user_leave_org"],"token":"123456","aes_key":"","url":"www.dingtalk.com"}
     */
    public function getCallBack()
    {
        $uri = "/call_back/get_call_back?access_token={$this->access_token}";

        $res = $this->request($uri);

        return $res;
    }

    /**
     * 更新事件回调接口
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @param array $call_back_tag
     * @param string $token
     * @param string $aes_key
     * @param string $url
     * @return array {"errmsg":"ok","errcode":0}
     */
    public function updateCallBack(array $call_back_tag, string $token, string $aes_key, string $url)
    {
        $uri = "/call_back/update_call_back?access_token={$this->access_token}";

        $params = [
            'call_back_tag' => $call_back_tag,
            'token' => $token,
            'aes_key' => $aes_key,
            'url' => $url,
        ];

        $res = $this->request($uri, $params, [], 'POST');

        return $res;
    }

    /**
     * 删除事件回调接口
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @return array {"errmsg":"ok","errcode":0}
     */
    public function deleteCallBack()
    {
        $uri = "/call_back/delete_call_back?access_token={$this->access_token}";

        $res = $this->request($uri);

        return $res;
    }

    /**
     * 获取回调失败的结果
     *
     * 钉钉服务器给回调接口推送时，有可能因为各种原因推送失败(比如网络异常)，此时钉钉将保留此次变更事件。用户可以通过此回调接口获取推送失败的变更事件。
     *
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @return array {"errcode":0,"errmsg":"ok","has_more":false,"failed_list":[{"event_time":32112412,"call_back_tag":"user_add_org","userid":["",""],"corpid":""}]}
     */
    public function getCallBackFailedResult()
    {
        $uri = "/call_back/get_call_back_failed_result?access_token={$this->access_token}";

        $res = $this->request($uri);

        return $res;
    }

    /**
     * CURL request
     * @return array {"errmsg":"ok","errcode":0}
     */
    private function request($uri, $data = [], $header = [], $type = 'GET')
    {
        $url = $this->host . $uri;
        $ssl = stripos($url, 'https://') === 0 ? true : false;

        //缺省header
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Encoding: gzip";

        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        }
        curl_setopt($curl, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出
        if (!empty ($data)) {
            $options = json_encode($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $options); // Post提交的数据包
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, 5); // 设置超时
        if (!empty($header)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header); // 设置HTTP头
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);

        $result = curl_exec($curl); // 执行操作
        curl_close($curl); // 关闭CURL会话

        $res = json_decode($result, true) ?: [];

        if (!$res) {
            return [
                'errcode' => __LINE__,
                'errmsg' => 'request dingding api no response',
            ];
        }

        return $res;
    }
}