<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/8/1
 * Time: 17:54
 * Email: varphper@gmail.com
 */

namespace app\award\service;


class Pl3Service extends Base
{
    public function zhxdsAward($award_info, $ticket)
    {
        //开奖号码处理
        $win = str_replace(',', '', $award_info['winning_number']);

        //单式投注内容分注
        //格式：123|456|789|112|136
        //注与注之间间隔采用 | 分隔
        $bet_nums = explode('|', $ticket['bet_content']);
        $len = count($bet_nums);
        $awards = [];
        for ($i = 0; $i < $len; $i++) {
            if ($bet_nums[$i] == $win) {
                $awards[$i]['awards_name'] = '直选';
                $awards[$i]['awards_num'] = 1;
                $awards[$i]['awards_money'] = 1040;
                $awards[$i]['ticket_id'] = $ticket['ticket_id'];
            } else {
                $awards[$i]['awards_name'] = '未中奖';
                $awards[$i]['awards_num'] = 0;
                $awards[$i]['awards_money'] = 0;
                $awards[$i]['ticket_id'] = $ticket['ticket_id'];
            }
        }
        return $awards;
    }

    public function zhxfsAward($award_info, $ticket)
    {
        //开奖号码处理
        $win = explode(',', $award_info['winning_number']);

        //单式投注内容分注
        //格式：123|456|789
        //位与位之间间隔采用 | 分隔
        $bet_nums = explode('|', $ticket['bet_content']);
        $awards = [];
        $hit = '';
        for ($i = 0; $i < 3; $i++) {
            if (strpos($bet_nums[$i], $win[$i]) !== false) {
                $hit .= '1';
            }
        }
        if ($hit == '111') {
            $awards[0]['awards_name'] = '直选';
            $awards[0]['awards_num'] = 1;
            $awards[0]['awards_money'] = 1040;
            $awards[0]['ticket_id'] = $ticket['ticket_id'];
        } else {
            $awards[0]['awards_name'] = '未中奖';
            $awards[0]['awards_num'] = 0;
            $awards[0]['awards_money'] = 0;
            $awards[0]['ticket_id'] = $ticket['ticket_id'];
        }
        return $awards;
    }

    public function zxAward($award_info, $ticket)
    {
        //开奖号码处理
        $win = explode(',', $award_info['winning_number']);
        sort($win);
        //判断开奖结果是组三还是组六
        if (($win[0] == $win[1] && $win[1] != $win[2]) || ($win[1] == $win[2] && $win[0] != $win[1])) {
            $flag = 1;//组三
        } elseif ($win[0] != $win[1] && $win[1] != $win[2]) {
            $flag = 0;//组六
        } else {
            //三个数字相同，只能直选中
            $awards[0]['awards_name'] = '未中奖';
            $awards[0]['awards_num'] = 0;
            $awards[0]['awards_money'] = 0;
            $awards[0]['ticket_id'] = $ticket['ticket_id'];
            return $awards;
        }
        //组选投注内容分注
        //格式：123|456|789
        //位与位之间间隔采用 | 分隔
        $bet_nums = explode('|', $ticket['bet_content']);
        $len = count($bet_nums);
        $awards = [];
        for ($i = 0; $i < $len; $i++) {
            $bet_temp[$i] = str_split($bet_nums[$i], 1);
            sort($bet_temp[$i]);
            if ($win === $bet_temp[$i]) {
                if ($flag === 1) {
                    //组三中奖
                    $awards[$i]['awards_name'] = '组选3';
                    $awards[$i]['awards_num'] = 1;
                    $awards[$i]['awards_money'] = 346;
                    $awards[$i]['ticket_id'] = $ticket['ticket_id'];
                } else {
                    //组六中奖
                    $awards[$i]['awards_name'] = '组选6';
                    $awards[$i]['awards_num'] = 1;
                    $awards[$i]['awards_money'] = 173;
                    $awards[$i]['ticket_id'] = $ticket['ticket_id'];
                }
            } else {
                $awards[$i]['awards_name'] = '未中奖';
                $awards[$i]['awards_num'] = 0;
                $awards[$i]['awards_money'] = 0;
                $awards[$i]['ticket_id'] = $ticket['ticket_id'];
            }
        }
        return $awards;
    }

    public function zsfsAward($award_info, $ticket)
    {
        //开奖号码处理
        $win = explode(',', $award_info['winning_number']);
        sort($win);
        $awards = [];
        //判断开奖结果是不是组三，如果是组三，开奖结果数组去掉重复数字
        if ($win[0] == $win[1] && $win[1] != $win[2]) {
            array_shift($win);
        } elseif ($win[0] != $win[1] && $win[1] == $win[2]) {
            array_pop($win);
        } else {
            $awards[0]['awards_name'] = '未中奖';
            $awards[0]['awards_num'] = 0;
            $awards[0]['awards_money'] = 0;
            $awards[0]['ticket_id'] = $ticket['ticket_id'];
            return $awards;
        }
        //组三复式投注内容分析
        //格式：直接输入数字 如 1234
        $bet_nums = str_split($ticket['bet_content']);
        if (in_array($win[0], $bet_nums) && in_array($win[1], $bet_nums)) {
            //组三中奖
            $awards[0]['awards_name'] = '组选3';
            $awards[0]['awards_num'] = 1;
            $awards[0]['awards_money'] = 346;
            $awards[0]['ticket_id'] = $ticket['ticket_id'];
        } else {
            $awards[0]['awards_name'] = '未中奖';
            $awards[0]['awards_num'] = 0;
            $awards[0]['awards_money'] = 0;
            $awards[0]['ticket_id'] = $ticket['ticket_id'];
        }
        return $awards;
    }

    public function zlfsAward($award_info, $ticket)
    {
        //开奖号码处理
        $win = explode(',', $award_info['winning_number']);
        $awards = [];
        //判断开奖结果是不是组六的
        if ($win[0] == $win[1] || $win[1] == $win[2] || $win[0] == $win[2]) {
            $awards[0]['awards_name'] = '未中奖';
            $awards[0]['awards_num'] = 0;
            $awards[0]['awards_money'] = 0;
            $awards[0]['ticket_id'] = $ticket['ticket_id'];
            return $awards;
        }
        //组六复式投注内容分析
        //格式：直接输入数字 如 1234
        $bet_nums = str_split($ticket['bet_content']);
        if (in_array($win[0], $bet_nums) && in_array($win[1], $bet_nums) && in_array($win[2], $bet_nums)) {
            //组六中奖
            $awards[0]['awards_name'] = '组选6';
            $awards[0]['awards_num'] = 1;
            $awards[0]['awards_money'] = 173;
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