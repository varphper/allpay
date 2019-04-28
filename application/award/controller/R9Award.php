<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/8/3
 * Time: 14:14
 * Email: varphper@gmail.com
 */

namespace app\award\controller;

use app\award\model\Ticket;
use app\award\service\R9Service;
use app\award\model\TicketAward;
use app\award\model\TraditionalFootballData;

class R9Award extends Base
{
    /**
     * 任选九中奖结果计算
     * @return string
     */
    public function calculateAward()
    {
        //获取所有未计算中奖结果的任九有效彩票,$tickets['data']
        $tickets = (new Ticket())->getValidTickets('任选9场');
        if (!$tickets['valid']) {
            return json_encode($tickets);
        }
        $len = count($tickets['data']);
        $result = [];
        for ($i = 0; $i < $len; $i++) {
            //查询相应期数的开奖信息 $tickets['data']
            $award_info = (new TraditionalFootballData())->getAwardInfo($tickets['data'][$i]);
            if (!$award_info['valid']) {
                $result[$tickets['data'][$i]['ticket_id']] = $award_info;
                continue;
            }
            //中奖结果计算
            if ($tickets['data'][$i]['bet_type'] === '1') {
                $result[$tickets['data'][$i]['ticket_id']] = $this->danshi($award_info['data'], $tickets['data'][$i]);
            } elseif ($tickets['data'][$i]['bet_type'] === '2') {
                $result[$tickets['data'][$i]['ticket_id']] = $this->fushi($award_info['data'], $tickets['data'][$i]);
            } else {
                $result[$tickets['data'][$i]['ticket_id']] = '模式代码不支持';
            }
        }
        return json_encode($result);
    }

    /**
     * 单式票中奖结果计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function danshi($award_info, $ticket)
    {
        $awards = (new R9Service())->dsAward($award_info, $ticket);
        $result = (new TicketAward())->saveAwardResult($awards, $ticket);
        return $result;
    }

    /**
     * 复式票中奖结果计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function fushi($award_info, $ticket)
    {
        $awards = (new R9Service())->fsAward($award_info, $ticket);
        $result = (new TicketAward())->saveAwardResult($awards, $ticket);
        return $result;
    }

}