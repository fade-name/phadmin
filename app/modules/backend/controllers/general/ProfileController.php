<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Controllers\General;

use Pha\Modules\Backend\Controllers\ControllerBase;
use Pha\Modules\Backend\Logic\Auth;
use Pha\Modules\Backend\Models\Admin;

class ProfileController extends ControllerBase
{

    public function indexAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $data = $this->_ap['post'];
            unset($data['_cur_timestamp']);
            unset($data['file']);
            Auth::$_cur_adm->assign($data)->save();
            $this->success([], '更新成功');
        }
        $this->view->setVar('adm', Auth::$_cur_adm);
        $this->view->pick('../views/general/profile/index');
    }

    public function passwordAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $m = new Admin();
            if ($m->changePwd($this->_ap['post'])) {
                $this->success([], '密码修改成功，请牢记新密码');
            } else {
                $this->error($m->error);
            }
        }
        $this->view->pick('../views/general/profile/password');
    }

}
