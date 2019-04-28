<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/30
 * Time: 9:42
 * Email: varphper@gmail.com
 */

namespace app\award\model;


class NumberGameData extends Base
{
    protected $pk = 'id';

    /**
     * 根据期号及彩种名查询当期开奖结果
     * @param $ticket
     * @return array
     */
    public function getAwardInfo($ticket)
    {
        $issue = $this->getIssue($ticket);
        try{
            $result =  $this->where('issue', $issue)
                ->where('lottery_name',$ticket['lottery_name'])
                ->where('award_time', '<', time())
                ->where('winning_number', '<>', '')
                ->find();
            if ($result['wins_detail']){
                return ['valid'=>1,'data'=>$result];
            }else{
                return ['valid'=>0,'msg'=>'暂无开奖信息'];
            }
        }catch (\Exception $exception){
            return ['valid'=>0,'msg'=>$exception->getMessage()];
        }
    }

    /**
     * 获取彩票的期号
     * @param $ticket
     * @return mixed
     */
    public function getIssue($ticket)
    {
        $ticket_extend = json_decode($ticket['ticket_extend'], true);
        return $ticket_extend[0]['issue'];
    }

}