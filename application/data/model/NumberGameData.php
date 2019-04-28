<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/9
 * Time: 18:42
 * Email: varphper@gmail.com
 */

namespace app\data\model;


use think\Model;

class NumberGameData extends Model
{
    protected $autoWriteTimestamp = true;

    /**
     * 提取并处理采集信息中的数字彩售卖信息
     * 正常返回数组
     * @param $string string 采集的页面字符串
     * @return false|int|mixed
     */
    public function gameInfoDeal($string)
    {
        $pattern = "/var d = (\{.*?\});/is";
        $arr = [];
        $res = preg_match($pattern, $string, $arr);
        if ($res) {
            //成功匹配$res为1
            $data = str_replace("'", '"', $arr[1]);
            return json_decode($data, true);
        } else {
            //出错返回$res为false，未匹配到为0
            return $res;
        }
    }

    /**
     * 将数据存入数据库
     * @param $data array
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function gameInfoSave($data)
    {
        $arr = [];
        $i = -1;
        foreach ($data as $k => $v) {
            $i++;
            $arr[$i]['issue'] = $v['issue'];
            $arr[$i]['lottery_name'] = $k;
            $arr[$i]['official_sell_endtime'] = (string)strtotime($v['date']);
            $arr[$i]['award_time'] = (string)strtotime($v['date'] . '+ 30min');

            $replace = $this->where('issue', $arr[$i]['issue'])
                ->where('lottery_name', $arr[$i]['lottery_name'])
                ->find();

            try {
                if ($replace) {
                    $this->allowField(true)->update($arr[$i], ['issue' => $arr[$i]['issue'], 'lottery_name' => $arr[$i]['lottery_name']]);
                } else {
                    $this->allowField(true)->isUpdate(false)->data($arr[$i])->save();
                }
            } catch (\Exception $errorException) {
                return ['valid' => 0, 'msg' => $errorException->getMessage()];
            }
        }
        return ['valid' => 1, 'msg' => '操作成功'];
    }

    /**
     * 查询最新一期游戏期号及游戏名
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getNewIssue()
    {
        return $this->field('MAX(issue) as new_issue, lottery_name')->group('lottery_name')->select();
    }

    /**
     * 提取并处理采集信息中的开奖信息
     * @param $string string 采集的页面字符串
     * @return array|false|int
     */
    public function awardInfoDeal($string)
    {
        $pattern = "/var lottery_nums='(.*?)';/is";
        $arr = [];
        $res = preg_match($pattern, $string, $arr);
        if ($res) {
            return explode('~', $arr[1]);
        } else {
            return $res;
        }
    }

