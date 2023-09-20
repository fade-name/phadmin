<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Controllers\Authority;

use Pha\Library\Helpers;
use Pha\Library\Redirect;
use Pha\Library\Validator;
use Pha\Modules\Backend\Controllers\ControllerBase;
use Pha\Modules\Backend\Models\Admin;
use Pha\Modules\Backend\Models\Role;

class AdminController extends ControllerBase
{

    public function indexAction()
    {
        $pager = Helpers::pagerParams($this->_ap['get'], 15);
        $list = (new Admin())->getList($this->_ap['get'], $pager);
        $this->view->setVar('dataList', $list);
        $this->view->pick('../views/authority/admin/index');
    }

    public function addAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $m = new Admin();
            if ($m->addData($this->_ap['post'])) {
                $this->success();
            } else {
                $this->error($m->error);
            }
        }
        //全部角色
        $roles = (new Role())::find(['columns' => 'id,role_name']);
        $this->view->setVar('roles', $roles);
        $this->view->pick('../views/authority/admin/add');
    }

    public function editAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $m = new Admin();
            if ($m->editData($this->_ap['post'], $this->_ap['get'])) {
                $this->success();
            } else {
                $this->error($m->error);
            }
        }
        $data = null;
        if (isset($this->_ap['get']['id']) && Validator::isNumber($this->_ap['get']['id'], 1, 9)) {
            $data = Admin::findFirst('id=' . $this->_ap['get']['id']);
        }
        (empty($data)) && Redirect::location('/backend/authority/admin/index');
        $data = $data->toArray();
        //全部角色
        $roles = (new Role())::find(['columns' => 'id,role_name']);
        $adm_role = explode(',', $data['role_ids']);
        $this->view->setVars([
            'editData' => $data,
            'roles' => $roles,
            'adm_role' => $adm_role
        ]);
        $this->view->pick('../views/authority/admin/edit');
    }

    public function delAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $m = new Admin();
            if ($m->delData($this->_ap['post'])) {
                $this->success();
            }
        }
        $this->error('删除失败');
    }

}
