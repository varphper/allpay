<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/9/3
 * Time: 20:23
 * Email: varphper@gmail.com
 */

namespace app\agency\controller;


use app\common\controller\ApiBase;
use app\common\facade\IAuth;
use app\common\lib\exception\ApiAuthException;
use think\Db;
use think\Exception;
use think\facade\Cache;
use think\facade\Config;

class Base extends ApiBase
{
    public function initialize()
    {
        //$this->checkAuth();
        parent::initialize();
    }

    public function checkAuth()
    {
        $sign = $this->request->header('sign');
        $agency_id = $this->request->header('agency_id');

        if (!$sign || !$agency_id) {
            throw new ApiAuthException();
        }

        $agency = $this->getAgency($agency_id);
        if (!$agency) {
            throw new ApiAuthException();
        }

        $check = IAuth::checkOpenSign($sign, $agency['encrypt_key'], $agency['agency_key']);
        if (!$check) {
            throw new ApiAuthException();
        }
        Cache::set($agency_id, $agency, Config::get('custom.agency_info_cache_time'));
        return true;
    }

    /**
     * 根据代理机构的机构id查询代理相关信息
     * @param $agency_id
     * @return array|mixed|null|\PDOStatement|string|\think\Model
     */
    protected function getAgency($agency_id)
    {
        if (Cache::has($agency_id)) {
            return Cache::get($agency_id);
        }
        try {
            $res = Db::table('tc_agency')->where('agency_id', $agency_id)->find();
        } catch (Exception $exception) {
            return null;
        }
        return $res;
    }

}