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
use Phalcon\Db\Enum;

class DatabaseController extends ControllerBase
{

    public function indexAction()
    {
        $data = $this->db->query('SHOW TABLE STATUS');
        $data->setFetchMode(Enum::FETCH_ASSOC);
        $var = $data->fetchAll();
        $this->view->setVar('dataList', $var);
        $this->view->pick('../views/general/database/index');
    }

    public function optimizeAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $params = $this->_ap['post'];
            if (!empty($params['table_name'])) {
                $this->db->query('OPTIMIZE TABLE ' . $params['table_name']);
                $this->success([], '操作成功');
            }
        }
        $this->error();
    }

    public function repairAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            $params = $this->_ap['post'];
            if (!empty($params['table_name'])) {
                $this->db->query('REPAIR TABLE ' . $params['table_name']);
                $this->success([], '操作成功');
            }
        }
        $this->error();
    }

}
