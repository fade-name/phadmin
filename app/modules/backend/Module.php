<?php

namespace Pha\Modules\Backend;

use Pha\Library\Helpers;
use Phalcon\Config;
use Phalcon\Di\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;

class Module implements ModuleDefinitionInterface
{

    /**
     * Registers an autoloader related to the module
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        $np = [
            'Pha\Modules\Backend\Controllers' => __DIR__ . '/controllers/',
            'Pha\Modules\Backend\Logic' => __DIR__ . '/logic/',
            'Pha\Modules\Backend\Models' => __DIR__ . '/models/',
            'Pha\Modules\Backend\Validate' => __DIR__ . '/validate/'
        ];

        $nm = new Config(include __DIR__ . '/config/namespaces.php');
        $np = array_merge($np, $nm->toArray());

        $loader->registerNamespaces($np);

        $loader->register();
    }

    /**
     * Registers services related to the module
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        /**
         * Try to load local configuration
         */
        if (file_exists(__DIR__ . '/config/config.php')) {
            $config = $di['config'];
            $override = new Config(include __DIR__ . '/config/config.php');
            if ($config instanceof Config) {
                $config->merge($override);
            } else {
                $config = $override;
            }
        }

        /**
         * Setting up the view component
         */
        $di['view'] = function () use ($di) {
            $config = $this->getConfig();

            $view = new View();
            $view->setViewsDir($config->get('application')->viewsDir);

            $view->registerEngines([
                '.volt' => 'voltShared',
                '.phtml' => function ($view) use ($config, $di) {
                    $volt = new \Pha\Core\BaseVoltEngine($view, $di); //使用模板扩展
                    $volt->setOptions(array(
                        'always' => true, //模板是否实时编译//开发阶段可设为true，线上环境后注释或设为false
                        'path' => function ($templatePath) use ($config) {
                            return Helpers::mkTemplateCacheFilePath($templatePath, $config, __DIR__);
                        }
                    ));
                    $volt->initFunction();
                    return $volt;
                }
            ]);

            return $view;
        };

    }
}
