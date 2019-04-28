<?php
/**
 * Description: 大乐透中奖结果计算
 * User: varphper
 * Date: 2018/7/26
 * Time: 18:01
 * Email: varphper@gmail.com
 */

namespace app\award\controller;


use app\award\service\DltService;
use app\award\model\Ticket;
use app\award\model\NumberGameData;
use app\award\model\TicketAward;

class DltAward extends Base
{
    /**
     * 大乐透中奖结果计算
     * @return string
     */
    public function calculateAward()
    {
        //获取所有未计算中奖结果的大乐透有效彩票,$tickets['data']
        $tickets = (new Ticket())->getValidTickets('超级大乐透');
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
                    $result[$tickets['data'][$i]['ticket_id']] = $this->dantuofs($award_info['data'],$tickets['data'][$i]);
                    break;
                case '3':
                    $result[$tickets['data'][$i]['ticket_id']] = $this->danshizj($award_info['data'],$tickets['data'][$i]);
                    break;
                case '4':
                    $result[$tickets['data'][$i]['ticket_id']] = $this->dantuofszj($award_info['data'],$tickets['data'][$i]);
                    break;
                case '5':
                    $result[$tickets['data'][$i]['ticket_id']] = $this->putongfs($award_info['data'],$tickets['data'][$i]);
                    break;
                case '6':
                    $result[$tickets['data'][$i]['ticket_id']] = $this->dantuofszj($award_info['data'],$tickets['data'][$i]);
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
        $awards = (new DltService())->dsAward($award_info,$ticket);
        $result = (new TicketAward())->saveAwardResult($awards,$ticket);
        return $result;
    }

    /**
     * 单式追加票中奖结果结算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function danshizj($award_info,$ticket)
    {
        return $this->danshi($award_info,$ticket);
    }

    /**
     * 普通复式中奖结果计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function putongfs($award_info,$ticket)
    {
        return $this->dantuofs($award_info,$ticket);
    }

    /**
     * 普通复式中奖结果计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function putongfszj($award_info,$ticket)
    {
        return $this->dantuofs($award_info,$ticket);
    }

    /**
     * 胆拖复式中奖结果计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function dantuofs($award_info,$ticket)
    {
        $awards = (new DltService())->dtfsAward($award_info,$ticket);
        $result = (new TicketAward())->saveAwardResult($awards,$ticket);
        return $result;
    }

    /**
     * 胆拖复式追加中奖结果计算
     * @param $award_info
     * @param $ticket
     * @return array
     */
    public function dantuofszj($award_info,$ticket)
    {
        return $this->dantuofs($award_info,$ticket);
    }
}