<?php
/**
 * 权限相关操作类
 * User: varphper
 * Date: 2017/12/13
 * Time: 12:25
 * Email: varphper@gmail.com
 */

namespace app\common\lib;

use think\Exception;
use think\facade\Cache;
use think\facade\Config;

class IAuth
{
    /**
     * 生成md5加密后的密码
     * @param $password
     * @return string
     */
    public function passwordEncode($password)
    {
        $salt = Config::get('custom.salt');
        return md5($password . $salt);
    }

    /**
     * 生成代理机构签名
     * @param array $data 应答返回签名
     * @return string
     */
    public function generateOpenSign(array $data)
    {
        //1.生成自定义的13位时间戳，增强之后生成的sign的唯一性
        $sign['time'] = (new Time())->getTime13();
        $sign['api_version'] = $data['api_version'];
        $sign['app_version'] = $data['app_version'];
        $sign['device_type'] = $data['device_type'];
        $sign['agency_key'] = $data['agency_key'];
        //2.按键值排序
        ksort($sign);
        //3.转query_string
        $str = http_build_query($sign);
        //4.Aes加密
        $str = (new Aes())->encrypt($str,$data['aes_key']);
        return $str;
    }

    /**
     * 检测代理机构签名有效性
     * @param string $sign 代理机构请求时发送的签名
     * @param string $encrypt_key 代理机构的加密密钥
     * @param string $agency_key 代理机构的机构密钥
     * @return bool
     */
    public function checkOpenSign($sign,$encrypt_key,$agency_key)
    {
        try{
            $str = (new Aes())->decrypt($sign,$encrypt_key);
        }catch(\Exception $exception){
            //todo 异常处理重写后修改
            return $exception->getMessage();
        }

        if (!isset($str)) {
            return false;
        }
        parse_str($str, $arr);
        if (!is_array($arr)) {
            return false;
        }
        //调试模式开启时不检查sign签名有效期和是否已使用过
        if (!Config::get('app.app_debug')) {
            //sign的有效时间计算
            $time10 = (new Time())->getTime10($arr['time']);
            $life = Config::get('custom.sign_life');
            if (!isset($arr['time']) || (time() - $time10) > $life) {
                return false;
            }
            //验证该sign是否已使用过，如已使用则在缓存中可查询到且值为1
            if (Cache::get($sign) == 1) {
                return false;
            }
        }

        if (empty($arr['agency_key']) || $arr['agency_key']!= $agency_key){
            return false;
        }

        $device_types = Config::get('custom.device_type');
        if (empty($arr['device_type']) || !in_array($arr['device_type'], $device_types)) {
            return false;
        }

        Cache::set($sign, 1, Config::get('custom.sign_cache_time'));
        return true;
    }

    /**
     * 生成App登录时的唯一性token
     * @return string 生成的token值
     */
    public function setAppLoginToken()
    {
        $token = md5(uniqid(microtime(true),true));
        return sha1($token);
    }

    /**
     * 生成sign签名(和内部客户端工程师约定好生成方法)
     * @param array $data
     * @return string
     */
    public function generateSign(array $data = [])
    {
        //1.生成自定义的13位时间戳，增强之后生成的sign的唯一性
        $sign['time'] = (new Time())->getTime13();
        $sign['api-version'] = $data['api-version'];
        $sign['app-version'] = $data['app-version'];
        $sign['device-type'] = $data['device-type'];
        $sign['agency_id'] = $data['agency_id'];
        $sign['agency_key'] = $data['agency_key'];
        //2.按键值排序
        ksort($sign);
        //3.转query_string
        $str = http_build_query($sign);
        //4.Aes加密
        $str = (new Aes())->encrypt($str);
        return $str;
    }

    /**
     * 检测签名有效性
     * @param array $data
     * @return bool
     */
    public function checkSign($data)
    {
        /**
         * 字段名      字段类型   是否必需  描述
         * api_version   String    Y        接口版本
         * app_version   String    Y        App版本
         * device_type   Integer   Y        设备类型(1:IOS,2:Android,3:web)
         * device_name   String    Y        设备名称
         * time          Integer   Y        自己设计算法生成的13位时间戳
         * device_imei   String    N        设备序列号
         * os_version    String    N        系统版本
         * token         String    N        包含用户ID,手机号(加密)
         * device_id     String    N        设备推送ID(极光)
         */
        //可选取以上某些字段生成签名，但需提前与客户端工程师约定好，文档定好

        $str = (new Aes())->decrypt($data['sign']);
        if (!isset($str)) {
            return false;
        }
        parse_str($str, $arr);
        if (!is_array($arr)) {
            return false;
        }
        //调试模式开启时不检查sign签名有效期和是否已使用过
        if (!Config::get('app.app_debug')) {
            //sign的有效时间计算（注意getSign里13位时间戳的生成方法）
            if (!isset($arr['time']) || (time() - (new Time())->getTime10($arr['time']) > Config::get('custom.sign_life'))) {
                return false;
            }
            //验证该sign是否已使用过，如已使用则在缓存中可查询到且值为1
            if (Cache::get($data['sign']) == 1) {
                return false;
            }
        }
        if (empty($arr['api-version']) || empty($data['api-version']) || $arr['api-version'] != $data['api-version']) {
            return false;
        }
        if (empty($arr['app-version']) || empty($data['app-version']) || $arr['app-version'] != $data['app-version']) {
            return false;
        }
        if (empty($arr['device-type']) || empty($data['device-type']) || $arr['device-type'] != $data['device-type'] || !in_array($data['device-type'], Config::get('custom.device_type'))) {
            return false;
        }
        return true;
    }
}