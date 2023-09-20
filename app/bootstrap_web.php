<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;

//error_reporting(E_ALL);
error_reporting(E_ALL & ~E_NOTICE);

define('BASE_PATH', dirname(__DIR__));
const APP_PATH = BASE_PATH . '/app';

set_error_handler(function ($error_no, $error_str, $error_file, $error_line) {
    $proFile = dirname(__DIR__) . '/product.txt';
    if (file_exists($proFile)) {
        \Pha\Library\Log::write('ERROR_NO:' . $error_no . PHP_EOL
            . 'ERROR_MESSAGE:' . $error_str . PHP_EOL
            . 'ERROR_FILE:' . $error_file . PHP_EOL
            . 'ERROR_LINE:' . $error_line);
        echoHtml();
    } else {
        echo 'ERROR_NO：' . $error_no . '<br>';
        echo 'ERROR_MESSAGE：' . $error_str . '<br>';
        echo 'ERROR_FILE：' . $error_file . '<br>';
        echo 'ERROR_LINE：' . $error_line;
    }
    exit();
}, E_ALL | E_STRICT);

try {
    /**
     * The FactoryDefault Dependency Injector automatically registers the services that
     * provide a full stack framework. These default services can be overidden with custom ones.
     */
    $di = new FactoryDefault();

    /**
     * Include general services
     */
    require APP_PATH . '/config/services.php';

    /**
     * Include web environment specific services
     */
    require APP_PATH . '/config/services_web.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

    /**
     * Handle the request
     */
    $application = new Application($di);

    /**
     * Register application modules
     */
    $application->registerModules([
        'home' => ['className' => 'Pha\Modules\Home\Module'],
        'api' => ['className' => 'Pha\Modules\Api\Module'],
        'backend' => ['className' => 'Pha\Modules\Backend\Module'],
        'cli' => ['className' => 'Pha\Modules\Cli\Module']
    ]);

    /**
     * Include routes
     */
    require APP_PATH . '/config/routes.php';

    echo $application->handle($_SERVER['REQUEST_URI'])->getContent();
} catch (\Exception $e) {
    handleErr($e);
} catch (\Error $err) {
    handleErr($err);
}

function handleErr($e)
{
    \Pha\Library\Seaslog::initSet();
    $proFile = dirname(__DIR__) . '/product.txt';
    if (file_exists($proFile)) {
        $err = $e->getMessage() . "\r\n" . $e->getTraceAsString();
        \Pha\Library\Seaslog::recordMsg('ERROR:' . $err);
        echoHtml();
    } else {
        echo $e->getMessage() . '<br>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    }
}

function echoHtml()
{
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>页面不见啦~~~</title><link href="/style/404/fonts.css" rel="stylesheet"><link rel="stylesheet" href="/style/404/style.css"></head><body><div class="moon"></div><div class="moon__crater moon__crater1"></div><div class="moon__crater moon__crater2"></div><div class="moon__crater moon__crater3"></div><div class="star star1"></div><div class="star star2"></div><div class="star star3"></div><div class="star star4"></div><div class="star star5"></div><div class="error"><div class="error__title">404</div><div class="error__subtitle">呐尼？</div><div class="error__description">可能这个网页还未开发好，请稍等....</div><button id="retHomePage" class="error__button error__button--active">返回首页</button><div style="margin-top:50px;"></div></div><div class="astronaut"><div class="astronaut__backpack"></div><div class="astronaut__body"></div><div class="astronaut__body__chest"></div><div class="astronaut__arm-left1"></div><div class="astronaut__arm-left2"></div><div class="astronaut__arm-right1"></div><div class="astronaut__arm-right2"></div><div class="astronaut__arm-thumb-left"></div><div class="astronaut__arm-thumb-right"></div><div class="astronaut__leg-left"></div><div class="astronaut__leg-right"></div><div class="astronaut__foot-left"></div><div class="astronaut__foot-right"></div><div class="astronaut__wrist-left"></div><div class="astronaut__wrist-right"></div><div class="astronaut__cord"><canvas id="cord" height="500px" width="500px"></canvas></div><div class="astronaut__head"><canvas id="visor" width="60px" height="60px"></canvas><div class="astronaut__head-visor-flare1"></div><div class="astronaut__head-visor-flare2"></div></div></div><script src="/style/404/script.js"></script><style>.copyrights{text-indent:-9999px;height:0;line-height:0;font-size:0;overflow:hidden;}</style><div class="copyrights" id="links20210126"></div></body></html>';
}
