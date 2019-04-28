<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/9/12
 * Time: 14:26
 * Email: varphper@gmail.com
 */

namespace app\common\lib\exception;


class ApiAuthException extends BaseException
{
    private $error_code;
    private $msg;
    private $http_code;

    public function __construct($message = "没有权限", $code = 10002, $http_code = 403, Throwable $previous = null)
    {
        $this->http_code = $http_code;
        $this->error_code = $code;
        $this->msg = $message;
        parent::__construct($message, $code, $previous);
    }
}