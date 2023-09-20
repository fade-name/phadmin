<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

return new \Phalcon\Config([
    'version' => '1.0',

    'application' => [
        'appDir' => APP_PATH . '/',
        'modelsDir' => APP_PATH . '/common/models/',
        'migrationsDir' => APP_PATH . '/migrations/',
        'cacheDir' => BASE_PATH . '/cache/',
        'baseUri' => '/'
    ],

    'seasLog' => [
        'path' => BASE_PATH . '/logs/'
    ],

    /**
     * if true, then we print a new line at the end of each CLI execution
     * If we don't print a new line,
     * then the next command prompt will be placed directly on the left of the output,
     * and it is less readable.
     * You can disable this behaviour if the output of your application needs to don't have a new line at end
     */
    'printNewLine' => true
]);
