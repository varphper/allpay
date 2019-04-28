<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/9/12
 * Time: 14:07
 * Email: varphper@gmail.com
 */

namespace app\agency\controller;


use app\common\lib\Aes;
use app\common\lib\exception\ApiAuthException;

class Ticket extends Base
{
    public function deliverTicket()
    {
        if (!$this->request->isPost()) {
            throw new ApiAuthException();
        }

        $data = $this->request->param('data');
        if (!$data) {
            throw new ApiAuthException();
        }

        try {
            $json = (new Aes())->decrypt($data);
            $dataArr = json_decode($json, true);
        } catch(\Exception $exception) {
            throw new ApiAuthException();
        }



    }

    public function fetchResult()
    {

    }
}