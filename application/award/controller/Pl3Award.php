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
use app\award\service\Pl3Service;
use app\award\model\TicketAward;

class Pl3Award extends Base
{
    /**
     * 排列3中奖结果计算
     * @return string
     */
    public function calculateAward()
    {
        //获取所有未计算中奖结果的排列三有效彩票,$tickets['data']
        $tickets = (new Ticket())->getValidTickets('排列3');
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
                $result[$tickets['data'][$i]['ticket_id']] = $this->zhxds($award_info['data'], $tickets['data'][$i]);
            } elseif ($tickets['data'][$i]['bet_type'] === '2') {
                $result[$tickets['data'][$i]['ticket_id']] = $this->zhxfs($award_info['data'], $tickets['data'][$i]);
            } elseif ($tickets['data'][$i]['bet_type'] === '3') {
                $result[$tickets['data'][$i]['ticket_id']] = $this->zx($award_info['data'], $tickets['data'][$i]);
            } elseif ($tickets['data'][$i]['bet_type'] === '03') {
                $result[$tickets['data'][$i]['ticket_id']] = $this->zsfs($award_info['data'], $tickets['data'][$i]);
            } elseif ($tickets['data'][$i]['bet_type'] === '04') {
                $result[$tickets['data'][$i]['ticket_id']] = $this->zlfs($award_info['data'], $tickets['data'][$i]);
            } else {
                $result[$tickets['data'][$i]['ticket_id']] = '模式代码不支持';
            }
        }
        return json_encode($result);
    }

    /**
     * 直选单式票中奖结果计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function zhxds($award_info,$ticket)
    {
        $awards = (new Pl3Service())->zhxdsAward($award_info,$ticket);
        $result = (new TicketAward())->saveAwardResult($awards,$ticket);
        return $result;
    }

    /**
     * 直选复式票中奖结果计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function zhxfs($award_info,$ticket)
    {
        $awards = (new Pl3Service())->zhxfsAward($award_info,$ticket);
        $result = (new TicketAward())->saveAwardResult($awards,$ticket);
        return $result;
    }

    /**
     * 组选单式票中奖结果计算
     * 包含组三组六单式
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function zx($award_info,$ticket)
    {
        $awards = (new Pl3Service())->zxAward($award_info,$ticket);
        $result = (new TicketAward())->saveAwardResult($awards,$ticket);
        return $result;
    }

    /**
     * 组三复式票中奖结果计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function zsfs($award_info,$ticket)
    {
        $awards = (new Pl3Service())->zsfsAward($award_info,$ticket);
        $result = (new TicketAward())->saveAwardResult($awards,$ticket);
        return $result;
    }

    /**
     * 组六复式票中奖结果计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function zlfs($award_info,$ticket)
    {
        $awards = (new Pl3Service())->zlfsAward($award_info,$ticket);
        $result = (new TicketAward())->saveAwardResult($awards,$ticket);
        return $result;
    }

}