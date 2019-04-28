<?php
/**
 * Description: 还原打印机截取的数据内容.
 * User: varphper
 * Date: 2018/8/19
 * Time: 17:08
 * Email: varphper@gmail.com
 */

namespace app\common\lib;


class Printer
{
    /**
     * 提取打印机打印数据
     * @param $string
     * @return string
     */
    public function decodePrintFile($string)
    {
        //打印机控制指令移除
        //先整体移除结尾
        $foot = strpos($string, '1B64011B1D78533');
        if ($foot !== false){
            $string = substr($string, 0, $foot);
        }
        //移除其它不影响内容的指令
        $print_order = [
            '1D4C0000', '1D573002','1D574002', '1D50CBCB',
            '1B2100', '1B2101',
            '1B331A', '1B3322', '1B331E',
            '1B4501', '1B4500',
            '1B6101', '1B6100',
            '1D2100', '1D2110',
            '1B1B1B1B1B'
        ];
        $string = str_replace($print_order, '', $string);
        return $this->hexToStr($string);
    }

    /**
     * 截取打印机的十六进制数据（汉字为区位码+0xA0后的编码）转字符串
     * 同时对打印机换行指令做替换
     * @param $hex
     * @return string
     */
    public function hexToStr($hex)
    {
        $result = "";
        $len = strlen($hex);
        for ($i = 0; $i < $len - 1; $i += 2) {
            $temp = $hex[$i] . $hex[$i + 1];
            if ($temp == '0A') {
                //打印机换行指令"LF"替换成系统换行符
                $result .= PHP_EOL;
            }elseif ($temp == "1B"){
                //打印机"1B6401"指令替换成系统换行符
                //"1B6401"为"ESC d n"指令：打印缓冲区数据走纸走n行，打印位置移到下行起始位置
                $temp2 = $hex[$i + 2] . $hex[$i + 3].$hex[$i + 4] . $hex[$i + 5];
                if ($temp2 == "6401"){
                    $result .= PHP_EOL;
                    $i+=4;
                }
            }else{
                $result .= chr(hexdec($hex[$i] . $hex[$i + 1]));
            }
        }
        return $result;
    }

    /**
     * 字符串转十六进制数据
     * @param $string
     * @return string
     */
    public function strToHex($string)
    {
        $hex = "";
        for ($i = 0; $i < strlen($string); $i++) {
            $hex .= dechex(ord($string[$i]));
        }
        $hex = strtoupper($hex);
        return $hex;

    }
}