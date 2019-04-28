<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/19
 * Time: 20:55
 * Email: varphper@gmail.com
 */

namespace app\data\model;

use think\Db;
use think\Model;

class SportteryFootballData extends Model
{
    protected $autoWriteTimestamp = true;

    /**
     * 竞彩足球混合过关信息（简）整理
     * @param $string
     * @return false|int|mixed
     */
    public function hhggInfoDeal($string)
    {
        $pattern = "/var data=(\[.*?\]);getData/is";
        $arr = [];
        $res = preg_match($pattern, $string, $arr);
        if ($res) {
            //成功匹配$res为1
            return json_decode($arr[1], true);
        } else {
            //出错返回$res为false，未匹配到为0
            return $res;
        }
    }

    /**
     * 竞彩足球混合过关（简）信息入库
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function hhggInfoSave($data)
    {
        $arr = [];
        for ($i = 0; $i < count($data); $i++) {
            $arr[$i]['match_id'] = $data[$i][0][4];
            $arr[$i]['game_num'] = mb_substr($data[$i][0][0], 2);
            $arr[$i]['week_day'] = mb_substr($data[$i][0][0], 0, 2);
            $arr[$i]['sell_date'] = $data[$i][0][11];
            $arr[$i]['our_match_id'] = $arr[$i]['sell_date'] . '#' . $arr[$i]['game_num'];
            $arr[$i]['league'] = $data[$i][0][6];
            $arr[$i]['league_short'] = $data[$i][0][1];
            $arr[$i]['host_team'] = $data[$i][0][7];
            $arr[$i]['host_team_id'] = $data[$i][0][12];
            $arr[$i]['guest_team'] = $data[$i][0][8];
            $arr[$i]['guest_team_id'] = $data[$i][0][13];
            $arr[$i]['play_time'] = strtotime($data[$i][0][3]);
            $arr[$i]['is_hhgg'] = 1;

            $replace = $this->where('our_match_id', $arr[$i]['our_match_id'])->find();

            try {
                if ($replace) {
                    $this->allowField(true)->update($arr[$i], ['our_match_id' => $arr[$i]['our_match_id']]);
                } else {
                    $this->allowField(true)->isUpdate(false)->data($arr[$i])->save();
                }
            } catch (\Exception $errorException) {
                return ['valid' => 0, 'msg' => $errorException->getMessage()];
            }
        }

        return ['valid' => 1, 'msg' => '操作成功'];
    }

    /**
     * 竞彩足球各种玩法信息（全）处理
     * @param $string
     * @return array
     */
    public function allInfoDeal($string)
    {
        $return = [];

        $string = substr($string, 8, -2);
        $data = json_decode($string, true);

        $i = -1;
        foreach ($data['data'] as $key => $value) {
            $i++;
            $return[$i]['match_id'] = $value['id'];
            $return[$i]['game_num'] = mb_substr($value['num'], 2);
            $return[$i]['week_day'] = mb_substr($value['num'], 0, 2);
            $return[$i]['sell_date'] = $value['b_date'];
            $return[$i]['our_match_id'] = $return[$i]['sell_date'] . '#' . $return[$i]['game_num'];
            $return[$i]['is_selling'] = $value['status'] == 'Selling' ? 1 : 0;
            $return[$i]['league'] = $value['l_cn'];
            $return[$i]['league_short'] = $value['l_cn_abbr'];
            $return[$i]['league_id'] = $value['l_id'];
            $return[$i]['host_team'] = $value['h_cn'];
            $return[$i]['host_team_short'] = $value['h_cn_abbr'];
            $return[$i]['host_team_id'] = $value['h_id'];
            $return[$i]['host_team_order'] = $value['h_order'];
            $return[$i]['guest_team'] = $value['a_cn'];
            $return[$i]['guest_team_short'] = $value['a_cn_abbr'];
            $return[$i]['guest_team_id'] = $value['a_id'];
            $return[$i]['guest_team_order'] = $value['a_order'];
            $return[$i]['play_time'] = strtotime($value['date'] . $value['time']);
            $return[$i]['rqspf_rq'] = $value['hhad']['fixedodds'];
            $return[$i]['weather'] = $value['weather'];

            $pic = isset($value['weather_pic']) ? $value['weather_pic'] : '';
            $return[$i]['weather_pic'] = $pic ? substr($pic, strrpos($pic, '/')) : '';
            $return[$i]['weather_pic'] = $return[$i]['weather_pic'] ? 'weather' . $return[$i]['weather_pic'] : '';

            $return[$i]['city'] = substr($value['weather_city'], 0, strpos($value['weather_city'], '|'));
            $return[$i]['temperature'] = $value['temperature'];
            $return[$i]['official_last_updated_time'] = strtotime($data['status']['last_updated']);
            $return[$i]['spf_sp_gg'] = isset($value['had']) ? $this->packSpfInfo($value['had']) : '';
            $return[$i]['rqspf_sp_gg'] = isset($value['hhad']) ? $this->packSpfInfo($value['hhad']) : '';
            $return[$i]['bf_sp_gg'] = isset($value['crs']) ? $this->packBfInfo($value['crs']) : '';
            $return[$i]['zjq_sp_gg'] = isset($value['ttg']) ? $this->packZjqInfo($value['ttg']) : '';
            $return[$i]['bqspf_sp_gg'] = isset($value['hafu']) ? $this->packBqspfInfo($value['hafu']) : '';
        }
        return $return;
    }

