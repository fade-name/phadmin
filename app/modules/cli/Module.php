<?php

namespace Pha\Modules\Cli;

use Phalcon\Di\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface
{

    /**
     * Registers an autoloader related to the module
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->registerNamespaces([
            'Pha\Modules\Cli\Tasks' => __DIR__ . '/tasks/',
        ]);

        $loader->register();
    }

    /**
     * Registers services related to the module
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
    }

}
