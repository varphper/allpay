<?php
/**
 * Description: 一些数学计算方法
 * User: varphper
 * Date: 2018/7/30
 * Time: 9:53
 * Email: varphper@gmail.com
 */

namespace app\common\lib;


class Math
{
    /**
     * 计算从$max向下连乘到$min 的值
     * 如果$min为1，即为 阶乘
     * @param $max
     * @param $min
     * @return float|int
     */
    public function factorial($max, $min = 1)
    {
        if ($max >= $min && $max > 1) {
            return $max * $this->factorial($max - 1, $min);
        } else {
            return 1;
        }
    }

    /**
     * 求组合数
     * @param $m
     * @param $n
     * @return float|int
     */
    public function combine($m, $n)
    {
        if ($m < $n || $n < 0) {
            return 0;
        }
        return $this->factorial($m, $m - $n + 1) / $this->factorial($n, 1);
    }


    /**
     * 求数组的笛卡儿积(全组合)
     * 输入：[[1,2,3],['a','b']]
     * 输出：[['1a'],['1b'],['2a'],['2b'],['3a'],['3b']]
     * @param $arr
     * @return array|mixed
     */
    public function cartesian($arr)
    {
        $result = array_shift($arr);
        while ($arr2 = array_shift($arr)) {
            $arr1 = $result;
            $result = [];
            foreach ($arr1 as $v1) {
                foreach ($arr2 as $v2) {
                    $result[] = $v1 . $v2;
                }
            }
        }
        return $result;
    }

    /**
     * 求竞彩M串N的组合情况(从数组$a中选$m个元素)
     * 输入：[[1,2],3，4,5] 3
     * 输出：[[1,3,4],[2,3,4],[1,3,5],[2,3,5],[1,4,5],[2,4,5],[3,4,5]]
     * @param $a array
     * @param $m integer
     * @return array
     */
    public function jcCombination($a, $m)
    {
        $r = [];
        $l = count($a);
        if ($m <= 0 || $m > $l) {
            return $r;
        }

        for ($i = 0; $i < $l; $i++) {
            $t = [$a[$i]];
            if ($m == 1) {
                $r[] = $t;
            } else {
                if ($i + 1 > $l) return $r;
                $b = array_slice($a, $i + 1);
                $c = $this->jcCombination($b, $m - 1);
                foreach ($c as $v) {
                    if (is_array($t[0])) {
                        foreach ($t[0] as $tt) {
                            if (count($v, 1) > 2) {
                                foreach ($v[0] as $vv) {
                                    $r[] = array_merge([$tt], [$vv]);
                                }
                            } else {
                                $r[] = array_merge([$tt], $v);
                            }
                        }
                    } else {
                        if (count($v, 1) > 2) {
                            foreach ($v[0] as $vv) {
                                $r[] = array_merge($t, [$vv]);
                            }
                        } else {
                            $r[] = array_merge($t, $v);
                        }
                    }
                }
            }
        }
        return $r;
    }

    /**
     * 列出从数组$a中选取$m个元素组合(递归法)
     * 输入：[1,2,3，4] 3
     * 输出：[[1,2,3],[1,2,4],[1,3,4],[2,3,4]]
     * @param $a
     * @param $m
     * @return array|bool
     */
    public function combination($a, $m)
    {
        $r = [];

        $n = count($a);
        if ($m <= 0 || $m > $n) {
            return $r;
        }

        for ($i = 0; $i < $n; $i++) {
            $t = [$a[$i]];
            if ($m == 1) {
                $r[] = $t;
            } else {
                $b = array_slice($a, $i + 1);
                $c = $this->combination($b, $m - 1);
                foreach ($c as $v) {

                    $r[] = array_merge($t, $v);
                }
            }
        }
        return $r;
    }

    /**
     * 使用“01位移法”列出从数组$array中选取$select个元素组合
     * 输入：[1,2,3，4] 3
     * 输出：[[1,2,3],[1,2,4],[1,3,4],[2,3,4]]
     * @param $array
     * @param $select
     * @return array|bool
     */
    public function shiftCombination($array, $select)
    {
        $count = count($array);
        if ($select < 2 || $count < $select) return false;
        $combination = [];//保存各种组合序列
        $temp = [];//保存原数组中取出的值的组合
        //初始化，将数组前n个元素置1其余置0，表示第一个组合为前n个数
        for ($i = 0; $i < $select; $i++) {
            $combination[$i] = 1;
            $temp[] = $array[$i];
        }
        for ($i = $select; $i < $count; $i++) {
            $combination[$i] = 0;
        }
        $combinations[] = $temp;//保存第一个组合
        //如果i移动到最大值，都未找到'10',则已经生成了所有序列
        $end = $count - 1;
        for ($i = 0, $one = 0; $i < $end; $i++) {
            if ($combination[$i]) {
                $one++;//统计i直到当前位置，'1'的个数
            }
            //找到第一对'10'，并交换
            if ($combination[$i] == 1 && $combination[$i + 1] == 0) {
                $combination[$i] = 0;
                $combination[$i + 1] = 1;
                $temp[$one - 1] = $array[$i + 1];//修改值组合

                //将第一对'10'左边的所有'1'全部移动到数组的最左端
                //只有第一位变为0且第一对'10'左边有'1'才需要移动，否则其左边的1本来就在最左端，无需移动
                if ($combination[0] == '0' && $one > 1) {
                    for ($k = 0; $k < $one - 1; $k++) {
                        $combination[$k] = 1;
                        $temp[$k] = $array[$k];//修改值组合
                    }
                    for ($k = $one - 1; $k < $i; $k++) {
                        $combination[$k] = 0;
                    }
                }
                $combinations[] = $temp;
                //查找下一个序列
                $i = -1;
                $one = 0;
            }
        }
        //返回原数组数值组合
        return $combinations;
    }

