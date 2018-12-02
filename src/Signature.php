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
     * @return State
     * @throws \Exception
     */
    public function encryptMsg($plain, $timeStamp, $nonce)
    {
        $state = State::getInstance();

        $aes_key = $this->conf['encoding_aes_key'] ?: '';
        $corp_id = $this->conf['corp_id'] ?: '';
        $token = $this->conf['ding_token'] ?: '';

        if (!$aes_key) {
            return $state->setErrorNo(__LINE__)->setErrorMsg('miss config dingding:encoding_aes_key');
        }

        if (!$corp_id) {
            return $state->setErrorNo(__LINE__)->setErrorMsg('miss config dingding:corp_id');
        }

        if (!$token) {
            return $state->setErrorNo(__LINE__)->setErrorMsg('miss config dingding:ding_token');
        }

        $crypt = new Crypt($aes_key, $corp_id);

        $encrypt_msg = $crypt->encrypt($plain);
        if (!$encrypt_msg) {
            return $state->setErrorNo(__LINE__)->setErrorMsg('encrypt fail');
        }

        if (!$timeStamp) {
            $timeStamp = time();
        }

        $verify_signature = $this->getSHA1($token, $timeStamp, $nonce, $encrypt_msg);
        if (!$verify_signature) {
            return $state->setErrorNo(__LINE__)->setErrorMsg('signature fail');
        }

        $data = json_encode(array(
            "msg_signature" => $verify_signature,
            "encrypt" => $encrypt_msg,
            "timeStamp" => $timeStamp,
            "nonce" => $nonce
        ));

        return $state->setData($data);
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
        $state = State::getInstance();
        
        $aes_key = $this->conf['encoding_aes_key'] ?: '';
        $corp_id = $this->conf['corp_id'] ?: '';
        $token = $this->conf['ding_token'] ?: '';

        if (strlen($aes_key) != 43) {
            return $state->setErrorNo(__LINE__)->setErrorMsg('illegal encoding_aes_key');
        }

        if (!$corp_id) {
            return $state->setErrorNo(__LINE__)->setErrorMsg('miss config dingding:corp_id');
        }

        if (!$token) {
            return $state->setErrorNo(__LINE__)->setErrorMsg('miss config dingding:ding_token');
        }

        $verify_signature = $this->getSHA1($token, $timestamp, $nonce, $encrypt_msg);
        if (!$verify_signature) {
            return $state->setErrorNo(__LINE__)->setErrorMsg('signature fail');
        }

        if ($verify_signature != $signature) {
            return $state->setErrorNo(__LINE__)->setErrorMsg('validate signature error');
        }

        $crypt = new Crypt($aes_key, $corp_id);
        $result = $crypt->decrypt($encrypt_msg);

        if (!$result) {
            return $state->setErrorNo(__LINE__)->setErrorMsg('decrypt fail');
        }

        return $state->setData($result);
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