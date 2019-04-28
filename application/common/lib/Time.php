<?php
/**
 * 时间处理类
 * User: varphper
 * Date: 2018/1/4
 * Time: 21:38
 * Email: varphper@gmail.com
 */

namespace app\common\lib;

class Time
{
    /**
     * 生成自定义13位时间戳(毫秒数)
     * @return string 自定义13位时间戳
     */
    public function getTime13()
    {
        //为了增强sign的唯一性
        list($t1, $t2) = explode(" ", microtime());
        return $t2 . ceil($t1 * 1000);
    }

    /**
     * 将自定义13位时间戳转换为正常10位时间戳
     * @param $time13 自定义13位时间戳
     * @return integer 正常10位时间戳
     */
    public function getTime10($time13)
    {
        return ceil($time13 / 1000);
    }


//    /**
//     * 获取服务器端时间戳
//     * @return array
//     */
//    public function index()
//    {
//        return show(1, '服务器当前时间获取成功', ['time' => time()], 200);
//    }
//
//    /**
//     * 获取服务器端时间减去客户端时间的差
//     * @param $client 客户端请求时刻时间戳
//     * @return \think\response\Json
//     */
//    public function difference($client)
//    {
//        $diff = time() - $client;
//        return show(1, '服务器时间与本地时间差', ['diff' => $diff], 200);
//    }
}