    /**
     * 使用“01位移法”列出从数组$array中选取$select个元素组合
     * 输入：[1,2,3，4] 3
     * 输出：[[1,2,3],[1,2,4],[1,3,4],[2,3,4]]
     * @param $array
     * @param $select
     * @return array|bool
     */
    public function shiftCombination2($array, $select)
    {
        $count = count($array);
        if ($select < 2 || $count < $select) return false;
        $combination = [];//保存各种组合序列
        $temp = [];//保存原数组中取出的值的组合
        $index = [];
        //初始化，将数组前n个元素置1其余置0，表示第一个组合为前n个数
        for ($i = 0; $i < $select; $i++) {
            $combination[$i] = 1;
            $index[] = $i;
            $temp[] = $array[$i];
        }
        for ($i = $select; $i < $count; $i++) {
            $combination[$i] = 0;
        }
        $combinations[] = $temp;//保存第一个组合
        $reverse = $index;
        //初始态时原数组中某个'1'是第几个'1',
        //与$index互相映射$reverse[$j] = $i,则$index[$i] = $j,
        //$j表示原数组的$j下标,$i表示第$i个1;

        //如果i移动到最大值，都未找到'10',则已经生成了所有序列
        $end = $count - 1;
        for ($i = 0, $one = 0; $i < $end; $i++) {
            if ($combination[$i]) {
                $one++;//统计i直到当前位置，'1'的个数
            }
            //找到第一对'10'，并交换
            if ($combination[$i] == 1 && $combination[$i + 1] == 0) {
                $combination[$i] = 0;
                $combination[$i + 1] = 1;
                $index[$reverse[$i]] = $i + 1; //更新第$i个1的位置信息
                $reverse[$i + 1] = $reverse[$i]; //更新原数组第$i+1的位置上的对应的第$i个1的位置
                unset($reverse[$i]);

                //将第一对'10'左边的所有'1'全部移动到数组的最左端
                //只有第一位变为0且第一对'10'左边有'1'才需要移动，否则其左边的1本来就在最左端，无需移动
                if ($combination[0] == '0' && $one > 1) {
                    for ($k = 0; $k < $one - 1; $k++) {
                        $combination[$k] = 1;
                        $reverse[$k] = $k;
                        $index[$k] = $k;
                    }
                    for ($k = $one - 1; $k < $i; $k++) {
                        $combination[$k] = 0;
                        if (isset($reverse[$k])) unset($reverse[$k]);
                    }
                }
                for ($k = 0; $k < $select; $k++) {
                    $temp[$k] = $array[$index[$k]];
                }
                $combinations[] = $temp;
                //查找下一个序列
                $i = -1;
                $one = 0;
            }
        }
        //返回原数组数值组合
        return $combinations;
    }

    /**
     * 使用“分治位移法”列出从$length个元素中取出$selectMany个元素组合
     * 输入：4 3
     * 输出：[[0,1,2],[0,1,3],[0,2,3],[1,2,3]]  元素由原数组下标组成
     * @param $length
     * @param $selectMany
     * @return array
     */
    public function bucketCombinations($length, $selectMany)
    {
        $bucket = [];  //桶的集合
        //$combinations = [];//组合数结果集
        $maxBucketID = $selectMany - 1;//最后一个桶的编号
        $offset = $length - $selectMany; //每个桶的最大容量-1

        for ($i = 0; $i < $selectMany; ++$i) { //初始化每个桶,[0,1,2,3,4,5]中选4个，初始化为[0,1,2,3]
            $bucket[] = $i;
        }

        $combinations = [$bucket]; //保存第一组组合数
        $bucketID = $maxBucketID; //设置开始时的桶编号，即从哪里开始求解组合数

        while (true) {
            //从当前位置向前移动到第一个没满的桶
            while ($bucket[$bucketID] == $bucketID + $offset) {
                --$bucketID;
                if ($bucketID < 0) return $combinations;//表示所有桶已经满了，便返回组合数结果集
            }
            //该桶+1
            ++$bucket[$bucketID];
            //设置当前桶位置之后的所有桶的值为当前桶的值
            for ($j = $bucketID + 1, $bid = $bucket[$bucketID] - $bucketID; $j <= $maxBucketID; ++$j) {
                $bucket[$j] = $j + $bid; //bid表示偏移量
            }
            //指针跳转到到末端
            $bucketID = $maxBucketID;
            for ($k = $bucket[$bucketID]; $k <= $bucketID + $offset; ++$k) {
                $bucket[$bucketID] = $k; //$k表示每一种组合数的最后一个
                $combinations[] = $bucket;  //保存组合数
            }
        }
    }
}