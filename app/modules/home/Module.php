<?php

namespace Pha\Modules\Home;

use Pha\Library\Helpers;
use Phalcon\Di\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface
{

    /**
     * Registers an autoloader related to the module
     *
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->registerNamespaces([
            'Pha\Modules\Home\Controllers' => __DIR__ . '/controllers/',
            'Pha\Modules\Home\Models' => __DIR__ . '/models/',
        ]);

        $loader->register();
    }

    /**
     * Registers services related to the module
     *
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        /**
         * Setting up the view component
         */
        $di->set('view', function () use ($di) {
            $config = $this->getConfig();

            $view = new View();
            $view->setDI($this);
            $view->setViewsDir(__DIR__ . '/views/');

            $view->registerEngines([
                '.volt' => 'voltShared',
                '.phtml' => function ($view) use ($config, $di) {
                    $volt = new \Pha\Core\BaseVoltEngine($view, $di); //使用模板扩展
                    $volt->setOptions(array(
                        'always' => false, //模板是否实时编译//开发阶段可设为true，线上环境后注释或设为false
                        'path' => function ($templatePath) use ($config) {
                            return Helpers::mkTemplateCacheFilePath($templatePath, $config, __DIR__);
                        }
                    ));
                    $volt->initFunction();
                    return $volt;
                }
            ]);

            return $view;
        });
    }

}
