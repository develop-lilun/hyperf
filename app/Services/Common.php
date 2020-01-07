<?php


namespace App\Services;


class Common
{
    /**
     * 从目标数组中选取一些字段
     *
     * @param array $array
     * @param array $keyArray
     *
     * @return array
     */
    public static function arrayOnly($array = [], $keyArray = [])
    {
        $result = [];
        foreach ($keyArray as $key => $value) {
            if (isset($array[$value])) {
                $result[$value] = $array[$value];
            }
        }
        return $result;
    }

    /**
     * 前台显示时间统一处理
     *
     * @param string $date
     *
     * @return false|string
     */
    public static function timeChange($date = '')
    {
        $result = '';
        // 当前时间
        $time = strtotime($date);
        // 今天0点时间戳
        $todayTime = strtotime(date("Y-m-d"));
        // 昨天0点时间戳
        $yesterdayTime = strtotime('yesterday');

        if ($time >= $todayTime) {
            $result = date('H:i', $time);
        } elseif ($time < $todayTime && $time >= $yesterdayTime) {
            $result = '昨天';
        } elseif ($time < $yesterdayTime) {
            $result = date('Y-m-d', $time);
        }
        return $result;
    }
}