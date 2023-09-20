<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Controllers;

use Pha\Core\BaseController;
use Pha\Library\Redirect;
use Pha\Modules\Backend\Logic\Auth;

class ControllerBase extends BaseController
{

    public function initialize()
    {
        parent::initialize();
        $this->initSession();
        $this->_session = parent::getDI()->getShared('session');
        $this->checkSession();
        $this->checkAuthority();
        //设置一个JS模块或样式版本，方便更新
        $this->view->setVar('style_ver', '202205200017');
    }

    #region 检测管理员登录状态

    //检测管理员登录状态
    private function checkSession()
    {
        Auth::loginStatus();
        if (Auth::$_isLogin !== true) {
            $this->clearExit();
        }
    }

    #endregion

    //检测权限
    private function checkAuthority()
    {
        $cName = $this->dispatcher->getControllerName();
        $aName = $this->dispatcher->getActionName();
        //common等控制器不进行验证
        if ($cName != 'common' && $cName != 'index' && ($cName != 'profile' || $aName != 'password')) {
            $path = $cName . '/' . $aName;
            $np = $this->dispatcher->getNamespaceName();
            $a = explode('Controllers', $np);
            $prefix = str_replace("\\", '/', $a[1]);
            $path = strtolower(trim((trim($prefix, '/') . '/' . $path), '/'));
            if (!Auth::checkAuth($path)) {
                exit('<div style="font-size:20px;width:90%;height:500px;text-align:center;line-height:300px;">抱歉，您未获得权限</div>');
            }
        }
    }

    private function clearExit()
    {
        Redirect::scriptAlert('/backend/login', '登录已过期，请重新登录', true);
    }

}
