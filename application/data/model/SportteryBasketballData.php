<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/20
 * Time: 17:05
 * Email: varphper@gmail.com
 */

namespace app\data\model;


use think\Db;
use think\Model;

class SportteryBasketballData extends Model
{
    protected $autoWriteTimestamp = true;

    /**
     * 竞彩篮球混合过关信息（简）整理
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
     * 竞彩篮球混合过关（简）信息入库
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
            $arr[$i]['match_id'] = $data[$i][0][5];
            $arr[$i]['game_num'] = mb_substr($data[$i][0][0], 2);
            $arr[$i]['week_day'] = mb_substr($data[$i][0][0], 0, 2);
            $arr[$i]['sell_date'] = $data[$i][0][12];
            $arr[$i]['our_match_id'] = $arr[$i]['sell_date'] . '#' . $arr[$i]['game_num'];
            $arr[$i]['league'] = $data[$i][0][7];
            $arr[$i]['league_short'] = $data[$i][0][1];
            $arr[$i]['host_team'] = $data[$i][0][8];
            $arr[$i]['host_team_short'] = $data[$i][0][2];
            $arr[$i]['host_team_id'] = $data[$i][0][13];
            $arr[$i]['guest_team'] = $data[$i][0][9];
            $arr[$i]['guest_team_short'] = $data[$i][0][3];
            $arr[$i]['guest_team_id'] = $data[$i][0][14];
            $arr[$i]['play_time'] = strtotime($data[$i][0][4]);
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
     * 竞彩篮球各种玩法信息（全）处理
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
            $return[$i]['guest_team'] = $value['a_cn'];
            $return[$i]['guest_team_short'] = $value['a_cn_abbr'];
            $return[$i]['guest_team_id'] = $value['a_id'];
            $return[$i]['play_time'] = strtotime($value['date'] . $value['time']);
            $return[$i]['rfsf_rf'] = isset($value['hdc']['fixedodds']) ? $value['hdc']['fixedodds'] : '';
            $return[$i]['dxf_zf'] = isset($value['hilo']['fixedodds']) ? $value['hilo']['fixedodds'] : '';
            $return[$i]['official_last_updated_time'] = strtotime($data['status']['last_updated']);
            $return[$i]['sf_sp_gg'] = isset($value['mnl']) ? $this->packSfInfo($value['mnl']) : '';
            $return[$i]['rfsf_sp_gg'] = isset($value['hdc']) ? $this->packSfInfo($value['hdc']) : '';
            $return[$i]['sfc_sp_gg'] = isset($value['wnm']) ? $this->packSfcInfo($value['wnm']) : '';
            $return[$i]['dxf_sp_gg'] = isset($value['hilo']) ? $this->packDxfInfo($value['hilo']) : '';
        }
        return $return;
    }

    /**
     * 竞彩篮球胜负(含让分胜负)玩法赔率等信息生成json
     * @param $data
     * @return string
     */
    public function packSfInfo($data)
    {
        $return = [];
        if (isset($data['fixedodds']) && $data['fixedodds'] != '') {
            $return['game_type'] = '61';//让分胜负
        } else {
            $return['game_type'] = '62';//胜负
        }
        $return['win_sp'] = $data['h'];
        $return['lose_sp'] = $data['a'];
        $return['p_id'] = $data['p_id'];
        $return['if_selling'] = ($data['p_status'] == 'Selling') ? '1' : '0';
        $return['if_single'] = $data['single'];
        $return['win_sp_trend'] = $data['h_trend'];
        $return['lose_sp_trend'] = $data['a_trend'];
        $return['rfsf_rf'] = isset($data['fixedodds']) ? $data['fixedodds'] : '';
        return json_encode($return);
    }

    /**
     * 竞彩篮球胜分差玩法赔率等信息生成json
     * @param $data
     * @return string
     */
    public function packSfcInfo($data)
    {
        $return = [];
        $return['game_type'] = '63';//胜分差
        $return['w1'] = $data['w1'];
        $return['w2'] = $data['w2'];
        $return['w3'] = $data['w3'];
        $return['w4'] = $data['w4'];
        $return['w5'] = $data['w5'];
        $return['w6'] = $data['w6'];
        $return['l1'] = $data['l1'];
        $return['l2'] = $data['l2'];
        $return['l3'] = $data['l3'];
        $return['l4'] = $data['l4'];
        $return['l5'] = $data['l5'];
        $return['l6'] = $data['l6'];
        $return['p_id'] = $data['p_id'];
        $return['if_selling'] = ($data['p_status'] == 'Selling') ? '1' : '0';
        $return['if_single'] = $data['single'];
        $return['win_sp_trend'] = $data['h_trend'];
        $return['lose_sp_trend'] = $data['a_trend'];
        return json_encode($return);
    }

