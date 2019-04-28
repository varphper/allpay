<?php
/**
 * aes 加密 解密类库（使用Openssl支持php7.1）
 * User: varphper
 * Date: 2018/1/3
 * Time: 16:40
 * Email: varphper@gmail.com
 */

namespace app\common\lib;

use think\facade\Config;

class Aes
{
    private $_key = null;
    private $_cipher = null;

    public function __construct()
    {
        $this->_key = Config::get('custom.aes_key');
        $this->_cipher = 'AES-128-CBC';
    }

    /**
     * encrypt aes加密
     * @param   String  $input 要加密的数据
     * @param   String  $key   加密密钥
     * @return  String         加密后的数据
     */
    public function encrypt($input,$key='')
    {
        if (!$key){
            $key = $this->_key;
        }
        //openssl默认使用PKCS7模式进行填充!!!注意通知客户端工程师
        $iv = $this->ivGernerator();
        $data = openssl_encrypt($input, $this->_cipher, $key, OPENSSL_RAW_DATA, $iv);
        $data = $iv . base64_encode($data);
        return $data;
    }

    /**
     * decrypt aes解密
     * @param  String  $sStr 要解密的数据
     * @param  String  $key  加密密钥
     * @return String        解密后的数据
     */
    public function decrypt($sStr,$key='')
    {
        if (!$key){
            $key = $this->_key;
        }
        //求取初始化向量
        $iv_len = openssl_cipher_iv_length($this->_cipher);
        $iv = substr($sStr, 0, $iv_len);
        //base64编码时会将"+"变成空格，解码前需替换一下，否则base64解码会乱!!!!!!
        $sStr = str_replace(' ', '+', substr($sStr, $iv_len));
        $sStr = base64_decode($sStr);
        //openssl默认使用PKCS7模式进行填充!!!注意通知客户端工程师
        $decrypted = openssl_decrypt($sStr, $this->_cipher, $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }

    /**
     * 随机生成初始化向量
     * @return string 初始化向量
     */
    public function ivGernerator()
    {
        $len = openssl_cipher_iv_length($this->_cipher);
        $res = '';
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        for ($i = 0; $i < $len; $i++) {
            $res .= $chars[mt_rand(0, strlen($chars)-1)];
        }
        return $res;
    }

}