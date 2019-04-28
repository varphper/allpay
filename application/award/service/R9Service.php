<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/8/3
 * Time: 14:16
 * Email: varphper@gmail.com
 */

namespace app\award\service;


class R9Service extends Base
{
    public function dsAward($award_info, $ticket)
    {
        //开奖号码处理
        $win = explode(',', $award_info['winning_number']);
        //开奖详情处理
        $wins_detail = json_decode($award_info['wins_detail'], true);
        //单式投注内容分注
        //格式：31***3001**133|31***3001**133
        //注与注之间间隔采用 | 分隔
        $bets = explode('|', $ticket['bet_content']);
        $len = count($bets);
        $hit_num = 0;
        $awards = [];
        for ($i = 0; $i < $len; $i++) {
            for ($j = 0; $j < 14; $j++) {
                if ($win[$j] === $bets[$i][$j]) {
                    $hit_num++;
                }
            }
            if ($hit_num == 9) {
                $awards[$i]['awards_name'] = $wins_detail[2]['awards_name'];
                $awards[$i]['awards_num'] = 1;
                $awards[$i]['awards_money'] = $this->dealMoney($wins_detail[2]['awards_money']);
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
        //格式：3-10-*-*-*-30-0-0-1-*-*-1-3-3(只能任意选9场比赛投注)
        //位与位之间间隔采用 - 分隔
        $hit_num = 0;
        $bet_bits = explode('-', $ticket['bet_content']);
        for ($j = 0; $j < 14; $j++) {
            if (strpos($bet_bits[$j], $win[$j]) !== false) {
                $hit_num++;
            }
        }
        $awards = [];
        if ($hit_num == 9) {
            $awards[0]['awards_name'] = $wins_detail[2]['awards_name'];
            $awards[0]['awards_num'] = 1;
            $awards[0]['awards_money'] = $this->dealMoney($wins_detail[2]['awards_money']);
            $awards[0]['ticket_id'] = $ticket['ticket_id'];
        } else {
            $awards[0]['awards_name'] = '未中奖';
            $awards[0]['awards_num'] = 0;
            $awards[0]['awards_money'] = 0;
            $awards[0]['ticket_id'] = $ticket['ticket_id'];
        }
        return $awards;
    }
}