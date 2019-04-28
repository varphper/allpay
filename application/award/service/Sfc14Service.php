<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/8/3
 * Time: 14:16
 * Email: varphper@gmail.com
 */

namespace app\award\service;


use app\common\lib\Math;

class Sfc14Service extends Base
{
    public function dsAward($award_info, $ticket)
    {
        //开奖号码处理
        $win = explode(',', $award_info['winning_number']);
        //开奖详情处理
        $wins_detail = json_decode($award_info['wins_detail'], true);
        //单式投注内容分注
        //格式：12333|47856|73289|11452|19736
        //注与注之间间隔采用 | 分隔
        $bets = explode('|', $ticket['bet_content']);
        $len = count($bets);
        $hit_num = 0;
        $awards = [];
        for ($i = 0; $i < $len; $i++) {
            for ($j = 0; $j < 14; $j++) {
                if ($win[$j] == $bets[$i][$j]) {
                    $hit_num++;
                }
            }
            if ($hit_num == 14) {
                $awards[$i]['awards_name'] = $wins_detail[0]['awards_name'];
                $awards[$i]['awards_num'] = 1;
                $awards[$i]['awards_money'] = $this->dealMoney($wins_detail[0]['awards_money']);
                $awards[$i]['ticket_id'] = $ticket['ticket_id'];
            } elseif ($hit_num == 13) {
                $awards[$i]['awards_name'] = $wins_detail[1]['awards_name'];
                $awards[$i]['awards_num'] = 1;
                $awards[$i]['awards_money'] = $this->dealMoney($wins_detail[1]['awards_money']);
                $awards[$i]['ticket_id'] = $ticket['ticket_id'];
            } else {
                $awards[$i]['awards_name'] = '未中奖';
                $awards[$i]['awards_num'] = 0;
                $awards[$i]['awards_money'] = 0;
                $awards[$i]['ticket_id'] = $ticket['ticket_id'];
            }
            $hit_num = 0;
        }
        return $awards;
    }

    public function fsAward($award_info, $ticket)
    {
        //开奖号码处理
        $win = explode(',', $award_info['winning_number']);
        //开奖详情处理
        $wins_detail = json_decode($award_info['wins_detail'], true);
        //单式投注内容分拆
        //格式：1-31-301-0-0-3-1-3-3-1-1-0-3-1
        //位与位之间间隔采用 - 分隔
        $hits = [];
        $awards = [];
        $bet_bits = explode('-', $ticket['bet_content']);
        for ($j = 0; $j < 14; $j++) {
            if (strpos($bet_bits[$j], $win[$j]) !== false) {
                $hits[$j][] = 'T';
                $bit_len = strlen($bet_bits[$j]);
                for ($m = 0; $m < $bit_len - 1; $m++) {
                    $hits[$j][] = 'F';
                }
            } else {
                $bit_len = strlen($bet_bits[$j]);
                for ($m = 0; $m < $bit_len; $m++) {
                    $hits[$j][] = 'F';
                }
            }
        }
        //组合求出所有投注可能情况
        $hits_temp = (new Math())->cartesian($hits);
        $level = [0, 0];
        foreach ($hits_temp as $hit) {
            $hit_num = substr_count($hit, 'T');
            if ($hit_num == 14) {
                $level[0]++;
            } elseif ($hit_num == 13) {
                $level[1]++;
            }
        }
        //奖金获取
        if ($level == [0, 0]) {
            return ['awards_name' => '未中奖', 'awards_num' => 0, 'awards_money' => 0, 'ticket_id' => $ticket['ticket_id']];
        }
        for ($n = 0; $n < 2; $n++) {
            $awards[$n]['awards_name'] = $wins_detail[$n]['awards_name'];
            $awards[$n]['awards_num'] = $level[$n];
            $awards[$n]['awards_money'] = $this->dealMoney($wins_detail[$n]['awards_money']);
            $awards[$n]['ticket_id'] = $ticket['ticket_id'];
        }
        return $awards;
    }
}