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
use Pha\Modules\Backend\Logic\Auth;

class LoginController extends BaseController
{

    public function initialize()
    {
        parent::initialize();
        $this->initSession();
        $this->_session = parent::getDI()->getShared('session');
    }

    public function indexAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            if (!Auth::login($this->_ap['post'])) {
                $this->error(Auth::$error);
            }
            $this->_session->set('lgn_ses_manager', $this->_ap['post']['username']);
            $this->success(['url' => '/backend/index'], '登录成功');
        }
    }

}
