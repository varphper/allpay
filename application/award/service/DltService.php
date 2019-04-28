<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/26
 * Time: 18:29
 * Email: varphper@gmail.com
 */

namespace app\award\service;

use app\common\lib\Math;

class DltService extends Base
{
    /**
     * 大乐透单式中奖情况计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function dsAward($award_info, $ticket)
    {
        //开奖号码分区
        $winning = explode(',', $award_info['winning_number']);
        $winning_red = [$winning[0], $winning[1], $winning[2], $winning[3], $winning[4]];
        $winning_blue = [$winning[5], $winning[6]];

        //单式（含单式追加）投注内容分注
        //格式：
        //01 02 03 04 05 06 07|01 03 05 12 13 14 15
        //注与注之间间隔采用 | 分隔
        $bet_nums = explode('|', $ticket['bet_content']);
        $award = [];
        for ($i = 0; $i < count($bet_nums); $i++) {
            //投注号码分区
            $bet_num = explode(" ", $bet_nums[$i]);
            $bet_num_red = [$bet_num[0], $bet_num[1], $bet_num[2], $bet_num[3], $bet_num[4]];
            $bet_num_blue = [$bet_num[5], $bet_num[6]];
            //计算获奖等级
            $award[$i]['awards_name'] = $this->dsAwardLevel($winning_red, $winning_blue, $bet_num_red, $bet_num_blue);
            $award[$i]['awards_num'] = $award[$i]['awards_name'] == '未中奖' ? 0 : 1;
            //计算获奖奖金
            $award[$i]['awards_money'] = $this->dsAwardMoney($award_info, $award[$i]['awards_name']);
            $award[$i]['is_zhuijia'] = $this->isZhuijia($ticket['bet_type']);
            $award[$i]['ticket_id'] = $ticket['ticket_id'];
        }
        return $award;
    }

    /**
     * 单式中奖等级计算
     * @param $winning_red
     * @param $winning_blue
     * @param $bet_num_red
     * @param $bet_num_blue
     * @return string
     */
    public function dsAwardLevel($winning_red, $winning_blue, $bet_num_red, $bet_num_blue)
    {
        $win_red_num = 0;//猜中红球个数
        $win_blue_num = 0;//猜中蓝球个数

        for ($i = 0; $i < 5; $i++) {
            if (in_array($winning_red[$i], $bet_num_red)) {
                $win_red_num++;
            }
        }
        for ($i = 0; $i < 2; $i++) {
            if (in_array($winning_blue[$i], $bet_num_blue)) {
                $win_blue_num++;
            }
        }

        //中奖规则 http://caipiao.163.com/help/10/0726/11/6CH2ICTV00754IHH_3.html
        if (($win_red_num + $win_blue_num == 3) || ($win_red_num == 0 && $win_blue_num == 2)) {
            $level = '六等奖';
        } elseif ($win_red_num + $win_blue_num == 4) {
            $level = '五等奖';
        } elseif (($win_red_num == 4 && $win_blue_num == 1) || ($win_red_num == 3 && $win_blue_num == 2)) {
            $level = '四等奖';
        } elseif (($win_red_num == 4 && $win_blue_num == 2) || ($win_red_num == 5 && $win_blue_num == 0)) {
            $level = '三等奖';
        } elseif (($win_red_num == 5 && $win_blue_num == 1)) {
            $level = '二等奖';
        } elseif (($win_red_num == 5 && $win_blue_num == 2)) {
            $level = '一等奖';
        } else {
            $level = '未中奖';
        }
        return $level;
    }

