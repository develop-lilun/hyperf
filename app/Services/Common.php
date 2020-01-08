<?php


namespace App\Services;


use Hyperf\Di\Exception\Exception;

class Common
{
    /**
     * 获取首字母
     *
     * @param $str
     *
     * @return bool|string
     */
    public static function getInitial($str)
    {
        $return = 'A';
        try {
            if (empty($str)) {
                return $return;
            }
            if (is_numeric($str{0})) {
                return $return;
            }// 如果是数字开头 则返回数字
            $fchar = ord($str{0});
            if ($fchar >= ord('A') && $fchar <= ord('z')) {
                return strtoupper($str{0});
            } //如果是字母则返回字母的大写
            $s1 = iconv('UTF-8', 'gb2312', $str);
            $s2 = iconv('gb2312', 'UTF-8', $s1);
            $s = $s2 == $str ? $s1 : $str;
            $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
            if ($asc >= -20319 && $asc <= -20284) {
                return 'A';
            }//这些都是汉字
            if ($asc >= -20283 && $asc <= -19776) {
                return 'B';
            }
            if ($asc >= -19775 && $asc <= -19219) {
                return 'C';
            }
            if ($asc >= -19218 && $asc <= -18711) {
                return 'D';
            }
            if ($asc >= -18710 && $asc <= -18527) {
                return 'E';
            }
            if ($asc >= -18526 && $asc <= -18240) {
                return 'F';
            }
            if ($asc >= -18239 && $asc <= -17923) {
                return 'G';
            }
            if ($asc >= -17922 && $asc <= -17418) {
                return 'H';
            }
            if ($asc >= -17417 && $asc <= -16475) {
                return 'J';
            }
            if ($asc >= -16474 && $asc <= -16213) {
                return 'K';
            }
            if ($asc >= -16212 && $asc <= -15641) {
                return 'L';
            }
            if ($asc >= -15640 && $asc <= -15166) {
                return 'M';
            }
            if ($asc >= -15165 && $asc <= -14923) {
                return 'N';
            }
            if ($asc >= -14922 && $asc <= -14915) {
                return 'O';
            }
            if ($asc >= -14914 && $asc <= -14631) {
                return 'P';
            }
            if ($asc >= -14630 && $asc <= -14150) {
                return 'Q';
            }
            if ($asc >= -14149 && $asc <= -14091) {
                return 'R';
            }
            if ($asc >= -14090 && $asc <= -13319) {
                return 'S';
            }
            if ($asc >= -13318 && $asc <= -12839) {
                return 'T';
            }
            if ($asc >= -12838 && $asc <= -12557) {
                return 'W';
            }
            if ($asc >= -12556 && $asc <= -11848) {
                return 'X';
            }
            if ($asc >= -11847 && $asc <= -11056) {
                return 'Y';
            }
            if ($asc >= -11055 && $asc <= -10247) {
                return 'Z';
            }
        } catch (Exception $e) {
            return $return;
        }
        return $return;
    }

    /**
     *  * 清除html标签
     *  */
    public static function clearTags($str)
    {
        $str = strip_tags($str);
        //首先去掉头尾空格
        $str = trim($str);
        $str = preg_replace("/(\s|\&nbsp\;||\xc2\xa0)/", "", strip_tags($str));
        //接着去掉两个空格以上的
        $str = preg_replace('/\s(?=\s)/', '', $str);
        //最后将非空格替换为一个空格
        $str = preg_replace('/[\n\r\t]/', ' ', $str);
        return $str;
    }

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

