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
use Pha\Library\Validator;
use Pha\Modules\Backend\Controllers\ControllerBase;
use Pha\Modules\Backend\Models\MenuRule;

class MenuController extends ControllerBase
{

    public function indexAction()
    {
        $list = (new MenuRule())->getList($this->_ap['get'], [], '');
        $this->view->setVar('dataList', $list);
        $this->view->pick('../views/authority/menu/index');
    }

    public function addAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $m = new MenuRule();
            if ($m->addData($this->_ap['post'])) {
                $this->success(['top_reload' => 1]);
            } else {
                $this->error($m->error);
            }
        }
        $this->view->pick('../views/authority/menu/add');
    }

    public function editAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $m = new MenuRule();
            if ($m->editData($this->_ap['post'], $this->_ap['get'])) {
                $this->success(['top_reload' => 1]);
            } else {
                $this->error($m->error);
            }
        }
        $data = null;
        if (isset($this->_ap['get']['id']) && Validator::isNumber($this->_ap['get']['id'], 1, 9)) {
            $data = MenuRule::findFirst('id=' . $this->_ap['get']['id']);
        }
        (empty($data)) && Redirect::location('/backend/authority/menu/index');
        $data = $data->toArray();
        $data['icon'] = str_replace('&', '&amp;', $data['icon']);
        $this->view->setVars([
            'editData' => $data
        ]);
        $this->view->pick('../views/authority/menu/edit');
    }

    public function delAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $m = new MenuRule();
            if ($m->delData($this->_ap['post'])) {
                $this->success(['top_reload' => 1]);
            }
        }
        $this->error('删除失败');
    }

}
