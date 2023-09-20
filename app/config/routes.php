<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

$router = $di->getRouter();

$uri = $_GET['_url'];
$a = explode('/', $uri);

foreach ($application->getModules() as $key => $module) {
    $namespace = preg_replace('/Module$/', 'Controllers', $module['className']);
    if ($a[1] == 'backend') {
        if (count($a) == 5) {
            $router->add('/' . $key . '/([a-zA-Z0-9]{1,32})/:controller/:action/:params', [
                'namespace' => $namespace . '\\' . ucfirst($a[2]),
                'module' => $key,
                'controller' => 2,
                'action' => 3,
                'params' => 4
            ]);
        } elseif (count($a) > 5) {
            $router->add('/' . $key . '/([a-zA-Z0-9]{1,32})/([a-zA-Z0-9]{1,32})/:controller/:action/:params', [
                'namespace' => $namespace . '\\' . ucfirst($a[2]) . '\\' . ucfirst($a[3]),
                'module' => $key,
                'controller' => 3,
                'action' => 4,
                'params' => 5
            ]);
        } else {
            generalRoute($router, $key, $namespace);
        }
    } else {
        generalRoute($router, $key, $namespace);
    }
}

function generalRoute($router, $key, $namespace)
{
    $router->add('/' . $key . '/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 'index',
        'action' => 'index',
        'params' => 1
    ])->setName($key);
    $router->add('/' . $key . '/:controller/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action' => 'index',
        'params' => 2
    ]);
    $router->add('/' . $key . '/:controller/:action/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action' => 2,
        'params' => 3
    ]);
}
