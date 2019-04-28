<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/26
 * Time: 18:44
 * Email: varphper@gmail.com
 */

namespace app\award\model;


use think\Db;

class TicketAward extends Base
{
    protected $pk = 'award_id';
    protected $autoWriteTimestamp = true;

    /**
     * 中奖情况入库操作
     * @param $data
     * @param $ticket
     * @return array
     */
    public function saveAwardResult($data, $ticket)
    {
        $save['ticket_award_money'] = $this->totalMoney($data, $ticket);
        $save['ticket_id'] = $data[0]['ticket_id'];
        $save['ticket_award_result'] = json_encode($data);
        $save['create_time'] = time();
        $save['update_time'] = time();
        
        try {
        	Db::startTrans();// 启动事务
            Db::table('tc_ticket_award')->insert($save, true);
            Db::table('tc_ticket')->where('ticket_id', $save['ticket_id'])->update(['award_status' => 1]);
            Db::commit();// 提交事务
            return ['valid' => 1, 'msg' => '操作成功'];
        } catch (\Exception $exception) {
            Db::rollback(); // 回滚事务
            return ['valid' => 0, 'msg' => $exception->getMessage()];
        }
    }

    /**
     * 计算总奖金
     * @param $data
     * @param $ticket
     * @return float|int
     */
    public function totalMoney($data, $ticket)
    {
        if ($ticket['lottery_name'] == '超级大乐透') {
            $total_money = $this->dltMoney($data, $ticket);
        } else {
            $total_money = $this->commonMoney($data, $ticket);
        }
        return $total_money;
    }

    public function commonMoney($data, $ticket)
    {
        $len = count($data);
        $total_money = 0;
        for ($i = 0; $i < $len; $i++) {
            $total_money += $data[$i]['awards_money'] * $data[$i]['awards_num'] * $ticket['beishu'];
        }
        return $total_money;
    }

    /**
     * 大乐透总奖金计算
     * @param $data
     * @param $ticket
     * @return float|int
     */
    public function dltMoney($data, $ticket)
    {
        $len = count($data);
        $total_money = 0;
        for ($i = 0; $i < $len; $i++) {
            $total_money += $this->dltzjMoney($data[$i]) * $data[$i]['awards_num'] * $ticket['beishu'];
        }
        return $total_money;
    }

    /**
     * 大乐透追加奖金计算
     * @param $award
     * @return float
     */
    public function dltzjMoney($award)
    {
        if ($award['is_zhuijia'] && in_array($award['awards_name'], ['一等奖', '二等奖', '三等奖'])) {
            return floor($award['awards_money'] * 1.6);
        } elseif ($award['is_zhuijia'] && in_array($award['awards_name'], ['四等奖', '五等奖'])) {
            return floor($award['awards_money'] * 1.5);
        } else {
            return $award['awards_money'];
        }
    }
}