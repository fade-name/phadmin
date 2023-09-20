<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Controllers\User;

use Pha\Library\Helpers;
use Pha\Library\Redirect;
use Pha\Library\Validator;
use Pha\Modules\Backend\Controllers\ControllerBase;
use Pha\Modules\Backend\Models\UserGroup;
//...

class UserGroupController extends ControllerBase
{

    public function indexAction()
    {
        $pager = Helpers::pagerParams($this->_ap['get'], 15);
        $list = (new UserGroup())->getList($this->_ap['get'], $pager);
        $this->view->setVar('dataList', $list);
        $this->view->pick('../views/user/user_group/index');
    }

    public function addAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $m = new UserGroup();
            if ($m->addData($this->_ap['post'])) {
                $this->success();
            } else {
                $this->error($m->error);
            }
        }
        $this->view->pick('../views/user/user_group/add');
    }

    public function editAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $m = new UserGroup();
            if ($m->editData($this->_ap['post'], $this->_ap['get'])) {
                $this->success();
            } else {
                $this->error($m->error);
            }
        }
        $data = null;
        if (isset($this->_ap['get']['id']) && Validator::isNumber($this->_ap['get']['id'], 1, 9)) {
            $data = UserGroup::findFirst('id=' . $this->_ap['get']['id']);
        }
        (empty($data)) && Redirect::location('/backend/user/user_group/index');
        $data = $data->toArray();
        //...
        $this->view->setVars([
            'editData' => $data,
            //...
        ]);
        $this->view->pick('../views/user/user_group/edit');
    }

    public function delAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $m = new UserGroup();
            if ($m->delData($this->_ap['post'])) {
                $this->success();
            }
        }
        $this->error('删除失败.');
    }

    //专为下拉选择框提供数据使用
    public function provideDataForSelectionAction()
    {
        $this->success((new UserGroup())->getOptions($this->_ap['get']));
    }

    //专为下拉选择框提供数据使用（多选）
    public function provideDataForXmsSelectionAction()
    {
        $this->success((new UserGroup())->getXmsOptions($this->_ap['get']));
    }

}
