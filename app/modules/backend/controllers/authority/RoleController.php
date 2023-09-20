<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Controllers\Authority;

use Pha\Library\Redirect;
use Pha\Modules\Backend\Controllers\ControllerBase;
use Pha\Library\Helpers;
use Pha\Library\Validator;
use Pha\Modules\Backend\Models\Role;

class RoleController extends ControllerBase
{

    public function indexAction()
    {
        $pager = Helpers::pagerParams($this->_ap['get'], 15);
        $list = (new Role())->getList($this->_ap['get'], $pager);
        $this->view->setVar('dataList', $list);
        $this->view->pick('../views/authority/role/index');
    }

    public function addAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $m = new Role();
            if ($m->addData($this->_ap['post'])) {
                $this->success();
            } else {
                $this->error($m->error);
            }
        }
        $this->view->pick('../views/authority/role/add');
    }

    public function editAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $m = new Role();
            if ($m->editData($this->_ap['post'], $this->_ap['get'])) {
                $this->success();
            } else {
                $this->error($m->error);
            }
        }
        $data = null;
        if (isset($this->_ap['get']['id']) && Validator::isNumber($this->_ap['get']['id'], 1, 9)) {
            $data = Role::findFirst('id=' . $this->_ap['get']['id']);
        }
        (empty($data)) && Redirect::location('/backend/authority/role/index');
        $data = $data->toArray();
        $this->view->setVars([
            'editData' => $data
        ]);
        $this->view->pick('../views/authority/role/edit');
    }

    public function delAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $m = new Role();
            if ($m->delData($this->_ap['post'])) {
                $this->success(['top_reload' => 1]);
            }
        }
        $this->error('删除失败');
    }

}
