<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Library;

class Redirect
{

    public static function location($url)
    {
        header('Location:' . $url);
        exit();
    }

    public static function script($url, $top = false)
    {
        $sop = $top === true ? 'top.' : '';
        echo '<script>' . $sop . 'location.href="' . $url . '";</script>';
        exit();
    }

    public static function scriptTop($url)
    {
        echo '<script>top.location.href="' . $url . '";</script>';
        exit();
    }

    public static function scriptAlert($url, $msg, $top = false)
    {
        $sop = $top === true ? 'top.' : '';
        echo '<script>alert("' . $msg . '");' . $sop . 'location.href="' . $url . '";</script>';
        exit();
    }

    public static function scriptBack()
    {
        echo '<script>history.go(-1);</script>';
        exit();
    }

    public static function scriptAlertBack($msg)
    {
        echo '<script>alert("' . $msg . '");history.go(-1);</script>';
        exit();
    }

}
