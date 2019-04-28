<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/12
 * Time: 15:47
 * Email: varphper@gmail.com
 */

namespace app\data\controller;


use app\data\model\TraditionalFootballData;
use Curl\Curl;

class FootballData extends Base
{
    /**
     * 当期14场胜负(包含任9)数据采集
     * @return string|\think\response\Json
     * @throws \ErrorException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function traditional14()
    {
        $url1 = 'http://i.sporttery.cn/wap/fb_lottery/fb_lottery_nums?key=wilo&num=&f_callback=getNumBack&_=' . mt_rand();
        $url2 = 'http://i.sporttery.cn/wap/fb_lottery/fb_lottery_match?key=wilo&num=&f_callback=getDataBack&_=' . mt_rand();
        $curl1 = new Curl();
        $curl1->setOpts($this->initCurlOpts());
        $curl1->get($url1);
        $curl2 = new Curl();
        $curl2->setOpts($this->initCurlOpts());
        $curl2->get($url2);

        if ($curl1->error || $curl2->error) {
            $errorMessage = $curl1->errorMessage ? $curl1->errorMessage : $curl2->errorMessage;
            return 'Curl Error: ' . $errorMessage . "\n";
        } else {
            $response1 = $this->gb2312ToUtf8($curl1->response);
            $response2 = $this->gb2312ToUtf8($curl2->response);
            $curl1->close();
            $curl2->close();
            $data = (new TraditionalFootballData())->game14InfoDeal($response1, $response2);
            if ($data) {
                $result = (new TraditionalFootballData())->game14InfoSave($data);
                return json($result);
            } else {
                return json(['valid' => 0, 'msg' => '远程获取数据失败']);
            }
        }
    }


    /**
     * 14场胜负历史数据采集（包含彩果）
     * @return string|\think\response\Json
     * @throws \ErrorException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function traditional14Result()
    {
        $cur_issue = (new TraditionalFootballData())->getNewIssue();
        $return = [];
        for ($i = 0; $i < 2; $i++) {
            $url1 = 'http://i.sporttery.cn/wap/fb_lottery/fb_lottery_nums?key=wilo&num=' . ($cur_issue - $i) . '&f_callback=getNumBack&_=' . mt_rand();
            $url2 = 'http://i.sporttery.cn/wap/fb_lottery/fb_lottery_match?key=wilo&num=' . ($cur_issue - $i) . '&f_callback=getDataBack&_=' . mt_rand();
            $curl1 = new Curl();
            $curl1->setOpts($this->initCurlOpts());
            $curl2 = new Curl();
            $curl2->setOpts($this->initCurlOpts());
            $curl1->get($url1);
            $curl2->get($url2);

            if ($curl1->error || $curl2->error) {
                $errorMessage = $curl1->errorMessage ? $curl1->errorMessage : $curl2->errorMessage;
                return 'Curl Error: ' . $errorMessage . "\n";
            } else {
                $response1 = $this->gb2312ToUtf8($curl1->response);
                $response2 = $this->gb2312ToUtf8($curl2->response);
                $data = (new TraditionalFootballData())->game14InfoDeal($response1, $response2);
                $curl1->close();
                $curl2->close();
                if ($data) {
                    $result = (new TraditionalFootballData())->game14ResultSave($data);
                    $return[] = $result;
                } else {
                    $return[] = ['valid' => 0, 'msg' => '远程获取数据失败'];
                }
            }
            $sleep = array_rand([1,2],1);
            sleep($sleep);
        }
        return json($return);
    }

    /**
     * 14场胜负(含任选9场)中奖详情采集
     * @return string|\think\response\Json
     * @throws \ErrorException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function traditional14Award()
    {
        $cur_issue = (new TraditionalFootballData())->getNewIssue();
        $return = [];
        for ($i = 1; $i < 2; $i++) {//初期为了多抓数据所以写成了循环
            $url = 'http://zx.500.com/zc/inc/zc_kaijiang.php?gt=ajax&lotid=2:17&expect=' . ($cur_issue - $i) . '&_=' . mt_rand();
            $curl = new Curl();
            $curl->setOpts($this->initCurlOpts());
            $curl->get($url);

            if ($curl->error) {
                return 'Curl Error: ' . $curl->errorMessage . "\n";
            } else {
                $data = (new TraditionalFootballData())->game14AwardDeal($curl->response);
                $curl->close();
                if ($data) {
                    $result = (new TraditionalFootballData())->game14AwardSave($data);
                    $return[] = $result;
                } else {
                    $return[] = ['valid' => 0, 'msg' => '远程获取数据失败'];
                }
            }
        }
        return json($return);
    }
}