    /**
     * 竞彩足球各种玩法信息入库
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function allInfoSave($data)
    {
        for ($i = 0; $i < count($data); $i++) {
            $replace = $this->where('our_match_id', $data[$i]['our_match_id'])->find();
            $data[$i]['update_time'] = time();

            $log_data = [
                'match_id' => $data[$i]['match_id'],
                'our_match_id' => $data[$i]['our_match_id'],
                'spf_sp_gg' => $data[$i]['spf_sp_gg'],
                'rqspf_sp_gg' => $data[$i]['rqspf_sp_gg'],
                'bf_sp_gg' => $data[$i]['bf_sp_gg'],
                'zjq_sp_gg' => $data[$i]['zjq_sp_gg'],
                'bqspf_sp_gg' => $data[$i]['bqspf_sp_gg'],
                'official_last_updated_time' => $data[$i]['official_last_updated_time'],
                'create_time' => time()
            ];

            if ($replace) {
                if ($replace['official_last_updated_time'] != $data[$i]['official_last_updated_time']) {
                    Db::startTrans();
                    try {
                        //更新tc_sporttery_football_data和tc_sporttery_football_sp_log表
                        //这里用的都是数据库方法而非模型方法，所以时间戳不会自动更新
                        Db::table('tc_sporttery_football_data')->where('our_match_id', $data[$i]['our_match_id'])->update($data[$i]);
                        Db::table('tc_sporttery_football_sp_log')->insert($log_data);
                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                        return ['valid' => 0, 'msg' => $e->getMessage()];
                    }
                }
            } else {
                Db::startTrans();
                try {
                    $data[$i]['create_time'] = time();
                    Db::table('tc_sporttery_football_data')->insert($data[$i]);
                    Db::table('tc_sporttery_football_sp_log')->insert($log_data);
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    return ['valid' => 0, 'msg' => $e->getMessage()];
                }
            }
        }

        return ['valid' => 1, 'msg' => '操作成功'];
    }

    /**
     * 竞彩足球胜平负(含让球胜平负)玩法赔率等信息生成json
     * game_type对应代码由硬件手册查得
     * @param $data
     * @return string
     */
    public function packSpfInfo($data)
    {
        $return = [];
        if (isset($data['fixedodds']) && $data['fixedodds'] != '') {
            $return['game_type'] = '56';//足彩让球胜平负
        } else {
            $return['game_type'] = '51';//足彩胜平负
        }
        $return['win_sp'] = $data['h'];
        $return['draw_sp'] = $data['d'];
        $return['lose_sp'] = $data['a'];
        $return['p_id'] = $data['p_id'];
        $return['if_selling'] = ($data['p_status'] == 'Selling') ? '1' : '0';
        $return['if_single'] = $data['single'];
        $return['win_sp_trend'] = $data['h_trend'];
        $return['draw_sp_trend'] = $data['d_trend'];
        $return['lose_sp_trend'] = $data['a_trend'];
        $return['rqspf_rq'] = isset($data['fixedodds']) ? $data['fixedodds'] : '';
        return json_encode($return);
    }

