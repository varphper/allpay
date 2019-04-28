<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/20
 * Time: 17:04
 * Email: varphper@gmail.com
 */

namespace app\data\controller;

use app\data\model\SportteryBasketballData as SportteryModel;
use Curl\Curl;

class SportteryBasketballData extends Base
{
    /**
     * 竞彩篮球混合过关玩法信息（简）采集
     * @return string|\think\response\Json
     * @throws \ErrorException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getHhggInfo()
    {
        $url = 'http://info.sporttery.cn/interface/interface_mixed.php?action=bk_list&0.8655419661441686&_=' . mt_rand();
        $curl = new Curl();
        $curl->setOpts($this->initCurlOpts());
        $curl->get($url);
        if ($curl->error) {
            return 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
            $response = $this->gb2312ToUtf8($curl->response);
            $curl->close();
            $data = (new SportteryModel())->hhggInfoDeal($response);
            if ($data) {
                $result = (new SportteryModel())->hhggInfoSave($data);
                return json($result);
            } else {
                return json(['valid' => 0, 'msg' => '远程获取数据失败']);
            }
        }
    }

    /**
     * 竞彩篮球各种玩法信息（全）采集
     * @return string|\think\response\Json
     * @throws \ErrorException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAllInfo()
    {
        $url = 'http://i.sporttery.cn/odds_calculator/get_odds?i_format=json&i_callback=getData&poolcode[]=mnl&poolcode[]=hdc&poolcode[]=wnm&poolcode[]=hilo&_=' . mt_rand();
        $curl = new Curl();
        $curl->setOpts($this->initCurlOpts());
        $curl->get($url);
        if ($curl->error) {
            return 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
            $response = $this->gb2312ToUtf8($curl->response);
            $curl->close();
            $data = (new SportteryModel())->allInfoDeal($response);
            if ($data) {
                $result = (new SportteryModel())->allInfoSave($data);
                return json($result);
            } else {
                return json(['valid' => 0, 'msg' => '远程获取数据失败']);
            }
        }
    }

    /**
     * 竞彩篮球比赛结果信息采集
     * @return string|\think\response\Json
     * @throws \ErrorException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMatchResult()
    {
        //采集第一页数据
        $url = 'http://info.sporttery.cn/basketball/match_result.php?_=' . mt_rand();
        $curl = new Curl();
        $curl->setOpts($this->initCurlOpts());
        $curl->get($url);
        if ($curl->error) {
            return 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
            $response = $this->gb2312ToUtf8($curl->response);
            $curl->close();
            $data = (new SportteryModel())->onePageResultDeal($response);
            if (!isset($data['count'])) {
                return json(['valid' => 0, 'msg' => '远程获取数据失败']);
            }
            //每页30条数据
            $pages = ceil($data['count'] / 30);
            if ($pages <= 1) {
                //如果仅有一页
                $result = (new SportteryModel())->onePageResultSave($data);
                return json($result);
            } else {
                //如果有多页
                $result = $this->getMatchResultByPage($pages);
                return json($result);
            }
        }
    }

    /**
     * 分页采集竞彩篮球比赛结果信息
     * @param $pages
     * @return array|string
     * @throws \ErrorException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMatchResultByPage($pages)
    {
        for ($i = 2; $i <= $pages; $i++) {
            //采集其它页数据
            $url = 'http://info.sporttery.cn/basketball/match_result.php?page=' . $i . '&_=' . mt_rand();
            $curl = new Curl();
            $curl->setOpts($this->initCurlOpts());
            $curl->get($url);
            if ($curl->error) {
                return 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
            } else {
                $response = $this->gb2312ToUtf8($curl->response);
                $curl->close();
                $data = (new SportteryModel())->onePageResultDeal($response);
                if ($data) {
                    $result = (new SportteryModel())->onePageResultSave($data);
                    return $result;
                } else {
                    return ['valid' => 0, 'msg' => '远程获取数据失败'];
                }
            }
        }
    }
}