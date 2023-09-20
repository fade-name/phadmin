<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Controllers;

use Pha\Library\Redirect;
use Pha\Modules\Backend\Logic\Auth;

class IndexController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('admin_name', Auth::$_usName);
    }

    public function indexAction()
    {
        $menu = Auth::getMenuTree(); //菜单
        $this->view->setVar('menu', $menu);
    }

    public function logoutAction()
    {
        $this->_session->destroy();
        Redirect::scriptTop('/backend/login');
    }

}
