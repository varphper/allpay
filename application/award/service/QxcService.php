<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/31
 * Time: 12:50
 * Email: varphper@gmail.com
 */

namespace app\award\service;


use app\common\lib\Math;

class QxcService extends Base
{
    /**
     * 七星彩单式中奖结果计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function dsAward($award_info, $ticket)
    {
        //开奖号码分区
        $win = explode(',', $award_info['winning_number']);

        //单式投注内容分注
        //格式：
        //1233348|4785656|7322389|11424552|1956736
        //注与注之间间隔采用 | 分隔
        $bet_nums = explode('|', $ticket['bet_content']);

        $award = [];
        for ($i = 0; $i < count($bet_nums); $i++) {
            //计算获奖等级
            $award[$i]['awards_name'] = $this->dsAwardLevel($win, $bet_nums[$i]);
            $award[$i]['awards_num'] = $award[$i]['awards_name'] == '未中奖' ? 0 : 1;
            //计算获奖奖金
            $award[$i]['awards_money'] = $this->dsAwardMoney($award_info, $award[$i]['awards_name']);
            $award[$i]['ticket_id'] = $ticket['ticket_id'];
        }
        return $award;
    }

    /**
     * 七星彩单式中奖等级计算
     * @param $win
     * @param $bet_num
     * @return string
     */
    public function dsAwardLevel($win, $bet_num)
    {
        $hit = '';
        for ($i = 0; $i < 7; $i++) {
            if ($win[$i] == $bet_num[$i]) {
                $hit .= '1';
            } else {
                $hit .= '0';
            }
        }
        if (strpos($hit, '1111111') !== false) {
            return '一等奖';
        } elseif (strpos($hit, '111111') !== false) {
            return '二等奖';
        } elseif (strpos($hit, '11111') !== false) {
            return '三等奖';
        } elseif (strpos($hit, '1111') !== false) {
            return '四等奖';
        } elseif (strpos($hit, '111') !== false) {
            return '五等奖';
        } elseif (strpos($hit, '11') !== false) {
            return '六等奖';
        } else {
            return '未中奖';
        }
    }

    /**
     * 七星彩中奖各等级奖金
     * @param $award_info
     * @param $awards_name
     * @return int
     */
    public function dsAwardMoney($award_info, $awards_name)
    {
        if ($awards_name == '未中奖') {
            return 0;
        }

        $wins_detail = json_decode($award_info['wins_detail'], true);
        $len = count($wins_detail);
        for ($i = 0; $i < $len; $i++) {
            if ($wins_detail[$i]['awards_name'] == $awards_name) {
                $awards_money = $this->dealMoney($wins_detail[$i]['awards_money']);
                return $awards_money;
            }
        }
    }

    /**
     * 七星彩复式中奖结果计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function fsAward($award_info, $ticket)
    {
        //中奖等级及各等级注数计算
        $level = $this->fsAwardLevel($award_info, $ticket);
        //未中奖处理
        if ($level == [0,0,0,0,0,0]) {
            return [['awards_name' => '未中奖', 'awards_num' => 0, 'awards_money' => 0, 'ticket_id' => $ticket['ticket_id']]];
        }
        //中奖处理
        $result = [];
        $wins_detail = json_decode($award_info['wins_detail'], true);
        $winning_list = ['一等奖', '二等奖', '三等奖', '四等奖', '五等奖', '六等奖'];
        $len = count($level);
        for ($i = 0; $i < $len; $i++) {
            $result[$i]['awards_name'] = $winning_list[$i];
            $result[$i]['awards_num'] = $level[$i];
            $result[$i]['awards_money'] = $this->dealMoney($wins_detail[$i]['awards_money']);
            $result[$i]['ticket_id'] = $ticket['ticket_id'];
        }
        return $result;
    }

    /**
     * 七星彩复式中奖等级及各等级注数计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function fsAwardLevel($award_info, $ticket)
    {
        //开奖号码分区
        $win = explode(',', $award_info['winning_number']);
        //复式投注内容分析
        //格式：12|56|73|145|197|15|195
        //位与位之间间隔采用 | 分隔
        $bet_nums = explode('|', $ticket['bet_content']);
        $hits = [];
        //将投注内容转给01数字，1代表猜中号，0代表未猜中号
        for ($i = 0; $i < 7; $i++) {
            if (strpos($bet_nums[$i], $win[$i]) !== false) {
                $hits[$i][] = 1;
                $len = strlen($bet_nums[$i]);
                for ($j = 0; $j < $len - 1; $j++) {
                    $hits[$i][] = 0;
                }
            } else {
                $len = strlen($bet_nums[$i]);
                for ($j = 0; $j < $len; $j++) {
                    $hits[$i][] = 0;
                }
            }
        }
        //组合求出所有投注可能情况
        $hits_temp = (new Math())->cartesian($hits);
        //计算中奖等级及各等级注数
        $result = [0,0,0,0,0,0];
        foreach ($hits_temp as $hit) {
            if (strpos($hit, '1111111') !== false) {
                $result[0]++;
            } elseif (strpos($hit, '111111') !== false) {
                $result[1]++;
            } elseif (strpos($hit, '11111') !== false) {
                $result[2]++;
            } elseif (strpos($hit, '1111') !== false) {
                $result[3]++;
            } elseif (strpos($hit, '111') !== false) {
                $result[4]++;
            } elseif (strpos($hit, '11') !== false) {
                $result[5]++;
            }
        }
        return $result;
    }
}