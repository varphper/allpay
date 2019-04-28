<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/7/26
 * Time: 18:26
 * Email: varphper@gmail.com
 */

namespace app\award\service;


class Base
{
    /**
     * 奖金数据格式处理
     * @param $awards_money
     * @return int
     */
    public function dealMoney($awards_money)
    {

        return intval(str_replace([',', ' ', '元', '-'], '', $awards_money));
    }
}