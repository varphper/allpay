<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/31
 * Time: 11:52
 * Email: varphper@gmail.com
 */

namespace app\award\controller;


use app\award\model\Ticket;
use app\award\model\NumberGameData;
use app\award\service\Pl5Service;
use app\award\model\TicketAward;

class Pl5Award extends Base
{
    /**
     * 排列5中奖结果计算
     * @return string
     */
    public function calculateAward()
    {
        //获取所有未计算中奖结果的排列五有效彩票,$tickets['data']
        $tickets = (new Ticket())->getValidTickets('排列5');
        if (!$tickets['valid']){
            return json_encode($tickets);
        }
        $len = count($tickets['data']);
        $result = [];
        for ($i = 0; $i < $len; $i++) {
            //查询相应期数的开奖信息 $tickets['data']
            $award_info = (new NumberGameData())->getAwardInfo($tickets['data'][$i]);
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
    public function danshi($award_info,$ticket)
    {
        $awards = (new Pl5Service())->dsAward($award_info,$ticket);
        $result = (new TicketAward())->saveAwardResult($awards,$ticket);
        return $result;
    }

    /**
     * 复式票中奖结果计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function fushi($award_info,$ticket)
    {
        $awards = (new Pl5Service())->fsAward($award_info,$ticket);
        $result = (new TicketAward())->saveAwardResult($awards,$ticket);
        return $result;
    }
}