    /**
     * 开奖信息再处理且入库
     * @param $data array 开奖信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function awardInfoSave($data)
    {
        $arr = [];
        $return = [];
        $i = -1;
        foreach ($data as $k => $v) {
            $i++;
            if ($v) {
                $arr[$i] = explode('|', $v);
                if ($arr[$i][0] == '22选5' || $arr[$i][0] == '31选7') {
                    continue;
                }

                $return[$i]['winning_number'] = $arr[$i][2];
                $return[$i]['issue'] = $arr[$i][1];
                $return[$i]['lottery_name'] = $arr[$i][0] == '大乐透' ? '超级' . $arr[$i][0] : $arr[$i][0];
                $return[$i]['award_time'] = strtotime($arr[$i][3] . " 20:30");
                $return[$i]['balance'] = $arr[$i][4];

                $replace = $this->where('issue', $return[$i]['issue'])
                    ->where('lottery_name', $return[$i]['lottery_name'])
                    ->find();
                try {
                    if ($replace) {
                        $this->allowField(true)->update($return[$i], ['issue' => $return[$i]['issue'], 'lottery_name' => $return[$i]['lottery_name']]);
                    } else {
                        $this->allowField(true)->isUpdate(false)->data($return[$i])->save();
                    }
                } catch (\Exception $errorException) {
                    return ['valid' => 0, 'msg' => $errorException->getMessage()];
                }
            }
        }
        return ['valid' => 1, 'msg' => '操作成功'];
    }

    /**
     * 中奖信息提取
     * @param $string string 采集的页面html
     * @return array|false|int
     */
    public function detailAwardInfoDeal($string)
    {
        $return = [];

        $arr = [];
        $pattern = "/第 (\d*) 期开奖公告/is";
        $res = preg_match($pattern, $string, $arr);
        if ($res) {
            $return['issue'] = $arr[1];
        } else {
            return $res;
        }

        $arr = [];
        $pattern = "/本期全国销售金额：<b>(\S*)<\/b> 元/is";
        $res = preg_match($pattern, $string, $arr);
        if ($res) {
            $return['sales'] = $arr[1];
        } else {
            return $res;
        }

        $arr = [];
        $pattern = "/兑奖截止日为(\d*?)年(\d*?)月(\d*?)日/is";
        $res = preg_match($pattern, $string, $arr);
        if ($res) {
            $return['prize_end_date'] = strtotime($arr[1] . '-' . $arr[2] . '-' . $arr[3]);
        } else {
            return $res;
        }

        $arr = [];
        $pattern = "/<td .*?>(.*?)<\/td>/is";
        $res = preg_match_all($pattern, $string, $arr);
        if ($res) {
            $return['award'] = $arr[1];
        } else {
            return $res;
        }

        $arr = [];
        $pattern1 = '/本期开奖号码：<\/div><span .*?>(.*?)<\/span><div style="width:100px; float:left;">&nbsp;<\/div><span class="color_g" style="float:left; line-height:30px;">(.*?)<\/span>/is';
        $pattern2 = '/本期开奖号码：<\/div><span .*?>(.*?)<\/span>/is';
        if ($res = preg_match_all($pattern1, $string, $arr)) {
            $return['winning_number'] = str_replace('&nbsp;&nbsp;&nbsp;&nbsp;', ',', $arr[1][0]) . ',' . str_replace('&nbsp;&nbsp;&nbsp;&nbsp;', ',', $arr[2][0]);
        } elseif ($res = preg_match_all($pattern2, $string, $arr)) {
            $return['winning_number'] = $arr[1][0];
        } else {
            return $res;
        }
        if (strrpos($return['winning_number'], ',') === strlen($return['winning_number']) - 1) {
            $return['winning_number'] = substr($return['winning_number'], 0, -1);
        }

        $arr = [];
        $pattern = "/开奖日期：(.*?)日<br/is";
        $res = preg_match_all($pattern, $string, $arr);
        if ($res) {
            $return['award_time'] = strtotime(str_replace(['年', '月'], '-', $arr[1][0]) . " 20:30");
        } else {
            return $res;
        }

        $arr = [];
        $pattern = "/<\/table>.*?<b>(.*?)<\/b> 元奖金滚入下期奖池/is";
        $res = preg_match_all($pattern, $string, $arr);
        if ($res) {
            $return['balance'] = $arr[1][0];
        } else {
            $return['balance'] = '';
        }

        return $return;
    }

    /**
     * 中奖详情再处理入库
     * @param $data array 中奖详情
     * @param $lottery string 游戏名
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detailAwardInfoSave($data, $lottery)
    {
        //拼凑中奖详情字符串（json数组）
        $len = count($data['award']);
        $detail = '[';
        if ($len % 4 == 0) {
            //排列3排列5七星彩
            for ($i = 0; $i < $len / 4 - 1; $i++) {
                $detail .= '{"awards_name":"' . $data['award'][$i * 4] . '","awards_num":"' . $data['award'][$i * 4 + 1] . '","awards_money":"' . $data['award'][$i * 4 + 2] . '"},';
            }
            $detail = substr($detail, 0, -1) . ']';
        } elseif (($len + 1) % 9 == 0) {
            //todo 竞彩官网“乐善奖”活动期间数据不一样，待处理
            //超级大乐透
            for ($i = 0; $i < $len / 9 - 1; $i++) {
                $detail .= '{"awards_name":"' . $data['award'][$i * 9] . '","awards_num":"' . $data['award'][$i * 9 + 2] . '","awards_money":"' . $data['award'][$i * 9 + 3] . '"},' . '{"awards_name":"' . $data['award'][$i * 9] . $data['award'][$i * 9 + 5] . '","awards_num":"' . $data['award'][$i * 9 + 6] . '","awards_money":"' . $data['award'][$i * 9 + 7] . '"},';
            }
            $detail .= '{"awards_name":"' . $data['award'][$i * 9] . '","awards_num":"' . $data['award'][$i * 9 + 1] . '","awards_money":"' . $data['award'][$i * 9 + 2] . '"}' . ']';
        }
        $data['wins_detail'] = ($detail === ']') ? '' : $detail;
        $data['lottery_name'] = $lottery;

        $replace = $this->where('issue', $data['issue'])
            ->where('lottery_name', $lottery)
            ->find();
        try {
            if ($replace) {
                $aa = $this->allowField(true)->update($data, ['issue' => $data['issue'], 'lottery_name' => $lottery]);
            } else {
                $aa = $this->allowField(true)->isUpdate(false)->data($data)->save();
            }
        } catch (\Exception $errorException) {
            return ['valid' => 0, 'msg' => $errorException->getMessage()];
        }
        if ($aa) {
            return ['valid' => 1, 'msg' => '操作成功'];
        } else {
            return ['valid' => 0, 'msg' => '操作失败'];
        }
    }

}