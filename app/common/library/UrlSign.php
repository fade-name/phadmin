<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Library;

class UrlSign
{

    //记录数据
    protected static $_fullArr = [];

    /**
     * 除去数组中的空值和签名参数、然后排序、然后生成md5签名
     * @param array $params 签名参数组
     * @param string $secret 签名密钥
     * @return array 去掉空值与签名参数后并排序的新签名参数组
     */
    public static function generateSign(array $params, string $secret = ''): array
    {
        $params = self::paramsFilter($params); //过滤
        $params = self::argSort($params); //排序
        $preSign = self::generateHttpBuildQuery($params); //生成待签名的串
        $result['params'] = $params;
        $result['sign'] = self::md5Sign($preSign, $secret);
        $result['preSign'] = $preSign;
        return $result;
    }

    /**
     * 除去签名参数
     * @param array $params 签名参数组
     * @return array
     */
    private static function paramsFilter(array $params): array
    {
        $paramsFilter = [];
        foreach ($params as $key => $value) {
            if ($key == "sign_str") {
                continue;
            } else {
                $paramsFilter[$key] = $value;
            }
        }
        self::$_fullArr['param_arr'] = $paramsFilter;
        return $paramsFilter;
    }

    /**
     * 对数组排序
     * @param array $params 排序前的数组
     * @return array
     */
    private static function argSort(array $params): array
    {
        ksort($params);
        self::$_fullArr['sort_arr'] = $params;
        return $params;
    }

    /**
     * 把数组所有元素，按照“key1=value1&key2=value2”的模式拼接成字符串
     * @param array $params 需要拼接的一维数组
     * @return string
     */
    private static function generateHttpBuildQuery(array $params): string
    {
        $arg = '';
        foreach ($params as $key => $value) {
            $arg .= $key . '=' . $value . '&';
        }
        //去掉最后一个&字符
        //$arg = substr($arg, 0, count($arg) - 2);
        $arg = rtrim($arg, '&');
        self::$_fullArr['query_str'] = $arg;
        return $arg;
    }

    /**
     * 签名字符串
     * @param string $preSign 需要签名的字符串
     * @param string $secret 私钥
     * @return string
     */
    private static function md5Sign(string $preSign, string $secret = ''): string
    {
        $preSign = md5($secret . $preSign);
        self::$_fullArr['sign_str'] = $preSign;
        return $preSign;
    }

    /**
     * 得到完整结果
     */
    public static function getFullData(): array
    {
        return self::$_fullArr;
    }

}
