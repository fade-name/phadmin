<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Controllers\General;

use Pha\Library\Helpers;
use Pha\Modules\Backend\Controllers\ControllerBase;
use Pha\Modules\Backend\Models\Attachment;

class AttachmentController extends ControllerBase
{

    public function indexAction()
    {
        $pager = Helpers::pagerParams($this->_ap['get'], 15);
        $list = (new Attachment())->getList($this->_ap['get'], $pager);
        $this->view->setVar('dataList', $list);
        $this->view->pick('../views/general/attachment/index');
    }

    public function delAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $m = new Attachment();
            if ($m->delData($this->_ap['post'])) {
                $this->success();
            }
        }
        $this->error('删除失败.');
    }

}
