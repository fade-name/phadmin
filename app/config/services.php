<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Pha\Library\Helpers;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

/**
 * Shared database config
 */
$di->setShared('database', function () {
    return include APP_PATH . "/config/database.php";
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () use ($di) {
    //$dbConfig = \Phalcon\Di::getDefault()->getShared('database');
    $dbConfig = $di->getDefault()->getShared('database');

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $dbConfig->database->adapter;
    $params = [
        'host' => $dbConfig->database->host,
        'username' => $dbConfig->database->username,
        'password' => $dbConfig->database->password,
        'dbname' => $dbConfig->database->dbname,
        'charset' => $dbConfig->database->charset
    ];

    if ($dbConfig->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    return new $class($params);
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    return new MetaDataAdapter();
});

/**
 * Configure the Volt service for rendering .volt templates
 */
$di->setShared('voltShared', function ($view) {
    $config = $this->getConfig();

    $volt = new VoltEngine($view, $this);
    $volt->setOptions([
        'path' => function ($templatePath) use ($config) {
            return Helpers::mkTemplateCacheFilePath($templatePath, $config, __DIR__);
        }
    ]);

    return $volt;
});