    /**
     * 竞彩足球比分玩法赔率等信息生成json
     * @param $data
     * @return string
     */
    public function packBfInfo($data)
    {
        $return = [];
        $return['game_type'] = '52';//竞足比分
        $return['win_other_sp'] = $data['-1-h'];
        $return['draw_other_sp'] = $data['-1-d'];
        $return['lose_other_sp'] = $data['-1-a'];
        $return['0:0'] = $data['0000'];
        $return['0:1'] = $data['0001'];
        $return['0:2'] = $data['0002'];
        $return['0:3'] = $data['0003'];
        $return['0:4'] = $data['0004'];
        $return['0:5'] = $data['0005'];
        $return['1:0'] = $data['0100'];
        $return['1:1'] = $data['0101'];
        $return['1:2'] = $data['0102'];
        $return['1:3'] = $data['0103'];
        $return['1:4'] = $data['0104'];
        $return['1:5'] = $data['0105'];
        $return['2:0'] = $data['0200'];
        $return['2:1'] = $data['0201'];
        $return['2:2'] = $data['0202'];
        $return['2:3'] = $data['0203'];
        $return['2:4'] = $data['0204'];
        $return['2:5'] = $data['0205'];
        $return['3:0'] = $data['0300'];
        $return['3:1'] = $data['0301'];
        $return['3:2'] = $data['0302'];
        $return['3:3'] = $data['0303'];
        $return['4:0'] = $data['0400'];
        $return['4:1'] = $data['0401'];
        $return['4:2'] = $data['0402'];
        $return['5:0'] = $data['0500'];
        $return['5:1'] = $data['0501'];
        $return['5:2'] = $data['0502'];
        $return['p_id'] = $data['p_id'];
        $return['if_selling'] = ($data['p_status'] == 'Selling') ? '1' : '0';
        $return['if_single'] = $data['single'];
        return json_encode($return);
    }

    /**
     * 竞彩足球总进球玩法赔率等信息生成json
     * @param $data
     * @return string
     */
    public function packZjqInfo($data)
    {
        $return = [];
        $return['game_type'] = '53';//竞足总进球
        $return['s0'] = $data['s0'];
        $return['s1'] = $data['s1'];
        $return['s2'] = $data['s2'];
        $return['s3'] = $data['s3'];
        $return['s4'] = $data['s4'];
        $return['s5'] = $data['s5'];
        $return['s6'] = $data['s6'];
        $return['s7'] = $data['s7'];
        $return['p_id'] = $data['p_id'];
        $return['if_selling'] = ($data['p_status'] == 'Selling') ? '1' : '0';
        $return['if_single'] = $data['single'];
        return json_encode($return);
    }

    /**
     * 竞彩足球半全场胜平负玩法赔率等信息生成json
     * @param $data
     * @return string
     */
    public function packBqspfInfo($data)
    {
        $return = [];
        $return['game_type'] = '54';//半全场胜平负
        $return['ww'] = $data['hh'];
        $return['wd'] = $data['hd'];
        $return['wl'] = $data['ha'];
        $return['dw'] = $data['dh'];
        $return['dd'] = $data['dd'];
        $return['dl'] = $data['da'];
        $return['lw'] = $data['ah'];
        $return['ld'] = $data['ad'];
        $return['ll'] = $data['aa'];
        $return['p_id'] = $data['p_id'];
        $return['if_selling'] = ($data['p_status'] == 'Selling') ? '1' : '0';
        $return['if_single'] = $data['single'];
        return json_encode($return);
    }

