<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/9/5
 * Time: 21:47
 * Email: varphper@gmail.com
 */

namespace app\common\lib\exception;


use Exception;
use think\facade\Config;
use think\exception\Handle;

class ExceptionHandle extends Handle
{
    private $code;
    private $msg;
    private $http_code;

    public function render(Exception $e)
    {
        if ($e instanceof BaseException) {
            $this->msg = $e->getMsg();
            $this->code = $e->getErrorCode();
            $this->http_code = $e->getHttpCode();
        } else {
            if (Config::get('app.app_debug')) {
                return parent::render($e);
            }
            $this->msg = '服务器内部错误';
            $this->http_code = 500;
            $this->code = 999;
        }

        $return = [
            'code'=>$this->code,
            'msg'=>$this->msg,
        ];
        return json($return,$this->http_code);
    }
}