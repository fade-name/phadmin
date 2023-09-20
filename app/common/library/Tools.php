<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Library;

class Tools
{

    const NUMBERS = 1;
    const CAPITAL_LETTERS = 2;
    const LOWERCASE_LETTERS = 3;
    const CAPITAL_LETTERS_NUMBERS = 4;
    const LOWERCASE_LETTERS_NUMBERS = 5;
    const LETTERS = 6;
    const LETTERS_NUMBERS = 7;

    /**
     * 获取客户端IP
     * @return string
     */
    public static function getClientIp(): string
    {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $online_ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $online_ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $online_ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $online_ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $online_ip = "unknown";
        }

        return $online_ip;
    }

    /**
     * 生成指定长度的随机字符串
     * @param int $length 生成多少位的长度
     * @param int $flag 生成方式（1-纯数字，2-大写字母，3-小写字母，4-大写字母和数字，5-小写字母和数字，6-大写字母和小写字母，7-大小写字母和数字，其他默使用7）
     * @param bool $zero_begin 对于有数字的串，是否允许0开头
     * @return string
     */
    public static function getRandChar(int $length = 6, int $flag = self::LETTERS_NUMBERS, bool $zero_begin = true): string
    {
        $str = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        switch ($flag) {
            case 1:
                $str = '0123456789';
                break;
            case 2:
                $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 3:
                $str = 'abcdefghijklmnopqrstuvwxyz';
                break;
            case 4:
                $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                break;
            case 5:
                $str = 'abcdefghijklmnopqrstuvwxyz0123456789';
                break;
            case 6:
                $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                break;
            case 7:
                $str = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }

        $len = strlen($str);
        $restring = '';
        for ($i = 0; $i < $length; $i++) {
            $restring .= self::subString($str, mt_rand(0, $len - 1), 1);
            if (false === $zero_begin && $i === 0) {
                while ($restring == '0') {
                    $restring = self::subString($str, mt_rand(0, $len - 1), 1);
                }
            }
        }

        return $restring;
    }

    /**
     * 截取字符串
     * @param string $in_str 要截取的字符串
     * @param int $start 起始位置
     * @param mixed $length 要截取的长度
     * @param string $encoding 字符编码
     * @return false|string
     */
    public static function subString(string $in_str, int $start, $length = false, string $encoding = 'utf-8')
    {
        if (function_exists('mb_substr')) {
            return mb_substr($in_str, intval($start), ($length === false ? self::absLength($in_str) : intval($length)), $encoding);
        } else {
            return substr($in_str, $start, ($length === false ? self::absLength($in_str) : intval($length)));
        }
    }

    /**
     * 获得字符串的长度,绝对长度,中文只算一个字符,此方法仅支持UTF8编码
     * @param string $in_str 要计算长度的字符串
     * @return int
     */
    public static function absLength(string $in_str): int
    {
        if (empty($in_str) && $in_str != '0') {
            return 0;
        }
        if (function_exists('mb_strlen')) {
            return mb_strlen($in_str);
        } else {
            preg_match_all("/./iu", $in_str, $ar);
            return count($ar[0]);
        }
    }

    /**
     * 清除单引号
     */
    public static function clearQuotes($string)
    {
        return str_replace("'", '', $string);
    }

    /**
     * 替换英单引号为中文单引号
     */
    public static function replaceQuotes($string)
    {
        return str_replace("'", '‘', $string);
    }

    /**
     * 循环清除或替换所有单引号
     */
    public static function clearAllQuotes($data, $type = 'replace')
    {
        if (is_array($data)) {
            $resData = [];
            foreach ($data as $key => $item) {
                $resData[$key] = self::clearAllQuotes($item, $type);
            }
            return $resData;
        } else {
            return $type == 'replace' ? self::replaceQuotes($data) : self::clearQuotes($data);
        }
    }

    /**
     * 创建目录，传入的路径必须是绝对路径，参数二为目录权限如：0777
     */
    public static function createFolders($dir, $us = 0777, $pwr = null): bool
    {
        if (is_dir($dir) || is_file($dir)) {
            return true;
        }
        self::createFolders(dirname($dir), $pwr);
        mkdir($dir); //在这里加权限的话貌似会有一些问题，用下方的设置权限才会有效
        //先修改用户及组
        if (!empty($us)) {
            chown($dir, $us);
            chgrp($dir, $us);
        }
        //同时，chmod方法设置权限时，权限值不要加引号，否则会无效！！！
        if ($pwr === null) {
            chmod($dir, 0777);
        } else {
            chmod($dir, $pwr);
        }
        return true;
    }

    /**
     * 创建目录，传入的路径必须是绝对路径，参数二为目录权限如：0777
     */
    public static function createDir($dir, $pms = 0777): void
    {
        if (is_dir($dir) || is_file($dir)) {
            return;
        }
        self::createDir(dirname($dir), $pms);
        mkdir($dir, $pms, true);
    }

    /**
     * 返回http或者https，带有“://”
     */
    public static function getHttp(): string
    {
        return Validator::isHttps() ? 'https://' : 'http://';
    }

    /**
     * 获取当前完整URL
     */
    public static function getFullUrl(): string
    {
        //$url = $http . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] .'?' . $_SERVER['QUERY_STRING']; //这样得到的是包含index.php的最原始的URL
        //这样得到的才是地址栏上显示的URL
        return self::getHttp() . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * 获得域名，包含http(s)，最后不含斜杠“/”
     */
    public static function getDomainUrl($onlyDomain = false)
    {
        return $onlyDomain ? $_SERVER['HTTP_HOST'] : self::getHttp() . $_SERVER['HTTP_HOST'];
    }

    /**
     * 获得今天是星期几
     */
    public static function getWeek(): array
    {
        $w = date('w'); //得到数字如1，2，3；注意：星期天是0
        $weekArr = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
        return [
            'num' => $w,
            'cn' => $weekArr[$w]
        ];
    }

    /**
     * 默认生成0到1之间的小数
     */
    public static function randFloat($min = 0, $max = 1)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    /**
     * 读取TXT文件返回内容
     */
    public static function readTxtFile($filePath, $fGet = false)
    {
        $handle = fopen($filePath, 'r');
        if ($fGet) {
            $content = [];
            while (!feof($handle)) {
                $str = fgets($handle);
                $str = str_replace(array("\r\n", "\r", "\n"), '', $str);
                if (!empty($str)) {
                    $content[] = $str;
                }
            }
        } else {
            $content = fread($handle, filesize($filePath));
        }
        fclose($handle);
        return $content;
    }

    /**
     * 获取扩展名不带点号
     */
    public static function getExtName($filename)
    {
        $arr = explode('.', $filename);
        return $arr[count($arr) - 1];
    }

    /**
     * 获取扩展名，默认带点号，参数二传入false则不返回点号
     */
    public static function getExtNameByStrRChr($filename, $ex = true)
    {
        return $ex ? strrchr($filename, '.') : substr(strrchr($filename, '.'), 1);
    }

    /**
     * UUID
     */
    public static function getUUID(): string
    {
        $uuid = array(
            'time_low' => 0,
            'time_mid' => 0,
            'time_hi' => 0,
            'clock_seq_hi' => 0,
            'clock_seq_low' => 0,
            'node' => array()
        );

        $uuid['time_low'] = mt_rand(0, 0xffff) + (mt_rand(0, 0xffff) << 16);
        $uuid['time_mid'] = mt_rand(0, 0xffff);
        $uuid['time_hi'] = (4 << 12) | (mt_rand(0, 0x1000));
        $uuid['clock_seq_hi'] = (1 << 7) | (mt_rand(0, 128));
        $uuid['clock_seq_low'] = mt_rand(0, 255);

        for ($i = 0; $i < 6; $i++) {
            $uuid['node'][$i] = mt_rand(0, 255);
        }

        return sprintf('%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
            $uuid['time_low'],
            $uuid['time_mid'],
            $uuid['time_hi'],
            $uuid['clock_seq_hi'],
            $uuid['clock_seq_low'],
            $uuid['node'][0],
            $uuid['node'][1],
            $uuid['node'][2],
            $uuid['node'][3],
            $uuid['node'][4],
            $uuid['node'][5]
        );
    }

    /**
     * 将字符串编辑转为UTF8
     */
    public static function encodeToUtf8($str = '')
    {
        $current_encode = mb_detect_encoding($str, array("ASCII", "GB2312", "GBK", 'BIG5', 'UTF-8'));
        return mb_convert_encoding($str, 'UTF-8', $current_encode);
    }

    /**
     * 13位时间戮
     */
    public static function mSecTime(): int
    {
        list($mSec, $sec) = explode(' ', microtime());
        $mSecTime = (float)sprintf('%.0f', (floatval($mSec) + floatval($sec)) * 1000);
        return intval($mSecTime);
    }

    /**
     * 生成32位唯一字符串，如：ffae05d7762f1a1859143cb07838b8e1
     */
    public static function createUniqueString(): string
    {
        return md5(uniqid(md5(microtime(true)), true));
    }

    /**
     * 比较两个日期的相差天数
     */
    public static function differenceDays($date1, $date2): int
    {
        if ($date1 >= $date2) {
            $startTime = strtotime($date1);
            $endTime = strtotime($date2);
        } else {
            $startTime = strtotime($date2);
            $endTime = strtotime($date1);
        }
        $diff = $startTime - $endTime;
        $day = $diff / 86400;
        return intval($day);
    }

    /**
     * 获取指定日期段内每一天的日期
     */
    public static function getDateFromRange($startDate, $endDate, $format = 'Y-m-d'): array
    {
        $sTimestamp = strtotime($startDate);
        $eTimestamp = strtotime($endDate);
        //计算日期段内有多少天
        $days = ($eTimestamp - $sTimestamp) / 86400 + 1;
        //保存每天日期
        $date = [];
        for ($i = 0; $i < $days; $i++) {
            $date[] = date($format, $sTimestamp + (86400 * $i));
        }
        return $date;
    }

    /**
     * 获取指定日期前或者后N天的所有日期
     * @param int $day_number 天数
     * @param string $date 日期格式：2023-01-03
     * @param string $type 类型 after=指定日期后几天，before=指定日期的前几天
     * @param bool $is_contain 是否包含指定的日期
     * @return array
     */
    public static function getAllDays(int $day_number = 7, string $date = '', string $type = 'after', bool $is_contain = true): array
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        if ($type == 'after') {
            if (!$is_contain) {
                $date = date("Y-m-d", strtotime('+1 days', strtotime($date)));
            }
            $firstDay = $date;
            $lastDay = date("Y-m-d", strtotime('+' . $day_number . ' days', strtotime($date)));
        } else {
            if (!$is_contain) {
                $date = date("Y-m-d", strtotime('-1 days', strtotime($date)));
            }
            $lastDay = $date;
            $firstDay = date("Y-m-d", strtotime('-' . $day_number . ' days', strtotime($date)));
        }
        $days = [];
        $i = 0;
        while (date('Y-m-d', strtotime("$firstDay +$i days")) <= $lastDay) {
            $days[] = date('Y-m-d', strtotime("$firstDay +$i days"));
            $i++;
        }
        return $days;
    }

    /**
     * 图片转base64
     */
    public static function imageToBase64Enc($image_file): string
    {
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        return 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    }

    /**
     * 解析URL
     */
    public static function parseUrl($url)
    {
        $uArr = parse_url($url);
        if ($uArr) {
            return $uArr;
        }
        return false;
    }

    /**
     * 下划线转驼峰
     * 思路:
     * step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
     * step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
     * @param string $un_camelize_words 字符串
     * @param string $separator 下划线或其他分隔符
     * @param boolean $ucFirst 首字母是否要转大写
     * @return string
     */
    public static function camelize(string $un_camelize_words, string $separator = '_', bool $ucFirst = false): string
    {
        $un_camelize_words = $separator . str_replace($separator, " ", strtolower($un_camelize_words));
        if ($ucFirst) {
            return ucfirst(ltrim(str_replace(" ", "", ucwords($un_camelize_words)), $separator));
        }
        return ltrim(str_replace(" ", "", ucwords($un_camelize_words)), $separator);
    }

    /**
     * 驼峰命名转下划线命名（全部转小写）
     * 思路:
     * @param $camelCaps
     * @param string $separator
     * @return string
     */
    public static function unCamelize($camelCaps, string $separator = '_'): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

}