    /**
     * 支持中文字符串截取
     */
    public static function msUbStr($str, $length, $start = 0, $charset = "utf-8", $suffix = true)
    {
        switch ($charset) {
            case 'utf-8':
                $char_len = 3;
                break;
            case 'UTF8':
                $char_len = 3;
                break;
            default:
                $char_len = 2;
        }
        //小于指定长度，直接返回
        if (strlen($str) <= ($length * $char_len)) {
            return $str;
        }
        if (function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $charset);
        } else {
            if (function_exists('iconv_substr')) {
                $slice = iconv_substr($str, $start, $length, $charset);
            } else {
                $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
                $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
                $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
                $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
                preg_match_all($re[$charset], $str, $match);
                $slice = join("", array_slice($match[0], $start, $length));
            }
        }
        if ($suffix) {
            return $slice;
        }
        return $slice;
    }

    public static function fileSizeDispose($num)
    {
        $num = $num ?: 0;
        $p = 0;
        $format = 'B';
        if ($num > 0 && $num < 1024) {
            $p = 0;
            return number_format($num) . ' ' . $format;
        }
        if ($num >= 1024 && $num < pow(1024, 2)) {
            $p = 1;
            $format = 'KB';
        }
        if ($num >= pow(1024, 2) && $num < pow(1024, 3)) {
            $p = 2;
            $format = 'M';
        }
        if ($num >= pow(1024, 3) && $num < pow(1024, 4)) {
            $p = 3;
            $format = 'G';
        }
        if ($num >= pow(1024, 4) && $num < pow(1024, 5)) {
            $p = 3;
            $format = 'T';
        }
        $num /= pow(1024, $p);
        return number_format($num, 0) . ' ' . $format;
    }


    public static function microTimeFloat()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public static function timeTransform($time)
    {
        $t = time() - strtotime($time);
        $f = array(
            '31536000' => '年',
            '2592000' => '个月',
            '604800' => '星期',
            '86400' => '天',
            '3600' => '小时',
            '60' => '分钟',
            '1' => '秒'
        );
        foreach ($f as $k => $v) {
            if (0 != $c = floor($t / (int)$k)) {
                return $c . $v . '前';
            }
        }
    }

    /**
     * 根据活动时间返回状态
     *
     * @param string $startTime
     * @param string $endTime
     */
    public static function returnStatus($startTime = '', $endTime = '')
    {
        $currentTime = time();
        $startTime = strtotime($startTime);
        $endTime = strtotime($endTime);
        if ($startTime > $currentTime) {
            return "报名中";
        }
        if ($currentTime >= $startTime && $currentTime < $endTime) {
            return "进行中";
        }
        if ($endTime <= $currentTime) {
            return "已结束";
        }
    }

    /**
     * @param string $startTime
     * @param string $endTime
     * @return string
     */
    public static function returnTimeStatus($startTime = '', $endTime = '')
    {
        $currentTime = time();
        $endTime = strtotime($endTime);

        if ($endTime <= $currentTime) {
            return false;
        }
        return true;
    }

    /**
     * 消息体
     *
     * @param $key
     * @return bool|mixed
     */
    public static function reNewsContent($key, $integral = 0)
    {
        // 阅读数量
        $newContent = [
            ['ARTICLE_READ_NUM_ONE' => '你阅读文章获得'],
            ['ARTICLE_READ_NUM_THREE' => '你阅读文章获得'],
            ['ARTICLE_READ_FIVE' => '你阅读文章获得'],
            ['ARTICLE_READ_TEN' => '你阅读文章获得'],
            ['ARTICLE_READ_FIFTEEN' => '你阅读文章获得'],
            ['ARTICLE_READ_TWENTY' => '你阅读文章获得'],
            ['FIRST_REGISTER' => '首次注册获得'],
            ['EVERYDAY_LOGIN ' => '每日登陆获得'],
            ['ARTICLE_BY_PRAISE' => '文章被点赞获得'],
            ['ARTICLE_BY_COMMENT' => '文章被评论获得'],
            ['BBS_BY_PRAISE' => '帖子被赞获得'],
            ['BBS_BY_COMMENT' => '帖子被评论获得'],
            ['PRAISE_ARTICLE' => '点赞文章获得'],
            ['PRAISE_NEWS' => '点赞快讯获得'],
            ['PRAISE_BBS' => '点赞帖子获得'],
            ['PRAISE_TOOLS' => '点赞工具获得'],
            ['PRAISE_ENCYCLOPEDIA' => '点赞百科获得'],
            ['PRAISE_COMMENT' => '点赞评论获得'],
            ['COMMENT_ARTICLE' => '评论文章获得'],
            ['COMMENT_NEWS' => '评论快讯获得'],
            ['COMMENT_BBS' => '评论帖子获得'],
            ['COMMENT_TOOLS' => '评论工具获得'],
            ['COMMENT_ACTIVITY' => '评论活动获得'],
            ['COMMENT_SHOP' => '评论店铺获得'],
            ['PUBLISH_ENCYCLOPEDIA' => '发布或编辑百科'],
            ['PUBLISH_TOOLS' => '上传资源获得'],
            ['OBTAIN_MEDAL' => '获得勋章获得'],
            ['ARTICLE_BY_BUY' => '文章兑换获得'],
            ['TOOLS_BY_BUY' => '资源兑换'],
            ['LEVEL_ADD' => '等级增加']
        ];
        foreach ($newContent as $val) {
            if (array_key_exists($key, $val)) {
                return $val[$key] . $integral . '积分';
            }
        }
        return false;
    }

    public static function isMobile($mobile)
    {
        //校验规则
        $reg = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#';
        return preg_match($reg, $mobile) ? true : false;
    }

    public static function defaultNick($len = 6)
    {

        $chars = str_repeat('123456789', 3);

        // 位数过长重复字符串一定次数

        $chars = str_repeat($chars, $len);

        $chars = str_shuffle($chars);

        $str = substr($chars, 0, $len);

        return 'U' . $str;

    }


    /**
     * 直播开始时间处理处理
     *
     * @param string $date
     *
     * @return false|string
     */
    public static function tvStartTimeChange($date = '')
    {
        $result = '';
        // 当前时间
        $time = strtotime($date);
        // 今天0点时间戳
        $todayTime = strtotime(date("Y-m-d"));
        // 明天0点时间戳
        $tomorrowTime = strtotime(date("Y-m-d", strtotime("1 day")));
        // 后天0点时间戳
        $theTomorrowTime = strtotime(date("Y-m-d", strtotime("2 day")));

        if ($time >= $todayTime && $time < $tomorrowTime) {
            $result = '今天 ' . date('H:i', $time);
        } elseif ($time >= $tomorrowTime && $time < $theTomorrowTime) {
            $result = '明天 ' . date('H:i', $time);
        } else {
            $result = date('m月d日 H:i', $time);
        }
        return $result;
    }

    // 获取sign函数
    public static function getSign($params, $appSecret)
    {
        // 1. 对加密数组进行字典排序
        foreach ($params as $key => $value) {
            $arr[$key] = $key;
        }
        sort($arr);
        $str = $appSecret;
        foreach ($arr as $k => $v) {
            $str = $str . $arr[$k] . $params[$v];
        }
        $restr = $str . $appSecret;
        $sign = strtoupper(md5($restr));
        return $sign;
    }

    /**
     * 返回当前的毫秒时间戳
     */
    public static function getMsecsTime()
    {
        list($msecs, $sec) = explode(' ', microtime());
        $msecsTime = (float)sprintf('%.0f', (floatval($msecs) + floatval($sec)) * 1000);

        return $msecsTime;
    }

    /**
     * 获取客户端ip
     * @param $request
     * @return mixed
     */
    public static function getClientIp($request)
    {
        $forwarded = $request->header('X-Forwarded-For');
        $ip = '';
        if ($forwarded) {
            $attr = explode(',', $forwarded);
            $ip = $attr[0];
            if (empty($ip)) {
                $str = $request->headers;
                $reg = '|X-Forwarded-For:\s+?([^,]+)|';
                if (preg_match($reg, $str, $matches)) {
                    $ip = $matches[1];
                }
            }
        }
        return $ip;
    }

    /**
     * 砍价算法
     * @param $countPrice
     * @param int $number
     * @param int $key
     * @return float|int
     */
    public static function getPeelPrice($countPrice, int $number, int $key = 1)
    {
        if ($key > $number) {
            return 0;
        }
        $result = round($countPrice / $number, 2);

        // 如果是最后一位则做减法
        if ($number == $key) {
            $result = $countPrice - $result * ($number - 1);
        }

        return $result;
    }

    /**
     * 兑奖码
     * @param $marketingId
     * @return string
     */
    public static function getExchangeCode(int $key)
    {
        return str_pad($key, 5, '0', STR_PAD_LEFT) . rand(10000, 99999);
    }

    /**
     * @param $url
     * @param $data
     *
     * @return bool|string
     * 发起 http 请求
     */
    public static function httpPostNoRest($url, $data)
    {
        $postdata = http_build_query(
            $data
        );

        $opts = array(
            'http' =>
                array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata
                )
        );
        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    /**
     * 发起http POST请求带header
     * @param $url
     * @param array $params
     * @param array $header
     * @param int $type
     * @return bool|string
     */
    public static function httpPostRequest($url, array $params, $header = [], $type = 1)
    {
        if ($type == 1) {
            $params = json_encode($params, JSON_FORCE_OBJECT);
            $headers = [
                "Content-Type:application/json;charset=utf-8",
                "Accept:application/json;charset=utf-8"
            ];
            if ($header) {
                $headers = array_merge($header, $headers);
            }
        } else {
            $headers = [
                "Content-Type: application/x-www-form-urlencoded"
            ];
            if ($header) {
                $headers = array_merge($header, $headers);
            }
            $params = http_build_query($params);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * @param $url
     * @param $params
     *
     * @return mixed
     * http get请求
     */
    public static function httpGetRequest($url, $params, $headers = [])
    {
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 组装第三方服务url
     * @param string $path
     * @return string
     */
    public static function getOtherServerUrl($path = '')
    {
        return config('other_server.base_url') . $path;
    }
}