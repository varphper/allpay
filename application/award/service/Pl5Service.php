<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/8/2
 * Time: 18:00
 * Email: varphper@gmail.com
 */

namespace app\award\service;


class Pl5Service extends Base
{
    public function dsAward($award_info, $ticket)
    {
        //开奖号码处理
        $win = str_replace(',', '', $award_info['winning_number']);

        //单式投注内容分注
        //格式：12333|47856|73289|11452|19736
        //注与注之间间隔采用 | 分隔
        $bet_nums = explode('|', $ticket['bet_content']);
        $len = count($bet_nums);
        $awards = [];
        for ($i = 0; $i < $len; $i++) {
            if ($bet_nums[$i] == $win) {
                $awards[$i]['awards_name'] = '一等奖';
                $awards[$i]['awards_num'] = 1;
                $awards[$i]['awards_money'] = 100000;
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

    public function fsAward($award_info, $ticket)
    {
        //开奖号码处理
        $win = explode(',', $award_info['winning_number']);

        //复式投注内容分注
        //格式：123|47|739|1452|136
        //位与位之间间隔采用 | 分隔
        $bet_nums = explode('|', $ticket['bet_content']);
        $hit = '';
        for ($i = 0; $i < 5; $i++) {
            if (strpos($bet_nums[$i],$win[$i]) !== false) {
                $hit .= '1';
            }
        }
        $awards = [];
        if ($hit === '11111'){
            $awards[0]['awards_name'] = '一等奖';
            $awards[0]['awards_num'] = 1;
            $awards[0]['awards_money'] = 100000;
            $awards[0]['ticket_id'] = $ticket['ticket_id'];
        }else{
            $awards[0]['awards_name'] = '未中奖';
            $awards[0]['awards_num'] = 0;
            $awards[0]['awards_money'] = 0;
            $awards[0]['ticket_id'] = $ticket['ticket_id'];
        }
        return $awards;
    }
}