    /**
     * 单式各等级中奖奖金提取
     * @param $award_info
     * @param $awards_name
     * @return int
     */
    public function dsAwardMoney($award_info, $awards_name)
    {
        if ($awards_name == '未中奖'){
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
     * 大乐透胆拖复式中奖计算（含其它各种复式及追加）
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function dtfsAward($award_info, $ticket)
    {
        $hits = $this->dtfsHits($award_info, $ticket);

        //未中奖处理
        if ($hits === false) {
            return [['awards_name' => '未中奖', 'awards_num' => 0, 'awards_money' => 0, 'is_zhuijia' => $this->isZhuijia($ticket['bet_type']), 'ticket_id' => $ticket['ticket_id']]];
        }

        //中奖处理
        $result = [];
        $wins_detail = json_decode($award_info['wins_detail'], true);
        $winning_list = ['一等奖', '二等奖', '三等奖', '四等奖', '五等奖', '六等奖'];
        $len = count($hits);
        for ($i = 0; $i < $len; $i++) {
            $result[$i]['awards_name'] = $winning_list[$i];
            $result[$i]['awards_num'] = $hits[$i];
            $result[$i]['awards_money'] = $this->dealMoney($wins_detail[2 * $i]['awards_money']);
            $result[$i]['is_zhuijia'] = $this->isZhuijia($ticket['bet_type']);
            $result[$i]['ticket_id'] = $ticket['ticket_id'];
        }
        return $result;
    }

    /**
     * 计算胆拖复式各等级中奖的方案注数（含其它复式）
     * @param $award_info
     * @param $ticket
     * @return array|bool
     */
    public function dtfsHits($award_info, $ticket)
    {
        //开奖号码分区
        $win = explode(',', $award_info['winning_number']);
        $f_win = [$win[0], $win[1], $win[2], $win[3], $win[4]];
        $b_win = [$win[5], $win[6]];

        //胆拖复式（含普通复式及各自的追加）投注内容分区
        //格式：前胆|前拖|后胆|后拖
        //普通复式格式例子 0|01 02 03 04 05 06|0|01 02 胆的位置写0
        $bet_content = explode('|', $ticket['bet_content']);
        $f_req = explode(" ", $bet_content[0]);//前胆
        $f_opt = explode(" ", $bet_content[1]);//前拖
        $b_req = explode(" ", $bet_content[2]);//后胆
        $b_opt = explode(" ", $bet_content[3]);//后拖

        //计算投注内容中各部分数字个数
        //普通复式得到的$f_req和$b_req都是数组[0]
        $f_req_num = in_array(0, $f_req) ? 0 : count($f_req);
        $f_opt_num = count($f_opt);
        $b_req_num = in_array(0, $b_req) ? 0 : count($b_req);
        $b_opt_num = count($b_opt);

        //计算投注内容中各部分数字命中个数
        $f_req_hit_num = 0;//前胆中的个数
        $f_opt_hit_num = 0;//前拖中的个数
        $b_req_hit_num = 0;//后胆中的个数
        $b_opt_hit_num = 0;//后拖中的个数
        for ($i = 0; $i < 5; $i++) {
            if (in_array($f_win[$i], $f_req)) {
                $f_req_hit_num++;
            }
            if (in_array($f_win[$i], $f_opt)) {
                $f_opt_hit_num++;
            }
        }
        for ($i = 0; $i < 2; $i++) {
            if (in_array($b_win[$i], $b_req)) {
                $b_req_hit_num++;
            }
            if (in_array($b_win[$i], $b_opt)) {
                $b_opt_hit_num++;
            }
        }

        $f_hit_num = $f_req_hit_num + $f_opt_hit_num;
        $b_hit_num = $b_req_hit_num + $b_opt_hit_num;

        if (($f_hit_num < 2 && $b_hit_num < 2) || ($f_hit_num == 2 && $b_hit_num == 0)) {
            return false;//未中奖
        }

        $f_hits = $this->dtfsSolveHits(5, $f_req_num, $f_opt_num, $f_req_hit_num, $f_opt_hit_num);//前区命中指定个数的方案注数
        $b_hits = $this->dtfsSolveHits(2, $b_req_num, $b_opt_num, $b_req_hit_num, $b_opt_hit_num);//后区命中指定个数的方案注数

        return $this->dtfsWinResult($f_hits, $b_hits);//各等级中奖的方案注数
    }

    /**
     * 计算胆拖复式前区或后区命中指定个数的方案注数（含其它复式）
     * @param $num
     * @param $req_num
     * @param $opt_num
     * @param $req_hit_num
     * @param $opt_hit_num
     * @return array
     */
    public function dtfsSolveHits($num, $req_num, $opt_num, $req_hit_num, $opt_hit_num)
    {
        $opt_left = $num - $req_num;//拖区可以选择的个数
        $opt_miss = $opt_num - $opt_hit_num;//拖区未命中个数
        $max = $req_hit_num + $opt_hit_num;//胆拖可选择的命中数个数最大值
        $hits = [];
        for ($i = 0; $i <= $num; ++$i) {
            //$i为各种中奖情况中的命中个数
            if ($i < $req_hit_num || $i > $max) {
                $hits[$i] = 0;
            } else {
                $opt_need = $i - $req_hit_num;//拖区中可以选择的命中数的个数
                $math_service = new Math();
                $hits[$i] = $math_service->combine($opt_hit_num, $opt_need) * $math_service->combine($opt_miss, $opt_left - $opt_need);
            }
        }
        return $hits;
    }

    /**
     * 计算胆拖复式各等级的中奖注数（含其它复式）
     * @param $f_hits
     * @param $b_hits
     * @return array
     */
    public function dtfsWinResult($f_hits, $b_hits)
    {
        $winners = [
            [[5, 2]],
            [[5, 1]],
            [[5, 0], [4, 2]],
            [[4, 1], [3, 2]],
            [[4, 0], [3, 1], [2, 2]],
            [[3, 0], [1, 2], [2, 1], [0, 2]]
        ];
        $result = [];

        for ($i = 0; $i < count($winners); ++$i) {
            $winner = $winners[$i];
            $count = 0;
            for ($j = 0; $j < count($winner); ++$j) {
                $item = $winner[$j];
                $count += $f_hits[$item[0]] * $b_hits[$item[1]];
            }
            $result[$i] = $count;
        }
        return $result;
    }

    /**
     * 是否是追加玩法
     * @param $bet_type
     * @return string
     */
    public function isZhuijia($bet_type)
    {
        return in_array($bet_type, [3, 4, 6]) ? '1' : '0';
    }
}