    /**
     * 竞彩篮球大小分玩法赔率等信息生成json
     * @param $data
     * @return string
     */
    public function packDxfInfo($data)
    {
        $return = [];
        $return['game_type'] = '64';//大小分
        $return['big_sp'] = $data['h'];
        $return['small_sp'] = $data['l'];
        $return['p_id'] = $data['p_id'];
        $return['if_selling'] = ($data['p_status'] == 'Selling') ? '1' : '0';
        $return['if_single'] = $data['single'];
        $return['win_sp_trend'] = $data['h_trend'];
        $return['lose_sp_trend'] = $data['a_trend'];
        $return['dxf_zf'] = isset($data['fixedodds']) ? $data['fixedodds'] : '';
        return json_encode($return);
    }

    /**
     * 竞彩篮球各种玩法信息（全）入库
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
                'sf_sp_gg' => $data[$i]['sf_sp_gg'],
                'rfsf_sp_gg' => $data[$i]['rfsf_sp_gg'],
                'sfc_sp_gg' => $data[$i]['sfc_sp_gg'],
                'dxf_sp_gg' => $data[$i]['dxf_sp_gg'],
                'official_last_updated_time' => $data[$i]['official_last_updated_time'],
                'create_time' => time()
            ];

            if ($replace) {
                if ($replace['official_last_updated_time'] != $data[$i]['official_last_updated_time']) {
                    Db::startTrans();
                    try {
                        //更新tc_sporttery_basketball_data和tc_sporttery_basketball_sp_log表
                        Db::table('tc_sporttery_basketball_data')->where('our_match_id', $data[$i]['our_match_id'])->update($data[$i]);
                        Db::table('tc_sporttery_basketball_sp_log')->insert($log_data);
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
                    Db::table('tc_sporttery_basketball_data')->insert($data[$i]);
                    Db::table('tc_sporttery_basketball_sp_log')->insert($log_data);
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
     * 竞彩篮球比赛结果单页信息处理
     * @param $string
     * @return array|false|int
     */
    public function onePageResultDeal($string)
    {
        $return = [];

        $arr = [];
        $pattern = "/查询结果：有.*?(\d*?)<\/span>场赛事符合条件/is";
        $res = preg_match($pattern, $string, $arr);
        if ($res) {
            //成功匹配$res为1
            $return['count'] = $arr[1];
        } else {
            //出错返回$res为false，未匹配到为0
            return $res;
        }

        $arr = [];
        $pattern = "/bk_match_info\.php\?m=(\d*?)\" target=\"_blank\"><span class=\"zhu1";
        $pattern .= ".*?<td width=\"100\"><span class=\"u-w45\">(.*?)<\/span>";
        $pattern .= "<span class=\"u-w45\">(.*?)<\/span><\/td>";
        $pattern .= ".*?<td width=\"100\"><span class=\"u-w45\">(.*?)<\/span>";
        $pattern .= "<span class=\"u-w45\">(.*?)<\/span><\/td>";
        $pattern .= ".*?<td width=\"79\">(.*?)<\/td>";
        $pattern .= ".*?<td width=\"80\"><span class=\"u-org\"  style=\"font-weight:bold; font-size:13px;\">(.*?)<\/span><\/td>";
        $pattern .= ".*?<td width=\"66\">(.*?)<\/td>.*?<td width=\"70";
        $pattern .= "/is";
        $res = preg_match_all($pattern, $string, $arr);
        if ($res) {
            //成功匹配$res为1
            $return['match_id'] = $arr[1];
            $return['first_quarter_score'] = $arr[2];
            $return['second_quarter_score'] = $arr[3];
            $return['third_quarter_score'] = $arr[4];
            $return['fourth_quarter_score'] = $arr[5];
            $return['overtime_score'] = $arr[6];
            $return['final_score'] = $arr[7];
            $return['is_finish'] = $arr[8];
        } else {
            //出错返回$res为false，未匹配到为0
            return $res;
        }
        return $return;
    }

