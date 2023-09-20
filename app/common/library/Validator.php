<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Library;

class Validator
{

    /**
     * 输入串必须是数字
     * @param string $string 输入串
     * @param int $minLen 最小长度
     * @param int $maxLen 最大长度
     * @param boolean $greaterZero 是否要求一定要大于0
     * @return bool
     */
    public static function isNumber(string $string, int $minLen = 0, int $maxLen = 0, bool $greaterZero = false): bool
    {
        if (!preg_match('/^\d+$/i', $string)) {
            return FALSE;
        }
        if ($greaterZero) {
            if (preg_match('/^0{1,128}$/i', $string)) {
                return FALSE;
            }
        }
        return self::checkStringLen($string, $minLen, $maxLen);
    }

    /**
     * 输入串必须是字母,包括小写和大写
     * @param string $string 输入串
     * @param int $minLen 最小长度
     * @param int $maxLen 最大长度
     * @return bool
     */
    public static function isLetter(string $string, int $minLen = 0, int $maxLen = 0): bool
    {
        if (!preg_match('/^[a-zA-Z]+$/i', $string)) {
            return FALSE;
        }
        return self::checkStringLen($string, $minLen, $maxLen);
    }

    /**
     * 输入串必须是大写字母
     * @param string $string 输入串
     * @param int $minLen 最小长度
     * @param int $maxLen 最大长度
     * @return bool
     */
    public static function isCapitalLetter(string $string, int $minLen = 0, int $maxLen = 0): bool
    {
        if (!preg_match('/^[A-Z]+$/i', $string)) {
            return FALSE;
        }
        return self::checkStringLen($string, $minLen, $maxLen);
    }

    /**
     * 输入串必须是小写字母
     * @param string $string 输入串
     * @param int $minLen 最小长度
     * @param int $maxLen 最大长度
     * @return bool
     */
    public static function isLowercaseLetters(string $string, int $minLen = 0, int $maxLen = 0): bool
    {
        if (!preg_match('/^[a-z]+$/i', $string)) {
            return FALSE;
        }
        return self::checkStringLen($string, $minLen, $maxLen);
    }

    /**
     * 输入串必须是字母或与数字的组合,包括小写和大写
     * @param string $string 输入串
     * @param int $minLen 最小长度
     * @param int $maxLen 最大长度
     * @return bool
     */
    public static function isLetterAndNumber(string $string, int $minLen = 0, int $maxLen = 0): bool
    {
        if (!preg_match('/^[a-zA-Z0-9]+$/i', $string)) {
            return FALSE;
        }
        return self::checkStringLen($string, $minLen, $maxLen);
    }

    /**
     * 输入串必须是字母或与数字/横杠（减号）的组合,包括小写和大写
     * @param string $string 输入串
     * @param int $minLen 最小长度
     * @param int $maxLen 最大长度
     * @return bool
     */
    public static function isLetterMinusSignNumber(string $string, int $minLen = 0, int $maxLen = 0): bool
    {
        if (!preg_match('/^[a-zA-Z0-9\-]+$/i', $string)) {
            return FALSE;
        }
        return self::checkStringLen($string, $minLen, $maxLen);
    }

