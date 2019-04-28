<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/9/4
 * Time: 12:33
 * Email: varphper@gmail.com
 */

namespace app\common\facade;


use think\Facade;

class Utils extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\common\lib\Utils';
    }
}