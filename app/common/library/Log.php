<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Library;

class Log
{

    public static function write($log)
    {
        $logPath = BASE_PATH . DS . 'logs';
        if (!file_exists($logPath)) {
            Tools::createFolders($logPath);
        }
        $dt = date('Y-m-d H:i:s', time());
        $log = "[{$dt}]\n{$log}\n";
        $logPath .= DS . 'catch_log.log';
        if (!file_exists($logPath)) {
            $logFile = fopen($logPath, 'w');
            fwrite($logFile, $log);
            fclose($logFile);
        } else {
            error_log($log, 3, $logPath);
        }
    }

}
