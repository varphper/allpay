<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/9/5
 * Time: 22:14
 * Email: varphper@gmail.com
 */

namespace app\common\lib\exception;


use think\Exception;
use Throwable;

class BaseException extends Exception
{
    private $error_code;
    private $msg;
    private $http_code;

    public function __construct($message = "", $code = 0, $http_code = 500, Throwable $previous = null)
    {
        $this->http_code = $http_code;
        $this->error_code = $code;
        $this->msg = $message;
        parent::__construct($message, $code, $previous);
    }

    public function getErrorCode()
    {
        return $this->error_code;
    }

    public function getMsg()
    {
        return $this->msg;
    }

    public function getHttpCode()
    {
        return $this->http_code;
    }


}