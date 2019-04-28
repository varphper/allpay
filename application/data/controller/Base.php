<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/9
 * Time: 18:42
 * Email: varphper@gmail.com
 */

namespace app\data\controller;


use think\Controller;

class Base extends Controller
{
    public function initialize()
    {
        parent::initialize();
        if (!$this->isLocal()) {
            return json(['valid' => 0, 'msg' => '您没有权限访问']);
            exit;
        }
    }

    public function gb2312ToUtf8($str)
    {
        return iconv("GB2312", "UTF-8//IGNORE", $str);
    }

    public function isLocal()
    {
        return $this->request->ip() == '127.0.0.1';
    }

    public function parseUrl($url)
    {
        $parse = parse_url($url);
        $query = $parse['query'];
        $queryParts = explode('&', $query);
        $params = [];
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

    public function initCurlOpts()
    {
        $ip = mt_rand(11, 191) . "." . mt_rand(0, 240) . "." . mt_rand(1, 240) . "." . mt_rand(1, 240);   //随机ip
        $agentarry = [
            //PC端的UserAgent
            "safari 5.1 – MAC" => "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11",
            "safari 5.1 – Windows" => "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50",
            "Firefox 38esr" => "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0",
            "IE 11" => "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; .NET4.0C; .NET4.0E; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; InfoPath.3; rv:11.0) like Gecko",
            "IE 9.0" => "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0",
            "IE 8.0" => "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)",
            "IE 7.0" => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)",
            "IE 6.0" => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)",
            "Firefox 4.0.1 – MAC" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
            "Firefox 4.0.1 – Windows" => "Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
            "Opera 11.11 – MAC" => "Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; en) Presto/2.8.131 Version/11.11",
            "Opera 11.11 – Windows" => "Opera/9.80 (Windows NT 6.1; U; en) Presto/2.8.131 Version/11.11",
            "Chrome 17.0 – MAC" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_0) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11",
            "Chrome 65.0 – Windows" => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36',
        ];
        $useragent = $agentarry[array_rand($agentarry, 1)];  //随机浏览器useragent
        
        $opts = [
            CURLOPT_COOKIEJAR => '/tmp/curlCookieFile',// 把返回来的Cookie信息保存在文件中
            CURLOPT_COOKIEFILE => '/tmp/curlCookieFile',//发送Cookie
            CURLOPT_RETURNTRANSFER => true,//将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
            CURLOPT_USERAGENT => $useragent,
            CURLOPT_SSL_VERIFYPEER => false,// 对认证证书来源的检查
            CURLOPT_SSL_VERIFYHOST => 0,// 从证书中检查SSL加密算法是否存在
            CURLOPT_CONNECTTIMEOUT => 0,
        ];
        return $opts;
    }

}