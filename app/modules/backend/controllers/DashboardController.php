<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Controllers;

use Pha\Library\Tools;
use Pha\Modules\Backend\Logic\Auth;
use Phalcon\Version;

class DashboardController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('admin_name', Auth::$_usName);
    }

    public function indexAction()
    {
        $config = $this->di->getShared('config');
        $os = php_uname();
        $yinQing = $_SERVER['SERVER_SOFTWARE'];
        $runType = strtoupper(php_sapi_name());
        $php_ver = 'PHP' . PHP_VERSION;
        $safe_mode = $this->getCon("safe_mode");
        $this->view->setVars([
            'version' => $config->version,
            'url' => Tools::getDomainUrl(),
            'os_sys' => $os,
            'php_info' => $yinQing . ' ' . $runType . ' ' . $php_ver . ' ' . $safe_mode,
            'php_ver' => $php_ver,
            'run_type' => $runType,
            'mysql_ver' => '建议8.0+',
            'phalcon_ver' => Version::get(),
            'up_max' => $this->getCon('upload_max_filesize'),
            'run_max' => $this->getCon('max_execution_time')
        ]);
        $this->view->pick('../views/index/welcome');
    }

    private function getCon($varName)
    {
        switch ($res = get_cfg_var($varName)) {
            case 0:
                return 'Non Thread Safe';
            case 1:
                return 'Thread Safe';
            default:
                return $res;
        }

    }

}
