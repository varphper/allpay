<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/26
 * Time: 18:40
 * Email: varphper@gmail.com
 */

namespace app\award\model;


class Ticket extends Base
{
    protected $pk = 'ticket_id';

    /**
     * 查询获取未计算开奖结果的有效彩票
     * 彩票英文名见 tc_lottery_game 表
     * @param $lottery_name string
     * @return array
     */
    public function getValidTickets($lottery_name)
    {
        try {
            $result = $this->where('lottery_name', $lottery_name)
                ->where('bet_status', 'in', [2, 5])
                ->where('award_status', 0)
                ->select();
            if ($result->isEmpty()) {
                return ['valid' => 0, 'msg' => '暂无需要计算中奖结果的彩票'];
            } else {
                return ['valid' => 1, 'data' => $result];
            }
        } catch (\Exception $exception) {
            return ['valid' => 0, 'msg' => $exception->getMessage()];
        }
    }

}