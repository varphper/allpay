<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/12
 * Time: 12:55
 * Email: varphper@gmail.com
 */

namespace app\data\controller;


use Curl\Curl;

class TempQuery extends Base
{
    public function dailyRun()
    {
        set_time_limit(0);
        $curl = new Curl();

        //数字彩当期//OKOK
        dump($curl->get('http://tb.cd/index.php/data/number_data/getGameInfo'));
        sleep(2);
        echo 1;
        echo "--";
        //数字彩当期综合开奖号等基本信息,可略去，getDetailAwardInfo接口抓取的信息包含这部分
        //dump($curl->get('http://tb.cd/index.php/data/number_data/getAwardInfo'));
        //sleep(6);
        //echo 2;echo "--";
        //数字彩各期中奖详情//OK
        dump($curl->get('http://tb.cd/index.php/data/number_data/getDetailAwardInfo'));
        sleep(3);
        echo 2;
        echo "--";


        //传统足彩14场胜负当期//OKOK
        dump($curl->get('http://tb.cd/index.php/data/football_data/traditional14'));
        sleep(2);
        echo 3;
        echo "--";
        //传统足彩14场胜负各期中奖号等基本信息//OK//中频
        dump($curl->get('http://tb.cd/index.php/data/football_data/traditional14Result'));
        sleep(3);
        echo 4;
        echo "--";
        //传统足彩14场胜负开奖详情//OKOK
        dump($curl->get('http://tb.cd/index.php/data/football_data/traditional14Award'));
        echo 5;
        echo "--";

        //竞彩足球
        dump($curl->get('http://tb.cd/index.php/data/sporttery_football_data/getHhggInfo'));
        //这个本来也可以省略，但是需要多观察一些数据，看是否只要出现在比赛队列里，混合过关就会销售
        //省略
        sleep(3);
        echo 6;
        echo "--";
        //当前可投注比赛信息//OK//高频
        dump($curl->get('http://tb.cd/index.php/data/sporttery_football_data/getAllInfo'));
        sleep(3);
        echo 7;
        echo "--";
        //赛果//OK//中频
        dump($curl->get('http://tb.cd/index.php/data/sporttery_football_data/getMatchResult'));
        sleep(2);
        echo 8;
        echo "--";


        //竞彩篮球
        dump($curl->get('http://tb.cd/index.php/data/sporttery_basketball_data/getHhggInfo'));
        //这个本来也可以省略，但是需要多观察一些数据，看是否只要出现在比赛队列里，混合过关就会销售
        //省略
        sleep(5);
        echo 9;
        echo "--";
        //篮球当前可投注比赛//OK//高频
        dump($curl->get('http://tb.cd/index.php/data/sporttery_basketball_data/getAllInfo'));
        sleep(2);
        echo 10;
        echo "--";

        //篮球赛果//OK//中频
        dump($curl->get('http://tb.cd/index.php/data/sporttery_basketball_data/getMatchResult'));
        sleep(3);
        echo 11;
        echo "--";

    }
}