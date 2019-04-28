<?php
/**
 * Description: Created by PhpStorm.
 * User: varphper
 * Date: 2018/9/4
 * Time: 12:22
 * Email: varphper@gmail.com
 */

namespace app\common\lib;


class Utils
{
    /**
     * 根据键名删除数组中相应元素
     * @param $data
     * @param $key
     * @return mixed
     */
    public function array_remove($data, $key){
        if(!array_key_exists($key, $data)){
            return $data;
        }
        $keys = array_keys($data);
        $index = array_search($key, $keys);
        if($index !== FALSE){
            array_splice($data, $index, 1);
        }
        return $data;
    }

    /**
     * 将query string参数提取出来生成关联数组
     * @param string $queryStr query string,形式："aa=v1&bb=v2"
     * @return array 参数关联数组，形式: ["aa"=>"v1","bb"=>"v2"]
     */
    public function queryStringToArray($queryStr)
    {
        $params = [];
        $queryParts = explode("&", $queryStr);
        foreach ($queryParts as $queryPart) {
            $item = explode("=", $queryPart);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }
}