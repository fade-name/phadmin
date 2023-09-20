<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Models;

use Pha\Core\BaseModel;
//use Pha\Library\Seaslog;
use Pha\Library\Helpers;
use Pha\Library\Tools;
use Pha\Library\Validator;

class Config extends BaseModel
{

    protected $_infinite_level = false;

    protected $_enum_data_status = ['0' => '禁用', '1' => '启用'];

    public function initialize()
    {
        parent::initialize();
        $this->useDynamicUpdate(true); //设置只更新变化的字段.
        $this->setSource($this->_prefix . "config");
    }

    /**
     * 列表数据
     */
    public function getList($params, $pager, $pageLink = '/backend/general/config/index?page={P}'): array
    {
        $where = 'is_del=0';
        $where = $this->buildWhere($pageLink, $params, $where);
        $data = $this->defList(
            $where,
            self::class,
            'id,cfg_key,cfg_name,status',
            $pager,
            $pageLink,
            '',
            [],
            'id',
            'id DESC'
        );
        foreach ($data['list'] as $key => &$datum) {
            $datum['status_txt'] = empty($datum['status']) ? '' : $this->_enum_data_status[$datum['status']];
        }
        return $data;
    }

    /**
     * 添加数据
     */
    public function addData($params): bool
    {
        if (!\Pha\Modules\Backend\Validate\Config::chk($params)) {
            return $this->setErrReturn(\Pha\Modules\Backend\Validate\Config::$error);
        }
        if ($this->assign($params)->create()) {
            return true;
        }
        return $this->setErrReturn('抱歉，添加数据出错了');
    }

    /**
     * 编辑数据
     */
    public function editData($params, $gets): bool
    {
        if (!\Pha\Modules\Backend\Validate\Config::chk($params, $gets)) {
            return $this->setErrReturn(\Pha\Modules\Backend\Validate\Config::$error);
        }
        $eData = self::findFirst('id=' . $gets['edit_id']);
        if (empty($eData)) {
            return $this->setErrReturn('参数不正确');
        }
        if ($eData->assign($params)->update()) {
            return true;
        }
        return $this->setErrReturn('抱歉，更新数据出错了');
    }

    /**
     * 删除数据
     */
    public function delData($params): bool
    {
        if (empty($params['del_id'])) {
            return false;
        }
        if (Validator::isNumber($params['del_id'], 1, 9)) {
            $conditions = 'id=' . $params['del_id'];
        } elseif (Validator::isIntIdsLen($params['del_id'])) {
            $conditions = 'id IN (' . $params['del_id'] . ')';
        } else {
            return false;
        }
        $data = self::find(['conditions' => $conditions]);
        foreach ($data as $datum) {
            $datum->assign(['is_del' => 1])->update();
            //$datum->delete();
        }
        return true;
    }

    /**
     * 为下拉框提供数据
     */
    public function getOptions($params): array
    {
        $parameters = ['conditions' => 'is_del=0', 'columns' => 'id,cfg_name'];
        $date = self::find($parameters)->toArray();
        if ($this->_infinite_level) {
            $date = Helpers::getTree($date, 'cfg_name', 'id', 'parent_id');
        }
        return $date;
    }

    /**
     * 为下拉框提供数据（多选）
     */
    public function getXmsOptions($params): array
    {
        $where = 'a.is_del=0';
        if (!empty($params['search_keyword'])) {
            $kwd = Tools::clearQuotes($params['search_keyword']);
            $kwd = Tools::subString($kwd, 0, 20);
            $where = !empty($where) ? $where . " AND (a.cfg_name LIKE '%" . $kwd . "%')" : "a.cfg_name LIKE '%" . $kwd . "%'";
        }
        $field = 'a.id as value,a.cfg_name as name';
        $data = $this->joinList(
            $where,
            self::class,
            $field,
            ['page' => 1, 'limit' => 50, 'offset' => 0],
            '',
            'a',
            [],
            'a.id',
            'a.id DESC',
            false,
            false
        );
        return $data['list'];
    }

    //其他搜索条件
    private function buildWhere(&$pageLink, $params, $where = '')
    {
        if (!empty($params['search_keyword'])) {
            $search_keyword = Tools::clearQuotes($params['search_keyword']);
            $where = empty($where)
                ? "cfg_name LIKE '%" . $search_keyword . "%'"
                : "cfg_name LIKE '%" . $search_keyword . "%' AND " . $where;
            $pageLink = $pageLink . '&search_keyword=' . $params['search_keyword'];
        }
        return $where;
    }

}