    /**
     * 竞彩篮球比赛结果单页信息入库
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function onePageResultSave($data)
    {
        if (count($data['match_id']) != count($data['first_quarter_score']) or count($data['match_id']) != count($data['second_quarter_score']) or count($data['match_id']) != count($data['third_quarter_score'])) {
            return ['valid' => 0, 'msg' => '操作失败'];
        }

        $arr = [];
        $len = count($data['match_id']);
        for ($i = 0; $i < $len; $i++) {
            $arr[$i]['match_id'] = $data['match_id'][$i];
            $arr[$i]['first_quarter_score'] = $data['first_quarter_score'][$i];
            $arr[$i]['second_quarter_score'] = $data['second_quarter_score'][$i];
            $arr[$i]['third_quarter_score'] = $data['third_quarter_score'][$i];
            $arr[$i]['fourth_quarter_score'] = $data['fourth_quarter_score'][$i];
            $arr[$i]['overtime_score'] = $data['overtime_score'][$i];
            $arr[$i]['final_score'] = $data['final_score'][$i];
            $arr[$i]['is_finish'] = $data['is_finish'][$i];
            if (!empty($arr[$i]['final_score'])) {
                if ($arr[$i]['final_score'] == '取消') {
                    $arr[$i]['sf_result'] = '';
                    $arr[$i]['rfsf_result'] = '';
                    $arr[$i]['dxf_result'] = '';
                    $arr[$i]['sfc_result'] = '';
                } else {
                    $arr[$i]['sf_result'] = $this->calculateSfResult($arr[$i]['final_score']);
                    $arr[$i]['rfsf_result'] = $this->calculateRfsfResult($arr[$i]['match_id'], $arr[$i]['final_score']);
                    $arr[$i]['dxf_result'] = $this->calculateDxfResult($arr[$i]['match_id'], $arr[$i]['final_score']);
                    $arr[$i]['sfc_result'] = $this->calculateSfcResult($arr[$i]['final_score']);
                }
            }

            $replace = $this->where('match_id', $arr[$i]['match_id'])->find();

            try {
                if ($replace) {
                    $this->allowField(true)->update($arr[$i],['match_id'=> $arr[$i]['match_id']]);
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
     * 计算竞彩篮球胜负彩果
     * @param $score
     * @return string
     */
    public function calculateSfResult($score)
    {
        $score = explode(':', $score);
        if ($score[1] > $score[0]) {
            return '主胜';
        } elseif ($score[1] == $score[0]) {
            //其实竞彩篮球里没有平的结果
            return '平';
        } else {
            return '主负';
        }
    }

    /**
     * 计算竞彩篮球让分胜负彩果
     * @param $match_id
     * @param $final_score
     * @return string
     */
    public function calculateRfsfResult($match_id, $final_score)
    {
        $rfsf_rf = $this->where('match_id', $match_id)->value('rfsf_rf');
        $score = explode(':', $final_score);
        $guest = intval($score[0]) + intval($rfsf_rf);
        $host = intval($score[1]);
        if ($host > $guest) {
            return '让分主胜';
        } elseif ($host == $guest) {
            //其实竞彩篮球里没有让分平的结果
            return '让分平';
        } else {
            return '让分主负';
        }
    }

    /**
     * 计算竞彩篮球大小分彩果
     * @param $match_id
     * @param $final_score
     * @return string
     */
    public function calculateDxfResult($match_id, $final_score)
    {
        $dxf_zf = $this->where('match_id', $match_id)->value('dxf_zf');
        $score = explode(':', $final_score);
        $count_score = intval($score[0]) + intval($score[1]);
        if ($count_score > $dxf_zf) {
            return '大';
        } elseif ($count_score == $dxf_zf) {
            //竞彩篮球里没有平的结果
            return '平';
        } else {
            return '小';
        }
    }

    /**
     * 计算竞彩篮球胜分差彩果
     * @param $final_score
     * @return string
     */
    public function calculateSfcResult($final_score)
    {
        $score = explode(':', $final_score);
        $guest = intval($score[0]);
        $host = intval($score[1]);
        $difference = abs($guest - $host);
        if ($host > $guest) {
            if ($difference >= 1 && $difference <= 5) {
                return '主胜1-5';
            } elseif ($difference >= 6 && $difference <= 10) {
                return '主胜6-10';
            } elseif ($difference >= 11 && $difference <= 15) {
                return '主胜11-15';
            } elseif ($difference >= 16 && $difference <= 20) {
                return '主胜16-20';
            } elseif ($difference >= 21 && $difference <= 25) {
                return '主胜21-25';
            } elseif ($difference >= 26) {
                return '主胜26+';
            }
        } else {
            if ($difference >= 1 && $difference <= 5) {
                return '客胜1-5';
            } elseif ($difference >= 6 && $difference <= 10) {
                return '客胜6-10';
            } elseif ($difference >= 11 && $difference <= 15) {
                return '客胜11-15';
            } elseif ($difference >= 16 && $difference <= 20) {
                return '客胜16-20';
            } elseif ($difference >= 21 && $difference <= 25) {
                return '客胜21-25';
            } elseif ($difference >= 26) {
                return '客胜26+';
            }
        }
    }
}