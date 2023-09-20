<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Controllers;

use Pha\Modules\Backend\Logic\Generator;

class GeneratorController extends ControllerBase
{

    public function indexAction()
    {
        //读取生成记录...
        $this->view->setVar('dataList', ['list' => [], 'pageLink' => '']);
        $this->view->pick('../views/generator/index');
    }

    public function makeAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            ini_set('max_execution_time', 0);
            set_time_limit(0);
            if (!Generator::making($this->_ap['post'])) {
                $this->error(Generator::$error);
            }
            $reload = isset($this->_ap['post']['m_type']) && $this->_ap['post']['m_type'] == 2
                ? ['top_reload' => 1] : [];
            $this->success($reload);
        }
        $this->view->pick('../views/generator/make');
    }

    public function delAction()
    {
        $this->error('wait.for');
    }

}
