<?php
/**
 * Created by PhpStorm.
 * User: YJC
 * Date: 2018/12/2 002
 * Time: 10:10
 */

namespace Geek;

class Crypt
{

    private $key;//加密key
    private $iv;//加密向量

    private $corpid;//合作ID

    public static $block_size = 32;

    public function __construct($key, $corpid)
    {
        $this->corpid = $corpid;
        $this->key = base64_decode($key . "=");
    }


    public function pkcs7_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public function _unpad($text)
    {
        $pad = ord(substr($text, -1));//取最后一个字符的ASCII 码值
        if ($pad < 1 || $pad > strlen($text)) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }

    /**
     * 秘钥key和向量iv填充算法：大于block_size则截取，小于则填充"\0"
     * @param $str
     * @param $block_size
     * @return string
     */
    private function _pad0($str, $block_size)
    {
        return str_pad(substr($str, 0, $block_size), $block_size, chr(0)); //chr(0) 与 "\0" 等效,因为\0转义后表示空字符，与ASCII表里的0代表的字符一样
    }

    /**
     * 解密字符串
     * @param string $data 字符串
     * @param string $key 加密key
     * @return string
     */
    public function decrypt($text)
    {
        $iv = substr($this->key, 0, 16);

        //php7已废弃
//        $ciphertext_dec = base64_decode($text);
//        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
//        mcrypt_generic_init($module, $this->key, $iv);
//
//        $decrypted = mdecrypt_generic($module, $ciphertext_dec);
//        mcrypt_generic_deinit($module);
//        mcrypt_module_close($module);

        //php7替代方案
        $decrypted = openssl_decrypt(base64_decode($text), "aes-256-cbc", $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        $result = $this->_unpad($decrypted);

        //去除16位随机字符串,网络字节序和AppId
        if (strlen($result) < 16) return "";

        $raw_content = substr($result, 16, strlen($result));
        $len_list = unpack("N", substr($raw_content, 0, 4));

        $len = $len_list[1];
        $content = substr($raw_content, 4, $len);
//        $corpid = substr($raw_content, $len + 4);

        return $content;
    }

    /**
     * 加密字符串
     * @param string $data 字符串
     * @param string $key 加密key
     * @return string
     */
    public function encrypt($text)
    {
        //获得16位随机字符串，填充到明文之前
        $random = $this->getRandomStr();
        $text = $random . pack("N", strlen($text)) . $text . $this->corpid;

        $iv = substr($this->key, 0, 16);
        $text = $this->pkcs7_pad($text, self::$block_size);

        //php7已废弃
//        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
//        mcrypt_generic_init($module, $this->key, $iv);
//        $encrypted = mcrypt_generic($module, $text);
//        mcrypt_generic_deinit($module);
//        mcrypt_module_close($module);

        //php7替代方案
        $encrypted = openssl_encrypt($text, "aes-256-cbc", $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        return base64_encode($encrypted);
    }

    public function getRandomStr()
    {
        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }
}