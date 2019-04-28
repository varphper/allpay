<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/10/17
 * Time: 12:35
 * Email: varphper@gmail.com
 */

namespace app\index\controller;


use think\Controller;
use think\facade\Log;

class Index extends Controller
{
    public function index()
    {
        $data = $this->request->post();
//        $aa = json_encode($data);
//        Log::record($aa);
        echo 333;
    }

}