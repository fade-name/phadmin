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

class User extends BaseModel
{

    protected $_infinite_level = true;

    protected $_enum_data_gender = ['0' => '女', '1' => '男', '2' => '未知'];
    protected $_enum_data_status = ['normal' => '正常', 'lock' => '锁定'];

    public function initialize()
    {
        parent::initialize();
        $this->useDynamicUpdate(true); //设置只更新变化的字段.
        $this->setSource($this->_prefix . "user");
    }

    /**
     * 列表数据
     */
    public function getList($params, $pager, $pageLink = '/backend/user/user/index?page={P}'): array
    {
        $where = 'a.is_del=0';
        $where = $this->buildWhere($pageLink, $params, $where);
        $data = $this->joinList(
            $where,
            self::class,
            'a.id,a.user_name,a.nickname,a.real_name,a.birthday,a.mobile,a.money,a.score,a.create_time,a.status,c.group_name',
            $pager,
            $pageLink,
            'a',
            [
                ['model' => User::class, 'conditions' => 'a.leader_id=b.id', 'alias' => 'b'],
                ['model' => UserGroup::class, 'conditions' => 'a.group_id=c.id', 'alias' => 'c'],
            ],
            'a.id',
            'a.id DESC'
        );
        foreach ($data['list'] as $key => &$datum) {
            $datum['a_status_txt'] = empty($datum['a_status']) ? '' : $this->_enum_data_status[$datum['a_status']];
        }
        return $data;
    }

    /**
     * 添加数据
     */
    public function addData($params): bool
    {
        if (!\Pha\Modules\Backend\Validate\User::chk($params)) {
            return $this->setErrReturn(\Pha\Modules\Backend\Validate\User::$error);
        }
        $ck = self::findFirst("user_name='" . $params['user_name'] . "'");
        if ($ck) {
            return $this->setErrReturn('已存在的用户名');
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
        if (!\Pha\Modules\Backend\Validate\User::chk($params, $gets)) {
            return $this->setErrReturn(\Pha\Modules\Backend\Validate\User::$error);
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
        $parameters = ['conditions' => "status='normal' AND is_del=0", 'columns' => 'id,user_name,leader_id'];
        $date = self::find($parameters)->toArray();
        if ($this->_infinite_level) {
            $date = Helpers::getTree($date, 'user_name', 'id', 'leader_id');
        }
        return $date;
    }

    /**
     * 为下拉框提供数据（多选）
     */
    public function getXmsOptions($params): array
    {
        $where = "a.status='normal' AND a.is_del=0";
        if (!empty($params['search_keyword'])) {
            $kwd = Tools::clearQuotes($params['search_keyword']);
            $kwd = Tools::subString($kwd, 0, 20);
            $where = !empty($where) ? $where . " AND (a.user_name LIKE '%" . $kwd . "%')" : "a.user_name LIKE '%" . $kwd . "%'";
        }
        $field = 'a.id as value,a.user_name as name';
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
                ? "a.user_name LIKE '%" . $search_keyword . "%'"
                : "a.user_name LIKE '%" . $search_keyword . "%' AND " . $where;
            $pageLink = $pageLink . '&search_keyword=' . $params['search_keyword'];
        }
        return $where;
    }

}
