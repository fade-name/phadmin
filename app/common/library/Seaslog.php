<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Library;

use Phalcon\Di;

class Seaslog
{

    public static $_init_log = false;

    public static function recordMsg($message, $level = SEASLOG_ERROR)
    {
        self::init();
        \SeasLog::log($level, $message);
    }

    public static function initSet()
    {
        self::init();
    }

    public static function getBasePath(): string
    {
        return \SeasLog::getBasePath();
    }

    private static function init()
    {
        if (!self::$_init_log) {
            $config = Di::getDefault()->getShared('config');
            \SeasLog::setBasePath($config->seasLog->path);
            self::$_init_log = true;
        }
    }

}
