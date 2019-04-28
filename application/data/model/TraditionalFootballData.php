<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/12
 * Time: 17:14
 * Email: varphper@gmail.com
 */

namespace app\data\model;


use think\Model;

class TraditionalFootballData extends Model
{
    protected $autoWriteTimestamp = true;

    /**
     * 14场胜负采集页面数据提取
     * @param $string1 string
     * @param $string2 string
     * @return array
     */
    public function game14InfoDeal($string1, $string2)
    {
        $return = [];

        $string1 = substr($string1, 11, -2);
        $data = json_decode($string1, true);
        $return['num_back'] = $data['result'];

        $string2 = substr($string2, 12, -2);
        $data2 = json_decode($string2, true);
        $return['data_back'] = $data2['result'];

        return $return;
    }

    /**
     * 14场胜负（含任选9场）开奖详情采集页面数据提取
     * @param $string
     * @return array|false|int
     */
    public function game14AwardDeal($string)
    {
        $return = [];
        $arr = [];
        $pattern = '/<option .*? selected="selected">(\d*)<\/option>';
        $pattern .= '.*?开奖日期：(.*?)  兑奖截止日期：(.*?)<\/div>';
        $pattern .= '.*?足彩胜负滚存：.*?>(.*?)<.*?元，滚入下期一等奖';
        $pattern .= '.*?<li>一等奖.*?>(\d*)<.*?注，每注 <em class="red">(.*?)<\/em>元';
        $pattern .= '.*?<li>二等奖.*?>(\d*)<.*?注，每注 <em class="red">(.*?)<\/em>元';
        $pattern .= '.*?足彩胜负销量 <em class="red">(.*?)<\/em>元';
        $pattern .= '.*?<li>任选9场.*?>(\d*)<.*?注，每注 <em class="red">(.*?)<\/em>元';
        $pattern .= '.*?任选9场销量 <em class="red">(.*?)<\/em>元';
        $pattern .= "/is";
        $res = preg_match_all($pattern, $string, $arr);
        if ($res) {
            //成功匹配$res为1
            $return['issue'] = $arr[1][0];
            $return['award_time'] = strtotime($arr[2][0]);
            $return['prize_end_date'] = strtotime($arr[3][0]);
            $return['balance'] = $arr[4][0];
            $return['first_level_num'] = $arr[5][0];
            $return['first_level_money'] = $arr[6][0];
            $return['second_level_num'] = $arr[7][0];
            $return['second_level_money'] = $arr[8][0];
            $return['sales_14'] = $arr[9][0];
            $return['r9_num'] = $arr[10][0];
            $return['r9_money'] = $arr[11][0];
            $return['sales_r9'] = $arr[12][0];
        } else {
            //出错返回$res为false，未匹配到为0
            return $res;
        }
        return $return;
    }

    /**
     * 当期14场胜负数据再处理入库
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function game14InfoSave($data)
    {

        $info = $this->getGameBaseInfo($data);
        return $this->saveGameInfo($info);
    }

    /**
     * 14场胜负详细数据再处理入库
     * 包含彩果，可抓取多场
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function game14ResultSave($data)
    {
        $info = $this->getGameBaseInfo($data);

        $info['winning_number'] = '';
        foreach ($data['data_back'] as $v) {
            if ($v['result'] != ''){
                $info['winning_number'] .= $v['result'] . ',';
            }
        }
        $info['winning_number'] = substr($info['winning_number'], 0, -1);
        return $this->saveGameInfo($info);
    }

    /**
     * 14场胜负（含任选9场）开奖详情数据入库
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function game14AwardSave($data)
    {
        $data['sales'] = json_encode(["sales_14"=>$data['sales_14'],"sales_r9"=>$data['sales_r9']]);
        $data['wins_detail'] = json_encode([
            ['awards_name'=>'一等奖','awards_num'=>$data['first_level_num'],'awards_money'=>$data['first_level_money']],
            ['awards_name'=>'二等奖','awards_num'=>$data['second_level_num'],'awards_money'=>$data['second_level_money']],
            ['awards_name'=>'任九一等奖','awards_num'=>$data['r9_num'],'awards_money'=>$data['r9_money']]
        ]);
        $data['lottery_name'] = '14场胜负';
        return $this->saveGameInfo($data);
    }

    /**
     * 14场胜负数据再处理，为入库做准备
     * @param $data
     * @return array
     */
    public function getGameBaseInfo($data)
    {
        $info = [];
        $info['issue'] = $data['num_back']['num'];
        $info['lottery_name'] = '14场胜负';
        $info['sell_start_time'] = strtotime($data['num_back']['start']);
        $info['sell_end_time'] = strtotime($data['num_back']['end']);
        $info['award_time'] = strtotime($data['num_back']['prize']);

        $i = -1;
        $match = [];
        foreach ($data['data_back'] as $v) {
            $i++;
            $match[$i]['league'] = $v['league'];
            $match[$i]['host_team'] = $v['h_cn'];
            $match[$i]['guest_team'] = $v['a_cn'];
            $match[$i]['play_time'] = $v['time'] ? $v['date'] . ' ' . $v['time'] : $v['date'];
            $match[$i]['result'] = isset($v['result']) ? $v['result'] : '';
            $match[$i]['mid'] = isset($v['mid']) ? $v['mid'] : '';
        }
        $info['match_info'] = json_encode($match);
        return $info;
    }

    /**
     * 14场胜负数据存入数据库操作
     * @param $info array
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function saveGameInfo($info)
    {
        $replace = $this->where('issue', $info['issue'])
            ->where('lottery_name', $info['lottery_name'])
            ->find();
        try {
            if ($replace) {
                $this->allowField(true)->update($info,['issue' => $info['issue'], 'lottery_name' => $info['lottery_name']]);
            } else {
                $this->allowField(true)->isUpdate(false)->data($info)->save();
            }
        } catch (\Exception $errorException) {
            return ['valid' => 0, 'msg' => $errorException->getMessage()];
        }
        return ['valid' => 1, 'msg' => '操作成功'];
    }

    /**
     * 查找14场胜负的最新期号
     * @return mixed
     */
    public function getNewIssue()
    {
        return $this->where('lottery_name', '14场胜负')->max('issue');
    }

}