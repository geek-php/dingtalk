<?php
/**
 * Created by PhpStorm.
 * User: YJC
 * Date: 2018/12/2 002
 * Time: 10:05
 */

namespace Geek;

class Signature
{

    private $conf;

    public function __construct(array $conf)
    {
        $this->conf = $conf;
    }

    /**
     * 加密钉钉回调消息
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @param $plain
     * @param $timeStamp
     * @param $nonce
     * @throws \Exception
     */
    public function encryptMsg($plain, $timeStamp, $nonce)
    {
        $aes_key = $this->conf['encoding_aes_key'] ?: '';
        $corp_id = $this->conf['corp_id'] ?: '';
        $token = $this->conf['ding_token'] ?: '';

        if (!$aes_key) {
            throw new \Exception('miss config encoding_aes_key', __LINE__);
        }

        if (!$corp_id) {
            throw new \Exception('miss config corp_id', __LINE__);
        }

        if (!$token) {
            throw new \Exception('miss config ding_token', __LINE__);
        }

        $crypt = new Crypt($aes_key, $corp_id);

        $encrypt_msg = $crypt->encrypt($plain);
        if (!$encrypt_msg) {
            throw new \Exception('encrypt fail', __LINE__);
        }

        if (!$timeStamp) {
            $timeStamp = time();
        }

        $verify_signature = $this->getSHA1($token, $timeStamp, $nonce, $encrypt_msg);
        if (!$verify_signature) {
            throw new \Exception('signature fail', __LINE__);
        }

        $data = json_encode(array(
            "msg_signature" => $verify_signature,
            "encrypt" => $encrypt_msg,
            "timeStamp" => $timeStamp,
            "nonce" => $nonce
        ));

        return $data;
    }

    /**
     * 解析钉钉回调消息
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @param $signature
     * @param null $timestamp
     * @param $nonce
     * @param $encrypt_msg
     * @throws \Exception
     */
    public function decryptMsg($signature, $timestamp, $nonce, $encrypt_msg)
    {
        $aes_key = $this->conf['encoding_aes_key'] ?: '';
        $corp_id = $this->conf['corp_id'] ?: '';
        $token = $this->conf['ding_token'] ?: '';

        if (strlen($aes_key) != 43) {
            throw new \Exception('illegal encoding_aes_key', __LINE__);
        }

        if (!$corp_id) {
            throw new \Exception('miss config corp_id', __LINE__);
        }

        if (!$token) {
            throw new \Exception('miss config ding_token', __LINE__);
        }

        $verify_signature = $this->getSHA1($token, $timestamp, $nonce, $encrypt_msg);
        if (!$verify_signature) {
            throw new \Exception('signature fail', __LINE__);
        }

        if ($verify_signature != $signature) {
            throw new \Exception('validate signature error', __LINE__);
        }

        $crypt = new Crypt($aes_key, $corp_id);
        $result = $crypt->decrypt($encrypt_msg);

        if (!$result) {
            throw new \Exception('decrypt fail', __LINE__);
        }

        return $result;
    }

    /**
     * 计算signature
     * @author yjc@52fhy.com
     * @date 2018/9/12
     * @param $token
     * @param $timestamp
     * @param $nonce
     * @param $encrypt_msg
     * @return string
     */
    public function getSHA1($token, $timestamp, $nonce, $encrypt_msg)
    {
        try {
            $array = array($encrypt_msg, $token, $timestamp, $nonce);
            sort($array, SORT_STRING);
            $str = implode($array);
            return sha1($str);
        } catch (\Exception $e) {
            return '';
        }
    }

}