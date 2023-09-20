<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

use Phalcon\Loader;

$loader = new Loader();

/**
 * Register Namespaces
 */
$loader->registerNamespaces([
    'Pha\Extend' => APP_PATH . '/common/extend/',
    'Pha\Library' => APP_PATH . '/common/library/',
    'Pha\Logic' => APP_PATH . '/common/logic/',
    'Pha\Models' => APP_PATH . '/common/models/',
    'Pha\Service' => APP_PATH . '/common/service/',
    'Pha\Validate' => APP_PATH . '/common/validate/',
    'Pha\Core' => APP_PATH . '/core/'
]);

/**
 * Register module classes
 */
$loader->registerClasses([
    'Pha\Modules\Home\Module' => APP_PATH . '/modules/home/Module.php',
    'Pha\Modules\Api\Module' => APP_PATH . '/modules/api/Module.php',
    'Pha\Modules\Backend\Module' => APP_PATH . '/modules/backend/Module.php',
    'Pha\Modules\Cli\Module' => APP_PATH . '/modules/cli/Module.php'
]);

$loader->register();
