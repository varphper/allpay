<?php
/**
 * 数字彩游戏数据（开售、开奖）采集
 * User: varphper
 * Date: 2018/7/9
 * Time: 17:43
 * Email: varphper@gmail.com
 */

namespace app\data\controller;


use app\data\model\NumberGameData;
use Curl\Curl;

class NumberData extends Base
{
    /**
     * 采集数字彩游戏信息
     * @param NumberGameData $numberGameData
     * @return string|\think\response\Json
     * @throws \ErrorException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGameInfo(NumberGameData $numberGameData)
    {
        $url = 'http://www.sporttery.cn/digitallottery/?_=' . mt_rand();
        $curl = new Curl();
        $curl->setOpts($this->initCurlOpts());
        $curl->get($url);
        if ($curl->error) {
            return 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
            $response = $this->gb2312ToUtf8($curl->response);
            $curl->close();
            $data = $numberGameData->gameInfoDeal($response);
            if ($data) {
                $result = $numberGameData->gameInfoSave($data);
                return json($result);
            } else {
                return json(['valid' => 0, 'msg' => '远程获取数据失败']);
            }
        }
    }

    /**
     * 采集数字彩当期开奖基本信息
     * @param NumberGameData $numberGameData
     * @return string|\think\response\Json
     * @throws \ErrorException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAwardInfo(NumberGameData $numberGameData)
    {
        $url = 'http://info.sporttery.cn/interface/lottery_num.php?action=new&_=' . mt_rand();
        $curl = new Curl();
        $curl->setOpts($this->initCurlOpts());
        $curl->get($url);
        if ($curl->error) {
            return 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
            $response = $this->gb2312ToUtf8($curl->response);
            $curl->close();
            $data = $numberGameData->awardInfoDeal($response);
            if ($data) {
                $result = $numberGameData->awardInfoSave($data);
                return json($result);
            } else {
                return json(['valid' => 0, 'msg' => '远程获取数据失败']);
            }
        }
    }

    /**
     * 数字彩各期中奖详细信息
     * @param NumberGameData $numberGameData
     * @return \think\response\Json
     * @throws \ErrorException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDetailAwardInfo(NumberGameData $numberGameData)
    {
        $info = $numberGameData->getNewIssue();
        $type = ['超级大乐透' => 1, '7星彩' => 2, '22选5' => 3, '31选7' => 4, '排列3' => 5, '排列5' => 6];
        $result = [];
        foreach ($info as $v) {
            for ($i=1;$i<2;$i++){
                if (!in_array($v['lottery_name'], ['22选5', '31选7'])) {
                    $url = 'http://info.sporttery.cn/digital/dlt.php?&type=' . $type[$v['lottery_name']] . '&issue=' . ($v['new_issue']-$i);
                    $result[] = $this->queryDetailAwardInfo($numberGameData, $url, $v['lottery_name']);
                }
            }
            $sleep = array_rand([1,2],1);
            sleep($sleep);
        }
        return json($result);
    }

    /**
     * 数字彩各期中奖详细信息采集子方法
     * @param $numberGameData
     * @param $url
     * @param $lottery
     * @return array|string
     * @throws \ErrorException
     */
    public function queryDetailAwardInfo($numberGameData, $url, $lottery)
    {
        $curl = new Curl();
        $curl->setOpts($this->initCurlOpts());
        $curl->get($url);
        if ($curl->error) {
            return 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
            $response = $this->gb2312ToUtf8($curl->response);
            $curl->close();
            $data = $numberGameData->detailAwardInfoDeal($response);
            if ($data) {
                $result = $numberGameData->detailAwardInfoSave($data, $lottery);
                return $result;
            } else {
                return ['valid' => 0, 'msg' => '远程获取数据失败'];
            }
        }
    }
}