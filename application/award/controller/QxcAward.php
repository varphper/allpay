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
use app\award\service\QxcService;
use app\award\model\TicketAward;

class QxcAward extends Base
{
    /**
     * 七星彩中奖结果计算
     * @return string
     */
    public function calculateAward()
    {
        //获取所有未计算中奖结果的七星彩有效彩票,$tickets['data']
        $tickets = (new Ticket())->getValidTickets('七星彩');
        if (!$tickets['valid']){
            return json_encode($tickets);
        }
        $len = count($tickets['data']);
        $result = [];
        for ($i = 0; $i < $len; $i++) {
            //查询相应期数的开奖信息 $tickets['data']
            $award_info = (new NumberGameData())->getAwardInfo($tickets['data'][$i]);
            if (!$award_info['valid']){
                $result[$tickets['data'][$i]['ticket_id']] = $award_info;
                continue;
            }
            //中奖结果计算
            switch ($tickets['data'][$i]['bet_type']) {
                case '1':
                    $result[$tickets['data'][$i]['ticket_id']] = $this->danshi($award_info['data'],$tickets['data'][$i]);
                    break;
                case '2':
                    $result[$tickets['data'][$i]['ticket_id']] = $this->fushi($award_info['data'],$tickets['data'][$i]);
                    break;
                default:
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
        $awards = (new QxcService())->dsAward($award_info,$ticket);
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
        $awards = (new QxcService())->fsAward($award_info,$ticket);
        $result = (new TicketAward())->saveAwardResult($awards,$ticket);
        return $result;
    }
}