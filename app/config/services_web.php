<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Router;
use Phalcon\Url as UrlResolver;

/**
 * Registering a router
 */
$di->setShared('router', function () {
    $router = new Router();
    $router->setDefaultModule('home');
    return $router;
});

/**
 * The URL component is used to generate all kinds of URLs in the application
 */
$di->setShared('url', function () {
    $config = $this->getConfig();
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);
    return $url;
});

/**
 * Register Redis
 */
$di->setShared('redis', function () use ($di) {
    $redisConfig = $di->getDefault()->getShared('database');
    $cache = new \Redis();
    $cache->connect($redisConfig->redis->host, $redisConfig->redis->port);
    if (isset($redisConfig->redis->auth) && !empty($redisConfig->redis->auth)) {
        $cache->auth($redisConfig->redis->auth);
    }
    if (isset($redisConfig->redis->index)) {
        $cache->select($redisConfig->redis->index);
    }
    return $cache;
});

/**
 * Set the default namespace for dispatcher
 */
$di->setShared('dispatcher', function () {
    $dispatcher = new Dispatcher();
    $dispatcher->setDefaultNamespace('Pha\Modules\Home\Controllers');
    return $dispatcher;
});