    /**
     * 竞彩足球比赛结果单页信息处理
     * @param $string
     * @return array|false|int
     */
    public function onePageResultDeal($string)
    {
        $return = [];

        $arr = [];
        $pattern = "/查询结果.*?(\d*?)<\/span>场赛事符合条件/is";
        $res = preg_match($pattern, $string, $arr);
        if ($res) {
            //成功匹配$res为1
            $return['count'] = $arr[1];
        } else {
            //出错返回$res为false，未匹配到为0
            return $res;
        }

        $arr = [];
        $pattern = "/fb_match_info\.php\?m=(\d*?)\" target=\"_blank\"><span class=\"zhu";
        $pattern .= ".*?<td width=\"60\"><span class=\"blue\">(.*?)<\/span><\/td>
";
        $pattern .= ".*?<td width=\"60\"><span class=\"u-org\" style=\"font-weight:bold; font-size:13px;\">(.*?)<\/span><\/td>";
        $pattern .= ".*?<td width=\"86\">(.*?)<\/td>";
        $pattern .= "/is";
        $res = preg_match_all($pattern, $string, $arr);
        if ($res) {
            //成功匹配$res为1
            $return['match_id'] = $arr[1];
            $return['sbc_score'] = $arr[2];
            $return['final_score'] = $arr[3];
            $return['is_finish'] = $arr[4];
        } else {
            //出错返回$res为false，未匹配到为0
            return $res;
        }
        return $return;
    }

    /**
     * 竞彩足球比赛结果单页信息入库
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function onePageResultSave($data)
    {
        if (count($data['match_id']) != count($data['sbc_score']) or count($data['match_id']) != count($data['final_score']) or count($data['match_id']) != count($data['is_finish'])) {
            return ['valid' => 0, 'msg' => '操作失败'];
        }

        $arr = [];
        $len = count($data['match_id']);
        for ($i = 0; $i < $len; $i++) {
            $arr[$i]['match_id'] = $data['match_id'][$i];
            $arr[$i]['sbc_score'] = $data['sbc_score'][$i];
            $arr[$i]['final_score'] = $data['final_score'][$i];
            $arr[$i]['is_finish'] = $data['is_finish'][$i];
            if (!empty($arr[$i]['final_score'])) {
                $arr[$i]['spf_result'] = $this->calculateSpfResult($arr[$i]['final_score']);
                $arr[$i]['rqspf_result'] = $this->calculateRqspfResult($arr[$i]['match_id'], $arr[$i]['final_score']);
                $arr[$i]['zjq_result'] = $this->calculateZjqResult($arr[$i]['final_score']);
                $arr[$i]['bqspf_result'] = $this->calculateBqspfResult($arr[$i]['sbc_score'], $arr[$i]['final_score']);
            }

            $replace = $this->where('match_id', $arr[$i]['match_id'])
                ->find();

            try {
                if ($replace) {
                    $this->allowField(true)->update($arr[$i], ['match_id' => $arr[$i]['match_id']]);
                } else {
                    $this->allowField(true)->isUpdate(false)->data($arr[$i])->save();
                }
            } catch (\Exception $errorException) {
                return ['valid' => 0, 'msg' => $errorException->getMessage()];
            }
        }
        return ['valid' => 1, 'msg' => '操作成功'];
    }

    /**
     * 计算竞彩足球胜平负彩果
     * @param $score
     * @return string
     */
    public function calculateSpfResult($score)
    {
        $score = explode(':', $score);
        if ($score[0] > $score[1]) {
            return '胜';
        } elseif ($score[0] == $score[1]) {
            return '平';
        } else {
            return '负';
        }
    }

    /**
     * 计算竞彩足球让球胜平负彩果
     * @param $match_id
     * @param $final_score
     * @return string
     */
    public function calculateRqspfResult($match_id, $final_score)
    {
        $rqspf_rq = $this->where('match_id', $match_id)->value('rqspf_rq');
        $score = explode(':', $final_score);
        $host = intval($score[0]) + intval($rqspf_rq);
        $guest = intval($score[1]);
        if ($host > $guest) {
            return '胜';
        } elseif ($host == $guest) {
            return '平';
        } else {
            return '负';
        }
    }

    /**
     * 计算竞彩足球总进球彩果
     * @param $final_score
     * @return int
     */
    public function calculateZjqResult($final_score)
    {
        $score = explode(':', $final_score);
        $final = intval($score[0]) + intval($score[1]);
        return $final;
    }

    /**
     * 计算竞彩足球半全场胜平负彩果
     * @param $sbc_score
     * @param $final_score
     * @return string
     */
    public function calculateBqspfResult($sbc_score, $final_score)
    {
        $sbc_result = $this->calculateSpfResult($sbc_score);
        $final_result = $this->calculateSpfResult($final_score);
        return $sbc_result . $final_result;
    }
}