    /**
     * 输入串必须是字母或与数字/横杠（减号）/下划线的组合,包括小写和大写
     * @param string $string 输入串
     * @param int $minLen 最小长度
     * @param int $maxLen 最大长度
     * @return bool
     */
    public static function isLetterMinusSignUnderlineNumber(string $string, int $minLen = 0, int $maxLen = 0): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/i', $string)) {
            return FALSE;
        }
        return self::checkStringLen($string, $minLen, $maxLen);
    }

    /**
     * 输入串必须是字母、数字及允许的合法字符的组合
     * @param string $string 输入串
     * @param int $minLen 最小长度
     * @param int $maxLen 最大长度
     * @return bool
     */
    public static function isLettersNumberAndCharacters(string $string, int $minLen = 0, int $maxLen = 0): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_\-\~\!\@\#\$\%\^\&\*\=\?\[\]\|\,\.\;]+$/i', $string)) {
            return FALSE;
        }
        return self::checkStringLen($string, $minLen, $maxLen);
    }

    /**
     * 输入串必须是字母,数字,下划线或其组合,不能纯数字,下划线不能开头和结尾
     * @param string $string 输入串
     * @param int $minLen 最小长度
     * @param int $maxLen 最大长度
     * @return bool
     */
    public static function isUserName(string $string, int $minLen = 0, int $maxLen = 0): bool
    {
        if (!preg_match('/^(?!_)(?![0-9])(?!.*?_$)[a-zA-Z0-9_]+$/i', $string)) {
            return FALSE;
        }
        return self::checkStringLen($string, $minLen, $maxLen);
    }

    /**
     * 中国的电信电话号码,带区号在前面的话，区号和号码之间必须要有横杠符号“-”
     */
    public static function isTel($string): bool
    {
        if (!preg_match('/^\d{3}-?\d{8}$|^\d{4}-?\d{7}$|^\d{4}-?\d{8}$|^\d{7,8}$/i', $string)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 验证是否中国的手机号码
     */
    public static function isMobile($string): bool
    {
        if (!preg_match('/^1[3-9]\d{9}$/i', $string)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 电子邮件
     */
    public static function isEmail($string, $minLen = 0, $maxLen = 0): bool
    {
        if (!preg_match('/^[A-Za-z0-9\-_]+@[a-zA-Z0-9\-_]+(\.[a-zA-Z0-9\-_]+)+$/i', $string)) {
            return FALSE;
        }
        return self::checkStringLen($string, $minLen, $maxLen);
    }

    /**
     * 验证字符串长度是否在指定范围内
     * @param string $string 输入串
     * @param int $minLen 最小长度
     * @param int $maxLen 最大长度
     * @return bool
     */
    public static function checkStringLen(string $string, int $minLen = 0, int $maxLen = 0): bool
    {
        $len = Tools::absLength($string);
        if ($minLen > 0) {
            if ($len < $minLen) {
                return FALSE;
            }
        }
        if ($maxLen > 0) {
            if ($len > $maxLen) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * 是否https协议
     * @return bool
     */
    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        }
        return false;
    }

    /**
     * 检测是否是JSON格式的字符串，是JSON格式的话直接解码返回
     */
    public static function isJson($string)
    {
        $js = json_decode($string);
        return is_null($js) ? false : $js;
    }

    /**
     * 验证是否十进制数，只验证格式，不限长度
     */
    public static function isDecimal($string): bool
    {
        if (!preg_match('/^(0|([1-9]\d*))(\.\d+)?$/i', $string)) {
            return FALSE;
        }
        return true;
    }

    /**
     * 验证是否金额，限最多两位小数
     */
    public static function isMoney($string): bool
    {
        if (!preg_match('/^(0|([1-9]\d*))(\.\d{1,2})?$/i', $string)) {
            return FALSE;
        }
        return true;
    }

    /**
     * 日期时间格式，必须是：2013-9-6 12:33:45这样的格式
     */
    public static function isDatetime($string): bool
    {
        if (!preg_match('/^(((((1[6-9]|[2-9]\d)\d{2})-(0?[13578]|1[02])-(0?[1-9]|[12]\d|3[01]))|(((1[6-9]|[2-9]\d)\d{2})-(0?[13456789]|1[012])-(0?[1-9]|[12]\d|30))|(((1[6-9]|[2-9]\d)\d{2})-0?2-(0?[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))-0?2-29-)) (20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d)$/i', $string)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 验证串是否是以半角逗号相隔的整数数字串
     */
    public static function isIntIds($string): bool
    {
        if (!preg_match('/^(?:\d+\,)*\d+$/i', $string)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 验证串是否是以半角逗号相隔的整数数字串，数字串本身长度限制1至9位
     */
    public static function isIntIdsLen($string): bool
    {
        if (!preg_match('/^(?:\d{1,9}\,)*\d{1,9}$/i', $string)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 验证串是否是以半角逗号相隔的整数数字串，数字串本身长度限制1位
     */
    public static function isIntIdsLenOne($string): bool
    {
        if (!preg_match('/^(?:\d\,)*\d$/i', $string)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 是否路径格式，如：abc/gef；不允许数字
     */
    public static function isPath($string): bool
    {
        if (!preg_match('/^(?:[a-zA-Z]{1,32}\/)*[a-zA-Z]{1,32}$/i', $string)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 字符串是否以指定字符串结尾
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function str_end_with(string $haystack, string $needle): bool
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

    /**
     * 验证是否是小时，分钟的时间格式，如：22:30
     */
    public static function isHmTime($string): bool
    {
        if (!preg_match('/^[0-23]+\:[0-59]+$/i', $string)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 是否中文及英文数字及几个合理的字符
     * @param string $string 输入串
     * @param int $minLen 最小长度
     * @param int $maxLen 最大长度
     * @return bool
     */
    public static function isCnOrLetterNumberChar(string $string, int $minLen = 0, int $maxLen = 0): bool
    {
        if (!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\@\#\$\%\&]+$/iu', $string)) {
            return FALSE;
        }
        return self::checkStringLen($string, $minLen, $maxLen);
    }

    /**
     * 是否时间格式
     */
    public static function isTimeFormat($string): bool
    {
        $s = '2020-05-20 ' . $string;
        return self::isDatetime($s);
    }

    /**
     * 检查图片是不是bases64编码的
     */
    public static function isBase64Format($string)
    {
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $string, $result)) {
            return $result;
        } else {
            return false;
        }
    }

}
